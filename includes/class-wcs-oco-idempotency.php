<?php
/**
 * One Click Order idempotency storage.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_OCO_Idempotency {

    const TABLE_SUFFIX   = 'wcs_oco_requests';
    const RETENTION_DAYS = 30;

    public static function table_name() {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_SUFFIX;
    }

    /**
     * Create or update the dedicated request table.
     */
    public static function install() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table   = self::table_name();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            request_hash char(64) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'processing',
            order_id bigint(20) unsigned DEFAULT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            expires_at datetime NOT NULL,
            PRIMARY KEY  (request_hash),
            KEY status_expires (status,expires_at),
            KEY order_id (order_id)
        ) {$charset};";

        dbDelta( $sql );
    }

    public static function hash_request( $request_id ) {
        return hash( 'sha256', strtolower( (string) $request_id ) );
    }

    /**
     * Import a batch of legacy wp_options reservations without deleting them.
     *
     * A legacy numeric value represents an order ID. Any other value is
     * uncertain and must not be treated as safe to retry.
     *
     * @param int $limit    Maximum number of rows to scan.
     * @param int $after_id Continue after this option_id.
     * @return array|WP_Error
     */
    public static function migrate_legacy( $limit = 100, $after_id = 0 ) {
        global $wpdb;

        $table         = self::table_name();
        $options_table = $wpdb->options;
        $limit         = max( 1, min( 500, absint( $limit ) ) );
        $after_id      = absint( $after_id );

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_id, option_name, option_value
                 FROM {$options_table}
                 WHERE option_id > %d
                   AND option_name LIKE %s
                 ORDER BY option_id ASC
                 LIMIT %d",
                $after_id,
                $wpdb->esc_like( 'wcs_oco_request_' ) . '%',
                $limit
            ),
            ARRAY_A
        );

        if ( ! is_array( $rows ) ) {
            return new WP_Error(
                'wcs_oco_migration_read_failed',
                'Unable to read legacy reservations.'
            );
        }

        $imported = 0;
        $skipped  = 0;
        $last_id  = $after_id;

        foreach ( $rows as $row ) {
            $option_id = absint( $row['option_id'] );
            $key       = (string) $row['option_name'];

            if ( ! preg_match( '/^wcs_oco_request_([a-f0-9]{64})$/', $key, $matches ) ) {
                $skipped++;
                $last_id = $option_id;
                continue;
            }

            $hash     = $matches[1];
            $value    = (string) $row['option_value'];
            $order_id = null;
            $status   = 'uncertain';

            if ( ctype_digit( $value ) && (int) $value > 0 ) {
                $order_id = (int) $value;
                $status   = 'completed';
            }

            $now     = current_time( 'mysql', true );
            $expires = gmdate( 'Y-m-d H:i:s', time() + self::RETENTION_DAYS * DAY_IN_SECONDS );

            // A duplicate is harmless only when the existing record is compatible.
            // The no-op update preserves the existing status and order reference.
            $result = $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO {$table}
                     (request_hash, status, order_id, created_at, updated_at, expires_at)
                     VALUES (%s, %s, %s, %s, %s, %s)
                     ON DUPLICATE KEY UPDATE request_hash = request_hash",
                    $hash,
                    $status,
                    $order_id,
                    $now,
                    $now,
                    $expires
                )
            );

            if ( false === $result ) {
                return new WP_Error(
                    'wcs_oco_migration_write_failed',
                    'Unable to import legacy reservation at option_id ' . $option_id . '.'
                );
            }

            if ( 1 === (int) $result ) {
                $imported++;
            } else {
                $existing = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT status, order_id FROM {$table} WHERE request_hash = %s LIMIT 1",
                        $hash
                    ),
                    ARRAY_A
                );

                if ( ! is_array( $existing ) ) {
                    return new WP_Error(
                        'wcs_oco_migration_verify_failed',
                        'Unable to verify existing reservation at option_id ' . $option_id . '.'
                    );
                }

                $existing_order_id = null === $existing['order_id'] ? null : (int) $existing['order_id'];

                // Do not silently accept conflicting legacy and table states.
                if ( $existing['status'] !== $status || $existing_order_id !== $order_id ) {
                    return new WP_Error(
                        'wcs_oco_migration_conflict',
                        'Conflicting reservation at option_id ' . $option_id . '; manual reconciliation required.'
                    );
                }

                $skipped++;
            }

            $last_id = $option_id;
        }

        return array(
            'imported' => $imported,
            'skipped'  => $skipped,
            'scanned'  => count( $rows ),
            'last_id'  => $last_id,
            'has_more' => count( $rows ) === $limit,
        );
    }

    /**
     * Import one resumable batch. Does not indicate that cutover is safe.
     *
     * @return array|WP_Error
     */
    public static function migrate_next_batch() {
        $cursor = absint( get_option( 'wcs_oco_migration_cursor', 0 ) );
        $result = self::migrate_legacy( 100, $cursor );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        if ( ! is_array( $result ) || ! isset( $result['last_id'], $result['has_more'] ) ) {
            return new WP_Error( 'wcs_oco_migration_invalid_result', 'Unexpected migration result.' );
        }

        $last_id = absint( $result['last_id'] );

        if ( $last_id > $cursor ) {
            $saved = update_option( 'wcs_oco_migration_cursor', $last_id, false );

            if ( ! $saved && $last_id !== absint( get_option( 'wcs_oco_migration_cursor', 0 ) ) ) {
                return new WP_Error( 'wcs_oco_migration_cursor_failed', 'Unable to save migration progress.' );
            }
        }

        if ( empty( $result['has_more'] ) ) {
            $saved = update_option( 'wcs_oco_migration_scanned', 'yes', false );

            if ( ! $saved && 'yes' !== get_option( 'wcs_oco_migration_scanned', '' ) ) {
                return new WP_Error( 'wcs_oco_migration_status_failed', 'Unable to save migration status.' );
            }
        }

        return $result;
    }

    /**
     * Reserve a request atomically using the unique request_hash key.
     *
     * @return bool True only for a newly inserted reservation.
     */
    public static function reserve( $request_id ) {
        global $wpdb;

        $now     = current_time( 'mysql', true );
        $expires = gmdate( 'Y-m-d H:i:s', time() + self::RETENTION_DAYS * DAY_IN_SECONDS );

        $result = $wpdb->insert(
            self::table_name(),
            array(
                'request_hash' => self::hash_request( $request_id ),
                'status'       => 'processing',
                'order_id'     => null,
                'created_at'   => $now,
                'updated_at'   => $now,
                'expires_at'   => $expires,
            ),
            array( '%s', '%s', '%d', '%s', '%s', '%s' )
        );

        return 1 === $result;
    }

    public static function complete( $request_id, $order_id ) {
        global $wpdb;

        $result = $wpdb->update(
            self::table_name(),
            array(
                'status'     => 'completed',
                'order_id'   => absint( $order_id ),
                'updated_at' => current_time( 'mysql', true ),
            ),
            array(
                'request_hash' => self::hash_request( $request_id ),
                'status'       => 'processing',
            ),
            array( '%s', '%d', '%s' ),
            array( '%s', '%s' )
        );

        return 1 === $result;
    }

    public static function mark_uncertain( $request_id, $order_id = null ) {
        global $wpdb;

        $data = array(
            'status'     => 'uncertain',
            'updated_at' => current_time( 'mysql', true ),
        );
        $formats = array( '%s', '%s' );

        if ( null !== $order_id && absint( $order_id ) > 0 ) {
            $data['order_id'] = absint( $order_id );
            $formats[]        = '%d';
        }

        return $wpdb->update(
            self::table_name(),
            $data,
            array(
                'request_hash' => self::hash_request( $request_id ),
                'status'       => 'processing',
            ),
            $formats,
            array( '%s', '%s' )
        );
    }

    public static function release( $request_id ) {
        global $wpdb;

        return $wpdb->delete(
            self::table_name(),
            array(
                'request_hash' => self::hash_request( $request_id ),
                'status'       => 'processing',
            ),
            array( '%s', '%s' )
        );
    }

    /**
     * Remove only expired completed records, never uncertain/processing ones.
     */
    public static function cleanup_completed( $limit = 100 ) {
        global $wpdb;

        $table = self::table_name();
        $limit = max( 1, min( 500, absint( $limit ) ) );
        $now   = current_time( 'mysql', true );

        return $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table}
                WHERE status = %s
                AND expires_at < %s
                ORDER BY expires_at ASC
                LIMIT %d",
                'completed',
                $now,
                $limit
            )
        );
    }
}

