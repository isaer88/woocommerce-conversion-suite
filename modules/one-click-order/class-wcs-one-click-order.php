<?php
/**
 * One Click Order frontend.
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_One_Click_Order {

    private $settings;

    public function __construct() {
        $this->settings = VELTRIXA_One_Click_Order_Settings::get_settings();
    }

    public function register_hooks() {
        if ( 'yes' !== $this->settings['enabled'] ) {
            return;
        }

        add_action(
            'woocommerce_after_add_to_cart_form',
            array( $this, 'render_button' ),
            15
        );

        add_action(
            'wp_footer',
            array( $this, 'render_modal' ),
            40
        );

        add_action(
            'wp_enqueue_scripts',
            array( $this, 'enqueue_assets' )
        );
    }

    private function get_product() {
        if ( ! function_exists( 'is_product' ) || ! is_product() ) {
            return false;
        }

        $product = wc_get_product( get_queried_object_id() );

        if (
            ! $product
            || ! $product->is_type( 'simple' )
            || 'publish' !== $product->get_status()
            || ! $product->is_visible()
            || ! $product->is_purchasable()
            || ! $product->is_in_stock()
            || $product->backorders_allowed()
            || ! is_numeric( $product->get_price() )
            || ( wc_tax_enabled() && $product->is_taxable() )
        ) {
            return false;
        }

        return $product;
    }

    public function render_button() {
        if ( ! $this->get_product() ) {
            return;
        }

        ?>
        <div class="wcs-oco-trigger">
            <button
                type="button"
                class="wcs-oco-button"
                data-wcs-oco-open
            >
                <?php echo esc_html( $this->settings['button_text'] ); ?>
            </button>
        </div>
        <?php
    }

    public function render_modal() {
        $product = $this->get_product();

        if ( ! $product ) {
            return;
        }

        $settings = $this->settings;

        require __DIR__ . '/views/quick-order-modal.php';
    }

    public function enqueue_assets() {
        if ( ! $this->get_product() ) {
            return;
        }

        $base = VELTRIXA_PLUGIN_URL . 'modules/one-click-order/assets/';

        $css = __DIR__ . '/assets/css/one-click-order.css';
        $js  = __DIR__ . '/assets/js/one-click-order.js';

        wp_enqueue_style(
            'wcs-one-click-order',
            $base . 'css/one-click-order.css',
            array(),
            file_exists( $css ) ? filemtime( $css ) : '1.0.0'
        );

        wp_enqueue_script(
            'wcs-one-click-order',
            $base . 'js/one-click-order.js',
            array(),
            file_exists( $js ) ? filemtime( $js ) : '1.0.0',
            true
        );

        wp_add_inline_script(
            'wcs-one-click-order',
            'window.wcsOneClickOrder = ' . wp_json_encode(
                array(
                    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce( 'wcs_oco_create_order' ),
                    'successText' => $this->settings['success_text'],
                )
            ) . ';',
            'before'
        );

        $button = sanitize_hex_color(
            $this->settings['button_color']
        ) ?: '#2563eb';

        $text = sanitize_hex_color(
            $this->settings['text_color']
        ) ?: '#ffffff';

        wp_add_inline_style(
            'wcs-one-click-order',
            sprintf(
                '.wcs-oco {
                    --wcs-oco-button: %s;
                    --wcs-oco-text: %s;
                }',
                $button,
                $text
            )
        );
    }
}
