<?php
/**
 * Exit Intent settings.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_Exit_Intent_Settings {

    const OPTION_NAME = 'wcs_exit_intent_settings';
    const PAGE_SLUG   = 'wcs-exit-intent';

    public static function defaults() {
        return array(
            'enabled'         => 'no',
            'title'           => 'Wait! Don\'t leave yet!',
            'message'         => 'Discover our latest offers before you go.',
            'button_text'     => 'View Special Offers',
            'button_url'      => '',
            'delay'           => 5,
            'mobile_enabled'  => 'no',
            'mobile_delay'    => 30,
            'frequency_hours' => 24,
            'background'      => '#ffffff',
            'text_color'      => '#1f2937',
            'button_color'    => '#2563eb',
            'button_text_color' => '#ffffff',
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
            'Exit Intent Settings',
            'Exit Intent',
            'manage_woocommerce',
            self::PAGE_SLUG,
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'wcs_exit_intent_group',
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

        $result = array();

        foreach ( array( 'enabled', 'mobile_enabled' ) as $key ) {
            $result[ $key ] = (
                isset( $input[ $key ] )
                && 'yes' === $input[ $key ]
            ) ? 'yes' : 'no';
        }

        $result['title'] = isset( $input['title'] )
            ? sanitize_text_field( wp_unslash( $input['title'] ) )
            : $defaults['title'];

        $result['message'] = isset( $input['message'] )
            ? sanitize_textarea_field( wp_unslash( $input['message'] ) )
            : $defaults['message'];

        $result['button_text'] = isset( $input['button_text'] )
            ? sanitize_text_field( wp_unslash( $input['button_text'] ) )
            : $defaults['button_text'];

        $result['button_url'] = isset( $input['button_url'] )
            ? esc_url_raw( wp_unslash( $input['button_url'] ), array( 'http', 'https' ) )
            : '';

        $limits = array(
            'delay'           => array( 0, 120 ),
            'mobile_delay'    => array( 5, 300 ),
            'frequency_hours' => array( 1, 720 ),
        );

        foreach ( $limits as $key => $range ) {
            $value = isset( $input[ $key ] )
                ? absint( $input[ $key ] )
                : $defaults[ $key ];

            $result[ $key ] = max(
                $range[0],
                min( $range[1], $value )
            );
        }

        foreach (
            array(
                'background',
                'text_color',
                'button_color',
                'button_text_color',
            ) as $key
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
