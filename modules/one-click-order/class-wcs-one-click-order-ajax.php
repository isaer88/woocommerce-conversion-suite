<?php
/**
 * One Click Order AJAX handler.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_One_Click_Order_Ajax {

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
            error_log(
                'WCS OCO: Reservation requires manual review: ' . $key
            );
        }
    }

    private function get_post_string( $key, $max_length = 255 ) {
        if (
            ! isset( $_POST[ $key ] )
            || ! is_string( $_POST[ $key ] )
        ) {
            return '';
        }

        $value = wp_unslash( $_POST[ $key ] );

        if ( strlen( $value ) > $max_length ) {
            return '';
        }

        return sanitize_text_field( $value );
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

        $settings = WCS_One_Click_Order_Settings::get_settings();

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

        $quantity = absint(
            wp_unslash( $_POST['quantity'] )
        );

        $name = sanitize_text_field(
            wp_unslash( $_POST['customer_name'] )
        );

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

        $rate_key = 'wcs_oco_rate_' . md5( $ip );

        $attempts = (int) get_transient( $rate_key );

        if ( $attempts >= 5 ) {
            $this->error(
                'Too many requests. Please try again later.',
                429
            );
        }

        set_transient(
            $rate_key,
            $attempts + 1,
            HOUR_IN_SECONDS
        );

        // Atomic reservation through the unique option name.
        $request_key = 'wcs_oco_request_' . hash(
            'sha256',
            strtolower( $request_id )
        );

        if (
            ! add_option(
                $request_key,
                'processing',
                '',
                false
            )
        ) {
            $this->error(
                'This order request has already been submitted.',
                409
            );
        }

        // Cleanup abandoned processing reservations after 24 hours.
        $scheduled = wp_schedule_single_event(
            time() + DAY_IN_SECONDS,
            'wcs_oco_cleanup_request',
            array( $request_key ),
            true
        );

        if ( is_wp_error( $scheduled ) || ! $scheduled ) {
            error_log(
                'WCS OCO: Failed to schedule request cleanup.'
            );
        }

        $order = false;
        $cleanup_confirmed = false;

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

            // Store the order ID for duplicate detection.
            $order_id = (string) $order->get_id();

            $updated = update_option(
                $request_key,
                $order_id,
                false
            );

            if (
                ! $updated
                && $order_id !== (string) get_option( $request_key, '' )
            ) {
                throw new RuntimeException(
                    'Unable to persist the order request reservation.'
                );
            }

            wp_send_json_success(
                array(
                    'order_id' => $order->get_id(),
                    'message'  => $settings['success_text'] ?? 'Order request received.',
                )
            );

        } catch ( Throwable $exception ) {

            if ( $order instanceof WC_Order ) {
                try {
                    $order_id = $order->get_id();

                    if ( $order_id ) {
                        $order->delete( true );

                        // Confirm that the order no longer exists.
                        $cleanup_confirmed = ! wc_get_order(
                            $order_id
                        );
                    } else {
                        $cleanup_confirmed = true;
                    }
                } catch ( Throwable $cleanup_error ) {
                    error_log(
                        'WCS OCO cleanup failed: ' .
                        $cleanup_error->getMessage()
                    );
                }
            } else {
                $cleanup_confirmed = true;
            }

            // Only release the request ID when cleanup succeeded.
            if ( $cleanup_confirmed ) {
                delete_option( $request_key );
            }

            error_log(
                'WCS OCO error: ' .
                $exception->getMessage()
            );

            $this->error(
                'We could not create your request. Please try again.',
                500
            );
        }
    }
}