/**
 * Register periodic cleanup for expired OCO reservations.
 */
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'wcs_oco_cleanup_completed' ) ) {
        wp_schedule_event(
            time() + HOUR_IN_SECONDS,
            'hourly',
            'wcs_oco_cleanup_completed'
        );
    }
} );

add_action( 'wcs_oco_cleanup_completed', function () {
    global $wpdb;

    // Cleanup expired completed idempotency reservations.
    WCS_OCO_Idempotency::cleanup_completed( 100 );

    // Remove rate-limit options from previous hourly windows.
    $current_window = (int) floor( time() / HOUR_IN_SECONDS );

    $options_table = $wpdb->options;
    $prefix        = 'wcs_oco_rate_';

    // Delete expired rate-limit slots in bounded batches.
    // The option name contains the hourly window timestamp.
    $deleted = 0;

    do {
        $rows = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name
                FROM {$options_table}
                WHERE option_name LIKE %s
                AND option_name REGEXP %s
                AND CAST(
                    SUBSTRING_INDEX(
                        SUBSTRING_INDEX(option_name, '_', -2),
                        '_',
                        1
                    ) AS UNSIGNED
                ) < %d
                ORDER BY option_name ASC
                LIMIT 500",
                $wpdb->esc_like( $prefix ) . '%',
                '^wcs_oco_rate_[a-f0-9]{32}_[0-9]+_[1-5]$',
                $current_window
            )
        );

        $batch_deleted = 0;

        foreach ( $rows as $option_name ) {
            if (
                preg_match(
                    '/^wcs_oco_rate_[a-f0-9]{32}_([0-9]+)_[1-5]$/',
                    $option_name,
                    $matches
                )
                && (int) $matches[1] < $current_window
            ) {
                if ( delete_option( $option_name ) ) {
                    $batch_deleted++;
                    $deleted++;
                }
            }
        }
    } while ( 500 === count( $rows ) && $batch_deleted > 0 && $deleted < 5000 );
} );