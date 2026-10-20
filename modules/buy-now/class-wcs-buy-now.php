
<?php
/**
 * Buy Now frontend.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_Buy_Now {

    private $settings = array();

    public function __construct() {
        $saved = get_option(
            'wcs_buy_now_settings',
            array()
        );

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
            array( $this, 'handle_buy_now' ),
            19
        );
    }

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
            '<button type="submit" name="wcs_buy_now" value="1" class="button alt wcs-buy-now-button">%s</button>',
            esc_html( $this->settings['button_text'] )
        );
    }

    public function handle_buy_now() {
        if (
            ! isset( $_POST['wcs_buy_now'] )
            || ! isset( $_POST['add-to-cart'] )
        ) {
            return;
        }

        if (
            ! function_exists( 'WC' )
            || ! WC()->cart
        ) {
            return;
        }

        $product_id = absint(
            wp_unslash( $_POST['add-to-cart'] )
        );

        if ( ! $product_id ) {
            return;
        }

        if ( 'yes' === $this->settings['clear_cart'] ) {
            WC()->cart->empty_cart();
        }

        // WooCommerce handles product/variation validation
        // and adding to cart through its native handler.
        add_filter(
            'woocommerce_add_to_cart_redirect',
            array( $this, 'redirect_after_add' ),
            999
        );
    }

    public function redirect_after_add( $url ) {
        if ( 'cart' === $this->settings['redirect_type'] ) {
            return wc_get_cart_url();
        }

        return wc_get_checkout_url();
    }
}
