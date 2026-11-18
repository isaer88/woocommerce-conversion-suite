<?php
/**
 * FBT frontend.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_FBT {

    private $settings;

    public function __construct() {
        $this->settings = VELTRIXA_FBT_Settings::get_settings();
    }

    public function register_hooks() {
        add_action(
            'woocommerce_after_single_product_summary',
            array( $this, 'auto_display' ),
            20
        );

        add_shortcode(
            'veltrixa_fbt',
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

        $base = VELTRIXA_PLUGIN_URL . 'modules/frequently-bought-together/assets/';
        $path = __DIR__ . '/assets/';

        wp_enqueue_style(
            'veltrixa-fbt',
            $base . 'css/fbt.css',
            array(),
            file_exists( $path . 'css/fbt.css' )
                ? filemtime( $path . 'css/fbt.css' )
                : '1.0.0'
        );

        wp_enqueue_script(
            'veltrixa-fbt',
            $base . 'js/fbt.js',
            array(),
            file_exists( $path . 'js/fbt.js' )
                ? filemtime( $path . 'js/fbt.js' )
                : '1.0.0',
            true
        );

        wp_localize_script(
            'veltrixa-fbt',
            'veltrixaFBT',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'veltrixa_fbt_add' ),
                'cartUrl'      => wc_get_cart_url(),
                'currencyCode' => get_woocommerce_currency(),
                'decimals'     => wc_get_price_decimals(),
            )
        );
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
        if ( ! is_product() ) {
            return '';
        }

        $product_id = get_queried_object_id();
        $product = wc_get_product( $product_id );

        if (
            ! $product
            || ! $product->is_type( 'simple' )
            || ! $product->is_purchasable()
            || ! $product->is_in_stock()
        ) {
            return '';
        }

        $ids = VELTRIXA_FBT_Meta::get_products( $product );

        // Frontend requests do not load the admin product-fields class.
        // Use the same persistent meta key directly.
        if ( ! is_array( $ids ) ) {
            $ids = array();
        }

        $related = array();
        $limit = max( 1, min( 3, absint( $this->settings['max_products'] ) ) );

        foreach ( $ids as $id ) {
            $item = wc_get_product( absint( $id ) );

            if (
                ! $item
                || ! $item->is_type( 'simple' )
                || ! $item->is_purchasable()
                || ! $item->is_in_stock()
                || ! $item->is_visible()
            ) {
                continue;
            }

            $related[] = $item;

            if ( count( $related ) >= $limit ) {
                break;
            }
        }

        if ( empty( $related ) ) {
            return '';
        }

        ob_start();

        require __DIR__ . '/views/product-bundle.php';

        return ob_get_clean();
    }
}
