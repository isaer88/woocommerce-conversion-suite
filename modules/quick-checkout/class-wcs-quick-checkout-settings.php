<?php
/**
 * Quick Checkout settings.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_Quick_Checkout_Settings {

    const OPTION_NAME = 'wcs_quick_checkout_settings';
    const PAGE_SLUG = 'wcs-quick-checkout';

    public static function defaults() {
        return array(
            'hide_company'     => 'yes',
            'hide_address_2'   => 'yes',
            'hide_order_notes' => 'yes',
            'optional_postcode'=> 'no',
            'require_phone'    => 'yes',
            'require_email'    => 'yes',
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
            'Quick Checkout Settings',
            'Quick Checkout',
            'manage_woocommerce',
            self::PAGE_SLUG,
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'wcs_quick_checkout_group',
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

        $result = array();

        foreach ( array_keys( self::defaults() ) as $key ) {
            $result[ $key ] = (
                isset( $input[ $key ] )
                && 'yes' === $input[ $key ]
            ) ? 'yes' : 'no';
        }

        return $result;
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'woocommerce-conversion-suite' ) );
        }

        require __DIR__ . '/views/settings-page.php';
    }
}
