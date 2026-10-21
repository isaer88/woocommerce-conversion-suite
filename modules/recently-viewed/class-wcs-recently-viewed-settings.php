<?php
/**
 * Recently Viewed settings.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_Recently_Viewed_Settings {

    const OPTION_NAME = 'wcs_recently_viewed_settings';
    const PAGE_SLUG = 'wcs-recently-viewed';

    public static function defaults() {
        return array(
            'title'           => 'Recently Viewed Products',
            'limit'           => 4,
            'show_current'    => 'no',
            'auto_display'    => 'yes',
            'require_consent' => 'no',
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
            'Recently Viewed Settings',
            'Recently Viewed',
            'manage_woocommerce',
            self::PAGE_SLUG,
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'wcs_recently_viewed_group',
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

        $limit = isset( $input['limit'] )
            ? absint( $input['limit'] )
            : 4;

        return array(
            'title' => isset( $input['title'] )
                ? sanitize_text_field( $input['title'] )
                : 'Recently Viewed Products',

            'limit' => max( 4, min( 12, $limit ) ),

            'show_current' => (
                isset( $input['show_current'] )
                && 'yes' === $input['show_current']
            ) ? 'yes' : 'no',

            'auto_display' => (
                isset( $input['auto_display'] )
                && 'yes' === $input['auto_display']
            ) ? 'yes' : 'no',

            'require_consent' => (
                isset( $input['require_consent'] )
                && 'yes' === $input['require_consent']
            ) ? 'yes' : 'no',
        );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'woocommerce-conversion-suite' ) );
        }

        require __DIR__ . '/views/settings-page.php';
    }
}
