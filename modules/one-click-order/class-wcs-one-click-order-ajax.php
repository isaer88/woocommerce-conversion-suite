<?php
/**
 * One Click Order AJAX handler.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_One_Click_Order_Ajax {
    /**
     * Log operational errors through WooCommerce.
     *
     * @param string $message Error message.
     */
    private static function log_error( $message ) {
        if ( function_exists( 'wc_get_logger' ) ) {
            wc_get_logger()->error(
                $message,
                array( 'source' => 'wcs-one-click-order' )
            );
        }
    }


    public function register_hooks() {
        add_action(
            'wp_ajax_wcs_oco_create_order',
            array( $this, 'create_order' )
        );

        add_action(
            'wp_ajax_nopriv_wcs_oco_create_order',
            array( $this, 'create_order' )
        );

        add_action(
            'wcs_oco_cleanup_request',
            array( $this, 'cleanup_request' )
        );
    }

    private function error( $message, $status = 400 ) {
        wp_send_json_error(
            array( 'message' => $message ),
            $status
        );
    }

    public function cleanup_request( $key ) {
        if (
            ! is_string( $key )
            || ! preg_match(
                '/^wcs_oco_request_[a-f0-9]{64}$/',
                $key
            )
        ) {
            return;
        }

        // Do not automatically delete reservations.
        // A processing reservation may belong to an order
        // whose ID could not be persisted.
        if ( 'processing' === get_option( $key, false ) ) {
            self::log_error(
                'WCS OCO: Reservation requires manual review: ' . $key
            );
        }
    }

    public function create_order() {
        if (
            ! check_ajax_referer(
                'wcs_oco_create_order',
                'nonce',
                false
            )
        ) {
            $this->error(
                'Security verification failed. Refresh the page.',
                403
            );
        }

        if (
            ! function_exists( 'wc_create_order' )
            || ! function_exists( 'wc_get_product' )
        ) {
            $this->error(
                'WooCommerce is unavailable.',
                503
            );
        }

        $settings = VELTRIXA_One_Click_Order_Settings::get_settings();

        if ( 'yes' !== ( $settings['enabled'] ?? 'no' ) ) {
            $this->error( 'Quick Order is disabled.', 403 );
        }

        $fields = array(
            'product_id',
            'quantity',
            'customer_name',
            'customer_phone',
            'request_id',
        );

        foreach ( $fields as $field ) {
            if (
                ! isset( $_POST[ $field ] )
                || ! is_string( $_POST[ $field ] )
            ) {
                $this->error( 'Invalid request data.', 400 );
            }
        }

        $product_id = absint(
            wp_unslash( $_POST['product_id'] )
        );

        $quantity_raw = wp_unslash( $_POST['quantity'] );

        if (
            ! preg_match( '/^[1-9][0-9]*$/', $quantity_raw )
            || strlen( $quantity_raw ) > 2
            || (int) $quantity_raw > 20
        ) {
            $this->error(
                'Quantity must be between 1 and 20.',
                400
            );
        }

        $quantity = (int) $quantity_raw;

        $name = sanitize_text_field(
            wp_unslash( $_POST['customer_name'] )
        );

        if ( mb_strlen( $name, 'UTF-8' ) > 100 ) {
            $this->error(
                'Customer name must not exceed 100 characters.',
                400
            );
        }

        $phone = sanitize_text_field(
            wp_unslash( $_POST['customer_phone'] )
        );

        $request_id = sanitize_text_field(
            wp_unslash( $_POST['request_id'] )
        );

        if (
            ! preg_match(
                '/^[a-f0-9]{8}-[a-f0-9]{4}-[1-8][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i',
                $request_id
            )
        ) {
            $this->error( 'Invalid request identifier.' );
        }

        if (
            '' === $name
            || '' === $phone
            || ! preg_match(
                '/^\+?[0-9\s().-]{7,25}$/',
                $phone
            )
        ) {
            $this->error(
                'Enter a valid name and phone number.'
            );
        }

        if ( $quantity < 1 || $quantity > 20 ) {
            $this->error(
                'Quantity must be between 1 and 20.'
            );
        }

        $product = wc_get_product( $product_id );

        if (
            ! $product
            || ! $product->is_type( 'simple' )
            || 'publish' !== $product->get_status()
            || ! $product->is_visible()
            || ! $product->is_purchasable()
            || ! $product->is_in_stock()
            || $product->backorders_allowed()
            || ! is_numeric( $product->get_price() )
        ) {
            $this->error(
                'This product is unavailable for Quick Order.'
            );
        }

        if (
            wc_tax_enabled()
            && $product->is_taxable()
        ) {
            $this->error(
                'This product requires the standard checkout.'
            );
        }

        if (
            $product->is_sold_individually()
            && 1 !== $quantity
        ) {
            $this->error(
                'Only one unit can be ordered.'
            );
        }

        if (
            $product->managing_stock()
            && ! $product->has_enough_stock( $quantity )
        ) {
            $this->error(
                'Not enough stock available.'
            );
        }

        // Basic IP-based abuse mitigation.
        $ip = isset( $_SERVER['REMOTE_ADDR'] )
            ? sanitize_text_field(
                wp_unslash( $_SERVER['REMOTE_ADDR'] )
            )
            : 'unknown';

        // Atomic IP-based rate limiting: max 5 attempts per hour.
        // Uses unique option names to prevent concurrent requests
        // from claiming the same attempt slot.

        $rate_prefix = 'wcs_oco_rate_' . md5( $ip ) . '_';
        $rate_window = (int) floor( time() / HOUR_IN_SECONDS );
        $rate_allowed = false;

        for ( $attempt = 1; $attempt <= 5; $attempt++ ) {
            $rate_key = $rate_prefix . $rate_window . '_' . $attempt;

            if ( add_option( $rate_key, time(), '', false ) ) {
                $rate_allowed = true;
                break;
            }
        }

        if ( ! $rate_allowed ) {
            $this->error(
                'Too many requests. Please try again later.',
                429
            );
        }

        // During the rolling upgrade, the legacy option is acquired first.
        // This also blocks older AJAX handlers still using add_option().
        $request_key = 'wcs_oco_request_' . hash(
            'sha256',
            strtolower( $request_id )
        );

        if ( ! class_exists( 'VELTRIXA_OCO_Idempotency', false ) ) {
            $storage_file = VELTRIXA_PLUGIN_PATH . 'includes/class-wcs-oco-idempotency.php';
            if ( ! is_readable( $storage_file ) ) {
                $this->error( 'Order storage is unavailable.', 503 );
            }
            require_once $storage_file;
        }

        // Do not create an order if the dedicated storage is not installed.
        if ( VELTRIXA_OCO_DB_VERSION !== get_option( 'wcs_oco_table_version' ) ) {
            $this->error( 'Order storage is not ready. Please try again later.', 503 );
        }

        if ( ! add_option( $request_key, 'processing', '', false ) ) {
            $this->error( 'This order request has already been submitted.', 409 );
        }

        if ( ! VELTRIXA_OCO_Idempotency::reserve( $request_id ) ) {
            // The legacy reservation was created by this request; roll it back.
            delete_option( $request_key );

            global $wpdb;
            $table = VELTRIXA_OCO_Idempotency::table_name();
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT request_hash FROM %i WHERE request_hash = %s LIMIT 1",
                $table,
                    VELTRIXA_OCO_Idempotency::hash_request( $request_id )
                )
            );

            if ( is_string( $existing ) && '' !== $existing ) {
                $this->error( 'This order request has already been submitted.', 409 );
            }
            $this->error( 'Order storage is temporarily unavailable.', 503 );
        }

        // Keep the legacy reservation for compatibility with older handlers.
        // It must not be removed until the rolling upgrade is complete.
        $scheduled = wp_schedule_single_event(
            time() + DAY_IN_SECONDS,
            'wcs_oco_cleanup_request',
            array( $request_key ),
            true
        );
        if ( is_wp_error( $scheduled ) || ! $scheduled ) {
            self::log_error( 'WCS OCO: Failed to schedule legacy reservation review.' );
        }

        $order = false;

        try {
            $order = wc_create_order(
                array(
                    'created_via' => 'wcs-one-click-order',
                    'customer_id' => get_current_user_id(),
                )
            );

            if ( is_wp_error( $order ) ) {
                throw new Exception(
                    $order->get_error_message()
                );
            }

            if (
                ! $order instanceof WC_Order
                || ! $order->get_id()
            ) {
                throw new Exception(
                    'Order creation failed.'
                );
            }

            $item_id = $order->add_product(
                $product,
                $quantity
            );

            if ( ! $item_id ) {
                throw new Exception(
                    'Unable to add the product.'
                );
            }

            $order->set_billing_first_name( $name );
            $order->set_billing_phone( $phone );

            $order->set_payment_method_title(
                'Manual confirmation'
            );

            $order->set_customer_note(
                'Quick Order request. Delivery and final payable amount must be confirmed by the store.'
            );

            $order->update_meta_data(
                '_wcs_oco_request_id',
                $request_id
            );

            $order->update_meta_data(
                '_wcs_oco_source',
                'one-click-order'
            );

            // Taxes and shipping are excluded in this MVP.
            $order->calculate_totals( false );

            $order->set_status( 'on-hold' );

            $order->add_order_note(
                'Created by One Click Order. Shipping is not included. Confirm the final total with the customer.'
            );

            $order->save();

            if ( ! $order->get_id() ) {
                throw new Exception(
                    'Order was not saved.'
                );
            }

            // Both stores must be updated before acknowledging the order.
            $order_id = (string) $order->get_id();
            if ( ! VELTRIXA_OCO_Idempotency::complete( $request_id, $order->get_id() ) ) {
                throw new RuntimeException( 'Unable to complete database reservation.' );
            }

            $updated = update_option( $request_key, $order_id, false );
            if ( ! $updated && $order_id !== (string) get_option( $request_key, '' ) ) {
                throw new RuntimeException( 'Unable to complete legacy reservation.' );
            }

            wp_send_json_success(
                array(
                    'order_id' => $order->get_id(),
                    'message'  => $settings['success_text'] ?? 'Order request received.',
                )
            );

        } catch ( Throwable $exception ) {

            // Once order creation has been attempted, deleting an order or
            // releasing its reservation may allow duplicate external effects.
            // Preserve the order and both idempotency locks for reconciliation.
            $failed_order_id = $order instanceof WC_Order ? absint( $order->get_id() ) : 0;

            $marked = VELTRIXA_OCO_Idempotency::mark_uncertain(
                $request_id,
                $failed_order_id > 0 ? $failed_order_id : null
            );

            if ( 1 !== $marked ) {
                // A completed reservation must remain completed; a missing or
                // unmodified reservation requires administrator investigation.
                self::log_error( 'WCS OCO: Could not mark reservation uncertain; manual review required.' );
            }

            self::log_error(
                'WCS OCO error (request hash: ' .
                VELTRIXA_OCO_Idempotency::hash_request( $request_id ) .
                ', order ID: ' . $failed_order_id . '): ' .
                $exception->getMessage()
            );

            $this->error(
                'We could not confirm your order request. Please contact the store before trying again.',
                500
            );
        }
    }
}
