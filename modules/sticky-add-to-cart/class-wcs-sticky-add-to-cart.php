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

        wp_enqueue_style(
            'wcs-sticky-add-to-cart',
            $base . 'css/sticky-add-to-cart.css',
            array(),
            file_exists( $css_file ) ? filemtime( $css_file ) : '1.0.0'
        );

        wp_enqueue_script(
            'wcs-sticky-add-to-cart',
            $base . 'js/sticky-add-to-cart.js',
            array(),
            file_exists( $js_file ) ? filemtime( $js_file ) : '1.0.0',
            true
        );
    }

    public function render_bar() {
        $product = $this->get_product();

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
            class="wcs-sticky-cart"
            data-product-type="<?php echo esc_attr( $product->get_type() ); ?>"
            aria-hidden="true"
        >
            <div class="wcs-sticky-inner">

                <div class="wcs-sticky-product">
                    <div class="wcs-sticky-thumbnail">
                        <?php echo wp_kses_post( $image ); ?>
                    </div>

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
