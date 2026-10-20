
<?php
/**
 * Buy Now frontend.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_Buy_Now {

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

        // Runs before WooCommerce's native form handler.
        add_action(
            'wp_loaded',
            array( $this, 'prepare_buy_now' ),
            19
        );

        // Runs only after WooCommerce adds an item successfully.
        add_action(
            'woocommerce_add_to_cart',
            array( $this, 'after_add_to_cart' ),
            20,
            6
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
            '<button type="submit" name="wcs_buy_now" value="%1$d" class="button alt wcs-buy-now-button">%2$s</button>',
            absint( $product->get_id() ),
            esc_html( $this->settings['button_text'] )
        );
    }

    /**
     * Prepare the request for WooCommerce's native handler.
     */
    public function prepare_buy_now() {
        if (
            'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' )
            || ! isset( $_POST['wcs_buy_now'] )
        ) {
            return;
        }

        if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
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

        // The native handler reads add-to-cart from $_REQUEST.
        // For variable products the original form also supplies
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
     * WooCommerce calls this hook after a successful add_to_cart().
     */
    public function after_add_to_cart(
        $cart_item_key,
        $product_id,
        $quantity,
        $variation_id,
        $variation,
        $cart_item_data
    ) {
        if ( ! $this->buy_now_request || ! WC()->cart ) {
            return;
        }

        $this->added_to_cart = true;

        if ( 'yes' !== $this->settings['clear_cart'] ) {
            return;
        }

        // Preserve the successfully added cart item.
        // Remove other items only after native validation succeeds.
        foreach ( WC()->cart->get_cart() as $key => $item ) {
            if ( $key !== $cart_item_key ) {
                WC()->cart->remove_cart_item( $key );
            }
        }

        // If the same item was already in the cart, WooCommerce
        // may have merged quantities. Keep the requested quantity.
        $current = WC()->cart->get_cart_item( $cart_item_key );

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
     * Redirect only if WooCommerce added the item successfully.
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
