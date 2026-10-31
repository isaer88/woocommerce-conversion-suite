<?php
/**
 * Trust Badges settings.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_Trust_Badges_Settings {

    const OPTION_NAME = 'wcs_trust_badges_settings';
    const PAGE_SLUG   = 'wcs-trust-badges';

    public static function icons() {
        return array(
            'shield'   => 'Shield',
            'truck'    => 'Shipping',
            'returns'  => 'Returns',
            'support'  => 'Support',
            'lock'     => 'Lock',
            'check'    => 'Check',
        );
    }

    public static function default_badges() {
        return array(
            array(
                'enabled' => 'no',
                'icon'    => 'shield',
                'text'    => 'Secure Checkout',
            ),
            array(
                'enabled' => 'no',
                'icon'    => 'truck',
                'text'    => 'Fast Shipping',
            ),
            array(
                'enabled' => 'no',
                'icon'    => 'returns',
                'text'    => 'Easy Returns',
            ),
            array(
                'enabled' => 'no',
                'icon'    => 'support',
                'text'    => 'Customer Support',
            ),
            array(
                'enabled' => 'no',
                'icon'    => 'lock',
                'text'    => 'Protected Payment',
            ),
            array(
                'enabled' => 'no',
                'icon'    => 'check',
                'text'    => 'Quality Checked',
            ),
        );
    }

    public static function defaults() {
        return array(
            'auto_display' => 'yes',
            'background'   => '#f8fafc',
            'text_color'   => '#334155',
            'icon_color'   => '#15803d',
            'badges'       => self::default_badges(),
        );
    }

    public static function get_settings() {
        $saved = get_option( self::OPTION_NAME, array() );

        if ( ! is_array( $saved ) ) {
            $saved = array();
        }

        $settings = wp_parse_args( $saved, self::defaults() );

        if ( ! isset( $settings['badges'] ) || ! is_array( $settings['badges'] ) ) {
            $settings['badges'] = self::default_badges();
        }

        return $settings;
    }

    public function register_hooks() {
        add_action( 'admin_menu', array( $this, 'register_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function register_page() {
        add_submenu_page(
            'woocommerce-conversion-suite',
            'Trust Badges Settings',
            'Trust Badges',
            'manage_woocommerce',
            self::PAGE_SLUG,
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'wcs_trust_badges_group',
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
            'auto_display' => (
                isset( $input['auto_display'] )
                && 'yes' === $input['auto_display']
            ) ? 'yes' : 'no',
        );

        foreach ( array( 'background', 'text_color', 'icon_color' ) as $key ) {
            $color = isset( $input[ $key ] )
                ? sanitize_hex_color( $input[ $key ] )
                : false;

            $result[ $key ] = $color ?: $defaults[ $key ];
        }

        $result['badges'] = array();
        $allowed_icons = array_keys( self::icons() );
        $default_badges = self::default_badges();

        foreach ( $default_badges as $index => $default_badge ) {
            $badge = isset( $input['badges'][ $index ] )
                && is_array( $input['badges'][ $index ] )
                    ? $input['badges'][ $index ]
                    : array();

            $icon = isset( $badge['icon'] )
                ? sanitize_key( $badge['icon'] )
                : $default_badge['icon'];

            $result['badges'][] = array(
                'enabled' => (
                    isset( $badge['enabled'] )
                    && 'yes' === $badge['enabled']
                ) ? 'yes' : 'no',

                'icon' => in_array( $icon, $allowed_icons, true )
                    ? $icon
                    : $default_badge['icon'],

                'text' => isset( $badge['text'] )
                    ? sanitize_text_field( wp_unslash( $badge['text'] ) )
                    : $default_badge['text'],
            );
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
