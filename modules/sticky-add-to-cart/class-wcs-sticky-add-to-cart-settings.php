<?php
/**
 * Sticky Add to Cart settings.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_Sticky_Add_To_Cart_Settings {

    const OPTION_NAME = 'wcs_sticky_add_to_cart_settings';
    const PAGE_SLUG   = 'wcs-sticky-add-to-cart';

    public static function defaults() {
        return array(
            'position'     => 'bottom',
            'show_mobile'  => 'yes',
            'show_image'   => 'yes',
            'button_text'  => 'Add to cart',
            'background'   => '#ffffff',
            'button_color' => '#2271b1',
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
        add_action( 'admin_menu', array( $this, 'register_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function register_page() {
        add_submenu_page(
            'woocommerce',
            'Sticky Add to Cart Settings',
            'Sticky Add to Cart',
            'manage_woocommerce',
            self::PAGE_SLUG,
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'wcs_sticky_add_to_cart_group',
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

        $position = isset( $input['position'] )
            ? sanitize_key( $input['position'] )
            : 'bottom';

        if ( ! in_array( $position, array( 'top', 'bottom' ), true ) ) {
            $position = 'bottom';
        }

        $background = isset( $input['background'] )
            ? sanitize_hex_color( $input['background'] )
            : false;

        $button_color = isset( $input['button_color'] )
            ? sanitize_hex_color( $input['button_color'] )
            : false;

        return array(
            'position'     => $position,
            'show_mobile'  => (
                isset( $input['show_mobile'] )
                && 'yes' === $input['show_mobile']
            ) ? 'yes' : 'no',
            'show_image'   => (
                isset( $input['show_image'] )
                && 'yes' === $input['show_image']
            ) ? 'yes' : 'no',
            'button_text'  => ! empty( $input['button_text'] )
                ? sanitize_text_field( $input['button_text'] )
                : 'Add to cart',
            'background'   => $background ?: '#ffffff',
            'button_color' => $button_color ?: '#2271b1',
        );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'woocommerce-conversion-suite' ) );
        }

        require __DIR__ . '/views/settings-page.php';
    }
}
