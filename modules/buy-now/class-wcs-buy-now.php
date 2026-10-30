<?php
/**
 * Buy Now frontend.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_Buy_Now {

    private $settings = array();
    private $buy_now_request = false;
    private $added_to_cart = false;

    public function __construct() {
        $saved = get_option( 'wcs_buy_now_settings', array() );

        $this->settings = wp_parse_args(
            is_array( $saved ) ? $saved : array(),
            array(
                'enabled'       => 'yes',
                'button_text'   => 'Buy Now',
                'redirect_type' => 'checkout',
                'clear_cart'    => 'no',
            )
        );
    }

    public function register_hooks() {
        if ( 'yes' !== $this->settings['enabled'] ) {
            return;
        }

        add_action(
            'woocommerce_after_add_to_cart_button',
            array( $this, 'render_button' )
        );

        add_action(
            'wp_loaded',
            array( $this, 'prepare_buy_now' ),
            19
        );

        add_action(
            'woocommerce_add_to_cart',
            array( $this, 'after_add_to_cart' ),
            20,
            6
        );

        // Synchronize Buy Now with WooCommerce variations.
        add_action(
            'wp_enqueue_scripts',
            array( $this, 'enqueue_variation_script' ),
            20
        );
    }

    /**
     * Render Buy Now button.
     */
    public function render_button() {
        global $product;

        if ( ! $product instanceof WC_Product ) {
            return;
        }

        if (
            ! $product->is_purchasable()
            || ! $product->is_in_stock()
        ) {
            return;
        }

        if (
            ! $product->is_type( 'simple' )
            && ! $product->is_type( 'variable' )
        ) {
            return;
        }

        printf(
            '<button type="submit" name="wcs_buy_now" value="%1$d" class="button alt wcs-buy-now-button" %3$s>%2$s</button>',
            absint( $product->get_id() ),
            esc_html( $this->settings['button_text'] ),
            disabled( $product->is_type( 'variable' ), true, false )
        );
    }

    /**
     * Synchronize the Buy Now button with the native
     * WooCommerce Add to Cart button.
     */
    public function enqueue_variation_script() {
        if (
            ! function_exists( 'is_product' )
            || ! is_product()
        ) {
            return;
        }

        wp_enqueue_script( 'wc-add-to-cart-variation' );

        $script = <<<'JS'
jQuery(function($) {
    'use strict';

    $('form.variations_form').each(function() {
        var $form = $(this);

        var $buyNow = $form.find(
            '.wcs-buy-now-button'
        );

        var $addToCart = $form.find(
            '.single_add_to_cart_button'
        ).not('.wcs-buy-now-button').first();

        if (!$buyNow.length || !$addToCart.length) {
            return;
        }

        function syncBuyNow() {
            var disabled =
                $addToCart.prop('disabled') ||
                $addToCart.hasClass('disabled') ||
                !$form.find(
                    'input[name="variation_id"]'
                ).val();

            $buyNow
                .prop('disabled', !!disabled)
                .toggleClass('disabled', !!disabled)
                .attr('aria-disabled', String(!!disabled));
        }

        // Disabled until WooCommerce selects
        // a valid variation.
        $buyNow.prop('disabled', true);

        $form.on(
            'found_variation.wcsBuyNow ' +
            'show_variation.wcsBuyNow ' +
            'hide_variation.wcsBuyNow ' +
            'reset_data.wcsBuyNow ' +
            'woocommerce_variation_has_changed.wcsBuyNow',
            function() {
                window.setTimeout(syncBuyNow, 0);
            }
        );

        $form.on(
            'change.wcsBuyNow',
            'select[name^="attribute_"], ' +
            'input[name^="attribute_"]',
            function() {
                $buyNow
                    .prop('disabled', true)
                    .addClass('disabled')
                    .attr('aria-disabled', 'true');
            }
        );

        // Handles preselected variations and
        // WooCommerce initialization.
        window.setTimeout(syncBuyNow, 0);
    });
});
JS;

        wp_add_inline_script(
            'wc-add-to-cart-variation',
            $script,
            'after'
        );
    }

    /**
     * Prepare the request for WooCommerce's native handler.
     */
    public function prepare_buy_now() {
        if (
            'POST' !== sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) )
            || ! isset( $_POST['wcs_buy_now'] )
        ) {
            return;
        }

        if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
            return;
        }

        if (
            ! isset( $_POST['wcs_buy_now'] )
            || ! is_scalar( $_POST['wcs_buy_now'] )
        ) {
            return;
        }

        $product_id = absint(
            wp_unslash( $_POST['wcs_buy_now'] )
        );

        if ( ! $product_id ) {
            return;
        }

        $product = wc_get_product( $product_id );

        if (
            ! $product
            || ! $product->is_purchasable()
            || ! in_array(
                $product->get_type(),
                array( 'simple', 'variable' ),
                true
            )
        ) {
            return;
        }

        // Native WooCommerce validation handles
        // variation_id and attribute_* values.
        $_REQUEST['add-to-cart'] = $product_id;

        $this->buy_now_request = true;

        add_filter(
            'woocommerce_add_to_cart_redirect',
            array( $this, 'redirect_after_add' ),
            999
        );
    }

    /**
     * Runs after a successful WooCommerce add_to_cart().
     */
    public function after_add_to_cart(
        $cart_item_key,
        $product_id,
        $quantity,
        $variation_id,
        $variation,
        $cart_item_data
    ) {
        if (
            ! $this->buy_now_request
            || ! WC()->cart
        ) {
            return;
        }

        $this->added_to_cart = true;

        if ( 'yes' !== $this->settings['clear_cart'] ) {
            return;
        }

        // Keep the successfully added cart item.
        foreach ( WC()->cart->get_cart() as $key => $item ) {
            if ( $key !== $cart_item_key ) {
                WC()->cart->remove_cart_item( $key );
            }
        }

        // Preserve the requested quantity.
        $current = WC()->cart->get_cart_item(
            $cart_item_key
        );

        if ( $current ) {
            WC()->cart->set_quantity(
                $cart_item_key,
                $quantity,
                false
            );
        }

        WC()->cart->calculate_totals();
    }

    /**
     * Redirect only after a successful add to cart.
     */
    public function redirect_after_add( $url ) {
        if (
            ! $this->buy_now_request
            || ! $this->added_to_cart
        ) {
            return $url;
        }

        if ( 'cart' === $this->settings['redirect_type'] ) {
            return wc_get_cart_url();
        }

        return wc_get_checkout_url();
    }
}
