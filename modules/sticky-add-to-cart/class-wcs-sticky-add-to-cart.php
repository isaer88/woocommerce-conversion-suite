<?php
/**
 * Sticky Add to Cart frontend.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_Sticky_Add_To_Cart {

    public function register_hooks() {
        add_action(
            'wp_enqueue_scripts',
            array( $this, 'enqueue_assets' ),
            20
        );

        add_action(
            'wp_footer',
            array( $this, 'render_bar' ),
            20
        );
    }

    private function get_product() {
        if ( ! function_exists( 'is_product' ) || ! is_product() ) {
            return null;
        }

        $product = wc_get_product( get_queried_object_id() );

        if (
            ! $product
            || ! $product->is_type( array( 'simple', 'variable' ) )
            || ! $product->is_purchasable()
            || ! $product->is_in_stock()
        ) {
            return null;
        }

        return $product;
    }

    public function enqueue_assets() {
        if ( ! $this->get_product() ) {
            return;
        }

        $base = WCS_PLUGIN_URL . 'modules/sticky-add-to-cart/assets/';

        $css_file = __DIR__ . '/assets/css/sticky-add-to-cart.css';
        $js_file  = __DIR__ . '/assets/js/sticky-add-to-cart.js';

        // 1. Load CSS.
        wp_enqueue_style(
            'wcs-sticky-add-to-cart',
            $base . 'css/sticky-add-to-cart.css',
            array(),
            file_exists( $css_file ) ? filemtime( $css_file ) : '1.1.0'
        );

        // 2. Load module settings.
        $settings = WCS_Sticky_Add_To_Cart_Settings::get_settings();

        $background = sanitize_hex_color(
            $settings['background']
        ) ?: '#ffffff';

        $button_color = sanitize_hex_color(
            $settings['button_color']
        ) ?: '#2271b1';

        // 3. Generate custom CSS.
        $custom_css = sprintf(
            '#wcs-sticky-cart { background: %1$s; }'
            . '#wcs-sticky-cart .wcs-sticky-button { background: %2$s; border-color: %2$s; color: #ffffff; }',
            $background,
            $button_color
        );

        // 4. Hide on mobile when disabled.
        if ( 'no' === $settings['show_mobile'] ) {
            $custom_css .= '@media (max-width: 600px) { #wcs-sticky-cart { display: none !important; } }';
        }

        // 5. Add custom CSS after the stylesheet is registered.
        wp_add_inline_style(
            'wcs-sticky-add-to-cart',
            $custom_css
        );

        // 6. Load JavaScript.
        wp_enqueue_script(
            'wcs-sticky-add-to-cart',
            $base . 'js/sticky-add-to-cart.js',
            array(),
            file_exists( $js_file ) ? filemtime( $js_file ) : '1.1.0',
            true
        );
    }

    public function render_bar() {
        $product = $this->get_product();

        $settings = WCS_Sticky_Add_To_Cart_Settings::get_settings();

        $position_class = 'top' === $settings['position']
            ? 'wcs-sticky-cart--top'
            : 'wcs-sticky-cart--bottom';

        if ( ! $product ) {
            return;
        }

        $image = $product->get_image(
            'woocommerce_thumbnail',
            array( 'class' => 'wcs-sticky-image' )
        );

        ?>
        <div
            id="wcs-sticky-cart"
            class="wcs-sticky-cart <?php echo esc_attr( $position_class ); ?>"
            data-product-type="<?php echo esc_attr( $product->get_type() ); ?>"
            aria-hidden="true"
        >
            <div class="wcs-sticky-inner">

                <div class="wcs-sticky-product">
                    <?php if ( 'yes' === $settings['show_image'] ) : ?>
                        <div class="wcs-sticky-thumbnail">
                            <?php echo wp_kses_post( $image ); ?>
                        </div>
                    <?php endif; ?>

                    <div class="wcs-sticky-product-info">
                        <div class="wcs-sticky-title">
                            <?php echo esc_html( $product->get_name() ); ?>
                        </div>

                        <div
                            class="wcs-sticky-price"
                            aria-live="polite"
                        >
                            <?php echo wp_kses_post( $product->get_price_html() ); ?>
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    class="button alt wcs-sticky-button"
                    data-button-text="<?php echo esc_attr( $settings['button_text'] ); ?>"
                    disabled
                >
                    <?php
                    echo esc_html(
                        $product->is_type( 'variable' )
                            ? __( 'Choose options', 'woocommerce-conversion-suite' )
                            : __( 'Add to cart', 'woocommerce-conversion-suite' )
                    );
                    ?>
                </button>

            </div>
        </div>
        <?php
    }
}
