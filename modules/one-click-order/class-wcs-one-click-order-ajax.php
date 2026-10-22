<?php
/**
 * One Click Order AJAX handler.
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
            is_string( $key )
            && 0 === strpos( $key, 'wcs_oco_request_' )
        ) {
            delete_option( $key );
        }
    }

    public function create_order() {
        if ( ! check_ajax_referer(
            'wcs_oco_create_order',
            'nonce',
            false
        ) ) {
            $this->error( 'Security verification failed. Refresh the page.', 403 );
        }

        if (
            ! function_exists( 'wc_create_order' )
            || ! function_exists( 'wc_get_product' )
        ) {
            $this->error( 'WooCommerce is unavailable.', 503 );
        }

        $settings = WCS_One_Click_Order_Settings::get_settings();

        if ( 'yes' !== $settings['enabled'] ) {
            $this->error( 'Quick Order is disabled.', 403 );
        }

        $product_id = isset( $_POST['product_id'] )
            ? absint( wp_unslash( $_POST['product_id'] ) )
            : 0;

        $quantity = isset( $_POST['quantity'] )
            ? absint( wp_unslash( $_POST['quantity'] ) )
            : 0;

        $name = isset( $_POST['customer_name'] )
            ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) )
            : '';

        $phone = isset( $_POST['customer_phone'] )
            ? sanitize_text_field( wp_unslash( $_POST['customer_phone'] ) )
            : '';

        $request_id = isset( $_POST['request_id'] )
            ? sanitize_text_field( wp_unslash( $_POST['request_id'] ) )
            : '';

        if (
            ! preg_match(
                '/^[a-f0-9-]{36}$/i',
                $request_id
            )
        ) {
            $this->error( 'Invalid request identifier.' );
        }

        if (
            '' === $name
            || strlen( $name ) > 200
            || '' === $phone
            || ! preg_match( '/^\+?[0-9\s().-]{7,25}$/', $phone )
        ) {
            $this->error( 'Enter a valid name and phone number.' );
        }

        if ( $quantity < 1 || $quantity > 20 ) {
            $this->error( 'Quantity must be between 1 and 20.' );
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
            $this->error( 'This product is unavailable for Quick Order.' );
        }

        // Taxable products need the regular checkout calculation flow.
        if ( wc_tax_enabled() && $product->is_taxable() ) {
            $this->error(
                'This product requires the standard checkout.'
            );
        }

        if (
            $product->is_sold_individually()
            && 1 !== $quantity
        ) {
            $this->error( 'Only one unit can be ordered.' );
        }

        if (
            $product->managing_stock()
            && ! $product->has_enough_stock( $quantity )
        ) {
            $this->error( 'Not enough stock available.' );
        }

        /*
         * Limit request volume by IP fingerprint.
         * This is an abuse mitigation measure, not authentication.
         */
        $ip = isset( $_SERVER['REMOTE_ADDR'] )
            ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
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

        /*
         * Reserve a unique request ID.
         * add_option() uses a unique option_name constraint.
         */
        $request_key = 'wcs_oco_request_' . hash(
            'sha256',
            $request_id
        );

        if ( ! add_option( $request_key, 'processing', '', false ) ) {
            $this->error(
                'This order request has already been submitted.',
                409
            );
        }

        // Cleanup reservation after 24 hours.
        wp_schedule_single_event(
            time() + DAY_IN_SECONDS,
            'wcs_oco_cleanup_request',
            array( $request_key )
        );

        $order = false;

        try {
            $order = wc_create_order(
                array(
                    'created_via' => 'wcs-one-click-order',
                    'customer_id' => get_current_user_id(),
                )
            );

            if ( is_wp_error( $order ) ) {
                throw new Exception( $order->get_error_message() );
            }

            if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
                throw new Exception( 'Order creation failed.' );
            }

            $item_id = $order->add_product(
                $product,
                $quantity
            );

            if ( ! $item_id ) {
                throw new Exception( 'Unable to add the product.' );
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

            /*
             * Calculate product-line totals without tax.
             * Taxable products are excluded above.
             * Shipping is NOT calculated in this MVP.
             */
            $order->calculate_totals( false );

            $order->set_status( 'on-hold' );

            $order->add_order_note(
                'Created by One Click Order. Shipping is not included. Confirm the final total with the customer.'
            );

            $order->save();

            update_option(
                $request_key,
                (string) $order->get_id(),
                false
            );

            wp_send_json_success(
                array(
                    'order_id' => $order->get_id(),
                    'message'  => $settings['success_text'],
                )
            );
        } catch ( Throwable $exception ) {
            if ( $order && is_a( $order, 'WC_Order' ) ) {
                try {
                    $order->delete( true );
                } catch ( Throwable $cleanup_error ) {
                    error_log(
                        'WCS OCO cleanup failed: ' .
                        $cleanup_error->getMessage()
                    );
                }
            }

            delete_option( $request_key );

            error_log(
                'WCS OCO error: ' . $exception->getMessage()
            );

            $this->error(
                'We could not create your request. Please try again.',
                500
            );
        }
    }
}
