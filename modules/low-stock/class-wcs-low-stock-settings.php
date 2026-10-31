<?php
/**
 * Low Stock settings.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_Low_Stock_Settings {

    const OPTION_NAME = 'wcs_low_stock_settings';
    const PAGE_SLUG = 'wcs-low-stock';

    public static function defaults() {
        return array(
            'threshold'    => 10,
            'message'      => 'Hurry! Only {stock} items left in stock!',
            'auto_display' => 'yes',
            'show_bar'     => 'yes',
            'background'   => '#fff7ed',
            'text_color'   => '#9a3412',
            'bar_color'    => '#ea580c',
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
            'Low Stock Settings',
            'Low Stock',
            'manage_woocommerce',
            self::PAGE_SLUG,
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'wcs_low_stock_group',
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
            'threshold' => isset( $input['threshold'] )
                ? max( 1, min( 9999, absint( $input['threshold'] ) ) )
                : $defaults['threshold'],

            'message' => isset( $input['message'] )
                ? sanitize_text_field( wp_unslash( $input['message'] ) )
                : $defaults['message'],

            'auto_display' => (
                isset( $input['auto_display'] )
                && 'yes' === $input['auto_display']
            ) ? 'yes' : 'no',

            'show_bar' => (
                isset( $input['show_bar'] )
                && 'yes' === $input['show_bar']
            ) ? 'yes' : 'no',
        );

        foreach (
            array( 'background', 'text_color', 'bar_color' )
            as $key
        ) {
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
                esc_html__(
                    'Permission denied.',
                    'veltrixa-toolkit-for-woocommerce'
                )
            );
        }

        require __DIR__ . '/views/settings-page.php';
    }
}
