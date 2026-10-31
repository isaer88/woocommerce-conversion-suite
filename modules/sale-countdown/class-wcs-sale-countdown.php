<?php
/**
 * Sale Countdown frontend.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_Sale_Countdown {

    private $settings;

    public function __construct() {
        $this->settings = VELTRIXA_Sale_Countdown_Settings::get_settings();
    }

    public function register_hooks() {
        add_action(
            'woocommerce_single_product_summary',
            array( $this, 'auto_display' ),
            11
        );

        add_shortcode(
            'wcs_sale_countdown',
            array( $this, 'shortcode' )
        );

        add_action(
            'wp_enqueue_scripts',
            array( $this, 'enqueue_assets' )
        );
    }

    public function enqueue_assets() {
        wp_localize_script(
            'wcs-sale-countdown',
            'wcsSaleCountdown',
            array(
                'serverTime' => time(),
            )
        );
        if ( ! is_product() ) {
            return;
        }

        $base = VELTRIXA_PLUGIN_URL . 'modules/sale-countdown/assets/';
        $path = __DIR__ . '/assets/';

        wp_enqueue_style(
            'wcs-sale-countdown',
            $base . 'css/sale-countdown.css',
            array(),
            file_exists( $path . 'css/sale-countdown.css' )
                ? filemtime( $path . 'css/sale-countdown.css' )
                : '1.0.0'
        );

        wp_enqueue_script(
            'wcs-sale-countdown',
            $base . 'js/sale-countdown.js',
            array(),
            file_exists( $path . 'js/sale-countdown.js' )
                ? filemtime( $path . 'js/sale-countdown.js' )
                : '1.0.0',
            true
        );

        $background = sanitize_hex_color( $this->settings['background'] );
        $text       = sanitize_hex_color( $this->settings['text_color'] );
        $accent     = sanitize_hex_color( $this->settings['accent_color'] );

        $css = sprintf(
            '.wcs-sale-countdown {
                --wcs-sc-bg: %s;
                --wcs-sc-text: %s;
                --wcs-sc-accent: %s;
            }',
            $background ?: '#f8f9fa',
            $text ?: '#222222',
            $accent ?: '#2271b1'
        );

        wp_add_inline_style( 'wcs-sale-countdown', $css );
    }

    public function auto_display() {
        if ( 'yes' !== $this->settings['auto_display'] ) {
            return;
        }

        echo $this->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    public function shortcode() {
        return $this->render();
    }

    private function render() {
        if ( ! function_exists( 'wc_get_product' ) || ! is_product() ) {
            return '';
        }

        $product = wc_get_product( get_queried_object_id() );

        if (
            ! $product
            || ! $product->is_type( 'simple' )
            || 'publish' !== $product->get_status()
            || ! $product->is_on_sale()
        ) {
            return '';
        }

        $end_date = $product->get_date_on_sale_to();

        if ( ! $end_date instanceof WC_DateTime ) {
            return '';
        }

        $end_timestamp = $end_date->getTimestamp();

        if ( $end_timestamp <= time() ) {
            return '';
        }

        ob_start();
        ?>
        <div
            class="wcs-sale-countdown"
            data-wcs-sale-countdown
            data-end="<?php echo esc_attr( $end_timestamp ); ?>"
            hidden
        >
            <div class="wcs-sale-countdown__title">
                <?php echo esc_html( $this->settings['title'] ); ?>
            </div>

            <div class="wcs-sale-countdown__timer">
                <?php
                $units = array(
                    'days'    => 'Days',
                    'hours'   => 'Hours',
                    'minutes' => 'Minutes',
                    'seconds' => 'Seconds',
                );

                foreach ( $units as $key => $label ) :
                    ?>
                    <div class="wcs-sale-countdown__unit">
                        <strong data-wcs-sc="<?php echo esc_attr( $key ); ?>">
                            00
                        </strong>
                        <span><?php echo esc_html( $label ); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }
}
