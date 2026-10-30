<?php
/**
 * Sale Countdown settings.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_Sale_Countdown_Settings {

    const OPTION_NAME = 'wcs_sale_countdown_settings';
    const PAGE_SLUG = 'wcs-sale-countdown';

    public static function defaults() {
        return array(
            'title'        => 'Sale ends in:',
            'auto_display' => 'yes',
            'background'   => '#f8f9fa',
            'text_color'   => '#222222',
            'accent_color' => '#2271b1',
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
            'woocommerce-conversion-suite',
            'Sale Countdown Settings',
            'Sale Countdown',
            'manage_woocommerce',
            self::PAGE_SLUG,
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'wcs_sale_countdown_group',
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
        $defaults = self::defaults();

        $result = array(
            'title' => isset( $input['title'] )
                ? sanitize_text_field( $input['title'] )
                : $defaults['title'],

            'auto_display' => (
                isset( $input['auto_display'] )
                && 'yes' === $input['auto_display']
            ) ? 'yes' : 'no',
        );

        foreach ( array( 'background', 'text_color', 'accent_color' ) as $key ) {
            $color = isset( $input[ $key ] )
                ? sanitize_hex_color( $input[ $key ] )
                : false;

            $result[ $key ] = $color ?: $defaults[ $key ];
        }

        return $result;
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die(
                esc_html__( 'Permission denied.', 'veltrixa-toolkit-for-woocommerce' )
            );
        }

        require __DIR__ . '/views/settings-page.php';
    }
}
