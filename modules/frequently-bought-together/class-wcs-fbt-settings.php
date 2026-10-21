<?php
/**
 * Frequently Bought Together settings.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_FBT_Settings {

    const OPTION_NAME = 'wcs_fbt_settings';
    const PAGE_SLUG = 'wcs-fbt';

    public static function defaults() {
        return array(
            'title'        => 'Frequently Bought Together',
            'max_products' => 3,
            'auto_display' => 'yes',
            'button_text'  => 'Add Selected to Cart',
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
            'Frequently Bought Together Settings',
            'Frequently Bought Together',
            'manage_woocommerce',
            self::PAGE_SLUG,
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'wcs_fbt_group',
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

        return array(
            'title' => isset( $input['title'] )
                ? sanitize_text_field( $input['title'] )
                : $defaults['title'],

            'max_products' => max(
                1,
                min(
                    3,
                    isset( $input['max_products'] )
                        ? absint( $input['max_products'] )
                        : 3
                )
            ),

            'auto_display' => (
                isset( $input['auto_display'] )
                && 'yes' === $input['auto_display']
            ) ? 'yes' : 'no',

            'button_text' => ! empty( $input['button_text'] )
                ? sanitize_text_field( $input['button_text'] )
                : $defaults['button_text'],
        );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( 'Permission denied.' );
        }

        require __DIR__ . '/views/settings-page.php';
    }
}
