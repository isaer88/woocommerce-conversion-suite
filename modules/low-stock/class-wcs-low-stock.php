<?php
/**
 * Low Stock frontend.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_Low_Stock {

    private $settings;

    public function __construct() {
        $this->settings = VELTRIXA_Low_Stock_Settings::get_settings();
    }

    public function register_hooks() {
        add_action(
            'woocommerce_single_product_summary',
            array( $this, 'auto_display' ),
            25
        );

        add_shortcode(
            'wcs_low_stock',
            array( $this, 'shortcode' )
        );

        add_action(
            'wp_enqueue_scripts',
            array( $this, 'enqueue_assets' )
        );
    }

    public function enqueue_assets() {
        if ( ! is_product() ) {
            return;
        }

        $file = __DIR__ . '/assets/css/low-stock.css';

        wp_enqueue_style(
            'wcs-low-stock',
            VELTRIXA_PLUGIN_URL .
                'modules/low-stock/assets/css/low-stock.css',
            array(),
            file_exists( $file ) ? filemtime( $file ) : '1.0.0'
        );

        $background = sanitize_hex_color(
            $this->settings['background']
        ) ?: '#fff7ed';

        $text = sanitize_hex_color(
            $this->settings['text_color']
        ) ?: '#9a3412';

        $bar = sanitize_hex_color(
            $this->settings['bar_color']
        ) ?: '#ea580c';

        $css = sprintf(
            '.wcs-low-stock {
                --wcs-ls-background: %s;
                --wcs-ls-text: %s;
                --wcs-ls-bar: %s;
            }',
            $background,
            $text,
            $bar
        );

        wp_add_inline_style( 'wcs-low-stock', $css );
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
        if (
            ! function_exists( 'wc_get_product' )
            || ! is_product()
        ) {
            return '';
        }

        $product = wc_get_product( get_queried_object_id() );

        if (
            ! $product
            || ! $product->is_type( 'simple' )
            || 'publish' !== $product->get_status()
            || ! $product->is_visible()
            || ! $product->is_purchasable()
            || ! $product->is_in_stock()
            || ! $product->managing_stock()
            || $product->backorders_allowed()
        ) {
            return '';
        }

        $stock = $product->get_stock_quantity();

        if ( null === $stock ) {
            return '';
        }

        $stock = (int) $stock;

        $threshold = max(
            1,
            min( 9999, absint( $this->settings['threshold'] ) )
        );

        if ( $stock <= 0 || $stock > $threshold ) {
            return '';
        }

        $progress = min(
            100,
            max( 0, ( $stock / $threshold ) * 100 )
        );

        $message = str_replace(
            '{stock}',
            (string) $stock,
            $this->settings['message']
        );

        ob_start();
        ?>
        <div
            class="wcs-low-stock"
            role="status"
            aria-label="<?php echo esc_attr__( 'Low stock notice', 'veltrixa-toolkit-for-woocommerce' ); ?>"
        >
            <div class="wcs-low-stock__message">
                <span class="wcs-low-stock__icon" aria-hidden="true">
                    !
                </span>

                <span>
                    <?php echo esc_html( $message ); ?>
                </span>
            </div>

            <?php if ( 'yes' === $this->settings['show_bar'] ) : ?>
                <div
                    class="wcs-low-stock__track"
                    role="progressbar"
                    aria-label="<?php echo esc_attr__( 'Remaining stock relative to alert threshold', 'veltrixa-toolkit-for-woocommerce' ); ?>"
                    aria-valuemin="0"
                    aria-valuemax="<?php echo esc_attr( $threshold ); ?>"
                    aria-valuenow="<?php echo esc_attr( $stock ); ?>"
                >
                    <div
                        class="wcs-low-stock__fill"
                        style="width: <?php echo esc_attr( $progress ); ?>%;"
                    ></div>
                </div>

                <div class="wcs-low-stock__details">
                    <span>
                        <?php
                        printf(
                            /* translators: %d: Number of items remaining in stock. */
                            esc_html__( '%d items remaining', 'veltrixa-toolkit-for-woocommerce' ),
                            absint( $stock )
                        );
                        ?>
                    </span>

                    <span>
                        <?php
                        printf(
                            /* translators: %d: Low-stock alert threshold. */
                            esc_html__( 'Threshold: %d', 'veltrixa-toolkit-for-woocommerce' ),
                            absint( $threshold )
                        );
                        ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
        <?php

        return ob_get_clean();
    }
}
