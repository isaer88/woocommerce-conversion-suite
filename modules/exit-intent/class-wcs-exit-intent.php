<?php
/**
 * Exit Intent frontend.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_Exit_Intent {

    private $settings;

    public function __construct() {
        $this->settings = VELTRIXA_Exit_Intent_Settings::get_settings();
    }

    public function register_hooks() {
        if ( 'yes' !== $this->settings['enabled'] ) {
            return;
        }

        add_action(
            'wp_enqueue_scripts',
            array( $this, 'enqueue_assets' )
        );

        add_action(
            'wp_footer',
            array( $this, 'render_popup' ),
            30
        );
    }

    private function should_display() {
        if ( is_admin() || is_customize_preview() ) {
            return false;
        }

        if (
            ! function_exists( 'is_woocommerce' )
            || ! function_exists( 'is_cart' )
            || ! function_exists( 'is_checkout' )
            || ! function_exists( 'is_account_page' )
        ) {
            return false;
        }

        if (
            is_cart()
            || is_checkout()
            || is_account_page()
        ) {
            return false;
        }

        // WooCommerce shop, product and product category pages.
        if (
            ! is_woocommerce()
            && ! is_cart()
            && ! is_checkout()
        ) {
            return false;
        }

        return true;
    }

    public function enqueue_assets() {
        if ( ! $this->should_display() ) {
            return;
        }

        $base_url = VELTRIXA_PLUGIN_URL . 'modules/exit-intent/assets/';

        $css_file = __DIR__ . '/assets/css/exit-intent.css';
        $js_file  = __DIR__ . '/assets/js/exit-intent.js';

        wp_enqueue_style(
            'wcs-exit-intent',
            $base_url . 'css/exit-intent.css',
            array(),
            file_exists( $css_file ) ? filemtime( $css_file ) : '1.0.0'
        );

        wp_enqueue_script(
            'wcs-exit-intent',
            $base_url . 'js/exit-intent.js',
            array(),
            file_exists( $js_file ) ? filemtime( $js_file ) : '1.0.0',
            true
        );

        wp_add_inline_script(
            'wcs-exit-intent',
            'window.wcsExitIntent = ' . wp_json_encode(
                array(
                    'delay' => absint( $this->settings['delay'] ),
                    'mobileEnabled' => (
                        'yes' === $this->settings['mobile_enabled']
                    ),
                    'mobileDelay' => absint(
                        $this->settings['mobile_delay']
                    ),
                    'frequencyHours' => absint(
                        $this->settings['frequency_hours']
                    ),
                    'storageKey' => 'wcs_exit_intent_last_shown_v1',
                )
            ) . ';',
            'before'
        );

        $colors = array(
            'background'        => '#ffffff',
            'text_color'        => '#1f2937',
            'button_color'      => '#2563eb',
            'button_text_color' => '#ffffff',
        );

        foreach ( $colors as $key => $fallback ) {
            $colors[ $key ] = sanitize_hex_color(
                $this->settings[ $key ]
            ) ?: $fallback;
        }

        wp_add_inline_style(
            'wcs-exit-intent',
            sprintf(
                '.wcs-exit-intent {
                    --wcs-ei-background: %s;
                    --wcs-ei-text: %s;
                    --wcs-ei-button: %s;
                    --wcs-ei-button-text: %s;
                }',
                $colors['background'],
                $colors['text_color'],
                $colors['button_color'],
                $colors['button_text_color']
            )
        );
    }

    public function render_popup() {
        if ( ! $this->should_display() ) {
            return;
        }

        $settings = $this->settings;

        require __DIR__ . '/views/popup.php';
    }
}
