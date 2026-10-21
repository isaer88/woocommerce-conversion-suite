<?php
/**
 * FBT cart operations.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_FBT_Cart {

    public function register_hooks() {
        add_action(
            'wp_ajax_wcs_fbt_add',
            array( $this, 'add_bundle' )
        );

        add_action(
            'wp_ajax_nopriv_wcs_fbt_add',
            array( $this, 'add_bundle' )
        );
    }

    public function add_bundle() {
        if ( ! check_ajax_referer( 'wcs_fbt_add', 'nonce', false ) ) {
            wp_send_json_error(
                array( 'message' => 'Security check failed.' ),
                403
            );
        }

        if ( ! function_exists( 'WC' ) ) {
            wp_send_json_error(
                array( 'message' => 'WooCommerce is unavailable.' ),
                503
            );
        }

        if ( function_exists( 'wc_load_cart' ) && ! WC()->cart ) {
            wc_load_cart();
        }

        if ( ! WC()->cart ) {
            wp_send_json_error(
                array( 'message' => 'Cart is unavailable.' ),
                503
            );
        }

        $main_id = isset( $_POST['product_id'] )
            ? absint( $_POST['product_id'] )
            : 0;

        $main = wc_get_product( $main_id );

        if (
            ! $main
            || ! $main->is_type( 'simple' )
            || 'publish' !== $main->get_status()
            || ! $main->is_visible()
            || ! $main->is_purchasable()
            || ! $main->is_in_stock()
        ) {
            wp_send_json_error(
                array( 'message' => 'Main product is unavailable.' ),
                400
            );
        }

        $saved = $main->get_meta( '_wcs_fbt_products', true );
        $saved = is_array( $saved ) ? array_map( 'absint', $saved ) : array();

        $raw = isset( $_POST['related_ids'] )
            ? wp_unslash( $_POST['related_ids'] )
            : array();

        if ( ! is_array( $raw ) ) {
            wp_send_json_error(
                array( 'message' => 'Invalid selection.' ),
                400
            );
        }

        $selected = array_values(
            array_unique(
                array_filter( array_map( 'absint', $raw ) )
            )
        );

        $settings = WCS_FBT_Settings::get_settings();
        $limit = max( 1, min( 3, absint( $settings['max_products'] ) ) );

        if (
            count( $selected ) > $limit
            || array_diff( $selected, $saved )
        ) {
            wp_send_json_error(
                array( 'message' => 'Invalid bundle selection.' ),
                400
            );
        }

        $ids = array_merge( array( $main_id ), $selected );

        // Validate all products before changing the cart.
        foreach ( $ids as $id ) {
            $product = wc_get_product( $id );

            if (
                ! $product
                || ! $product->is_type( 'simple' )
                || 'publish' !== $product->get_status()
                || ! $product->is_visible()
                || ! $product->is_purchasable()
                || ! $product->is_in_stock()
            ) {
                wp_send_json_error(
                    array( 'message' => 'A selected product is unavailable.' ),
                    400
                );
            }

            if (
                $product->is_sold_individually()
                && WC()->cart->find_product_in_cart(
                    WC()->cart->generate_cart_id( $id )
                )
            ) {
                wp_send_json_error(
                    array( 'message' => 'A selected product is already in your cart.' ),
                    400
                );
            }

            $in_cart = 0;

            foreach ( WC()->cart->get_cart() as $cart_item ) {
                if ( (int) $cart_item['product_id'] === $id ) {
                    $in_cart += (int) $cart_item['quantity'];
                }
            }

            if (
                $product->managing_stock()
                && ! $product->has_enough_stock( $in_cart + 1 )
            ) {
                wp_send_json_error(
                    array( 'message' => 'Not enough stock for a selected product.' ),
                    400
                );
            }
        }

        // Snapshot quantities to support rollback on failure.
        $before = array();

        foreach ( WC()->cart->get_cart() as $key => $item ) {
            $before[ $key ] = (int) $item['quantity'];
        }

        $added = array();

        foreach ( $ids as $id ) {
            try {
                $key = WC()->cart->add_to_cart( $id, 1 );
            } catch ( Throwable $error ) {
                $key = false;
            }

            if ( ! $key ) {
                foreach ( $added as $added_key ) {
                    if ( isset( $before[ $added_key ] ) ) {
                        WC()->cart->set_quantity(
                            $added_key,
                            $before[ $added_key ],
                            false
                        );
                    } else {
                        WC()->cart->remove_cart_item( $added_key );
                    }
                }

                WC()->cart->calculate_totals();

                wp_send_json_error(
                    array(
                        'message' => 'Could not add all products. Cart changes were reverted.',
                    ),
                    400
                );
            }

            $added[] = $key;
        }

        WC()->cart->calculate_totals();

        wp_send_json_success(
            array(
                'message' => 'Products added to cart.',
                'cartUrl' => wc_get_cart_url(),
                'count'   => WC()->cart->get_cart_contents_count(),
            )
        );
    }
}
