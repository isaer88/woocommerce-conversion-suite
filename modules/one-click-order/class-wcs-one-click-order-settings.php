<?php
/**
 * One Click Order settings.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_One_Click_Order_Settings {

    const OPTION_NAME = 'wcs_one_click_order_settings';
    const PAGE_SLUG   = 'wcs-one-click-order';

    public static function defaults() {
        return array(
            'enabled'       => 'no',
            'button_text'   => 'Quick Order',
            'modal_title'   => 'Quick Order',
            'intro_text'    => 'Leave your contact details. We will confirm availability, delivery and the final total.',
            'success_text'  => 'Your request has been received. We will contact you to confirm the details.',
            'button_color'  => '#2563eb',
            'text_color'    => '#ffffff',
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
            'One Click Order Settings',
            'One Click Order',
            'manage_woocommerce',
            self::PAGE_SLUG,
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'wcs_one_click_order_group',
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
            'enabled' => (
                isset( $input['enabled'] )
                && 'yes' === $input['enabled']
            ) ? 'yes' : 'no',
        );

        foreach (
            array(
                'button_text',
                'modal_title',
                'intro_text',
                'success_text',
            ) as $key
        ) {
            $result[ $key ] = isset( $input[ $key ] )
                ? sanitize_text_field( wp_unslash( $input[ $key ] ) )
                : $defaults[ $key ];
        }

        foreach ( array( 'button_color', 'text_color' ) as $key ) {
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
                esc_html__( 'Permission denied.', 'woocommerce-conversion-suite' )
            );
        }

        require __DIR__ . '/views/settings-page.php';
    }
}
