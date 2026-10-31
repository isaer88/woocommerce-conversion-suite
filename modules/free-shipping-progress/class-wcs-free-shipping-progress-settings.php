<?php
/**
 * Free Shipping Progress settings.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_Free_Shipping_Progress_Settings {

    const OPTION_NAME = 'wcs_free_shipping_progress_settings';
    const PAGE_SLUG = 'wcs-free-shipping-progress';

    public static function defaults() {
        return array(
            'threshold'    => '1000',
            'show_cart'    => 'yes',
            'show_checkout'=> 'no',
            'message'      => 'Add {amount} more to unlock free shipping!',
            'success'      => 'You have reached the free shipping threshold!',
            'bar_color'    => '#2271b1',
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
            'Free Shipping Progress Settings',
            'Free Shipping Progress',
            'manage_woocommerce',
            self::PAGE_SLUG,
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'wcs_free_shipping_progress_group',
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

        $threshold = isset( $input['threshold'] )
            ? wc_format_decimal( wp_unslash( $input['threshold'] ) )
            : $defaults['threshold'];

        $threshold = max( 0.01, (float) $threshold );

        return array(
            'threshold' => (string) $threshold,

            'show_cart' => (
                isset( $input['show_cart'] )
                && 'yes' === $input['show_cart']
            ) ? 'yes' : 'no',

            'show_checkout' => (
                isset( $input['show_checkout'] )
                && 'yes' === $input['show_checkout']
            ) ? 'yes' : 'no',

            'message' => isset( $input['message'] )
                ? sanitize_text_field( wp_unslash( $input['message'] ) )
                : $defaults['message'],

            'success' => isset( $input['success'] )
                ? sanitize_text_field( wp_unslash( $input['success'] ) )
                : $defaults['success'],

            'bar_color' => (
                isset( $input['bar_color'] )
                ? sanitize_hex_color( $input['bar_color'] )
                : false
            ) ?: $defaults['bar_color'],
        );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'veltrixa-toolkit-for-woocommerce' ) );
        }

        require __DIR__ . '/views/settings-page.php';
    }
}
