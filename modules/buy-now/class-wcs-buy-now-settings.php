<?php
/**
 * Buy Now settings.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_Buy_Now_Settings {

    const OPTION_NAME = 'wcs_buy_now_settings';
    const PAGE_SLUG = 'wcs-buy-now';

    public static function defaults() {
        return array(
            'enabled'       => 'yes',
            'button_text'   => 'Buy Now',
            'redirect_type' => 'checkout',
            'clear_cart'    => 'no',
        );
    }

    public static function get_settings() {
        $saved = get_option( self::OPTION_NAME, array() );

        return wp_parse_args(
            is_array( $saved ) ? $saved : array(),
            self::defaults()
        );
    }

    public function register_hooks() {
        add_action(
            'admin_menu',
            array( $this, 'register_page' )
        );

        add_action(
            'admin_init',
            array( $this, 'register_settings' )
        );
    }

    public function register_page() {
        add_submenu_page(
            'woocommerce-conversion-suite',
            'Buy Now Settings',
            'Buy Now',
            'manage_woocommerce',
            self::PAGE_SLUG,
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'wcs_buy_now_group',
            self::OPTION_NAME,
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize' ),
                'default'           => self::defaults(),
            )
        );
    }

    public function sanitize( $input ) {
        $input = is_array( $input ) ? $input : array();

        $redirect = isset( $input['redirect_type'] )
            ? sanitize_key( $input['redirect_type'] )
            : 'checkout';

        if ( ! in_array( $redirect, array( 'checkout', 'cart' ), true ) ) {
            $redirect = 'checkout';
        }

        return array(
            'enabled' => (
                isset( $input['enabled'] )
                && 'yes' === $input['enabled']
            ) ? 'yes' : 'no',

            'button_text' => ! empty( $input['button_text'] )
                ? sanitize_text_field( $input['button_text'] )
                : 'Buy Now',

            'redirect_type' => $redirect,

            'clear_cart' => (
                isset( $input['clear_cart'] )
                && 'yes' === $input['clear_cart']
            ) ? 'yes' : 'no',
        );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( 'Insufficient permissions.' );
        }

        require __DIR__ . '/views/settings-page.php';
    }
}
