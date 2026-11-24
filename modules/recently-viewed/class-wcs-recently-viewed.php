<?php
/**
 * Recently Viewed frontend.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_Recently_Viewed {

    const AJAX_ACTION = 'veltrixa_recently_viewed_products';

    private $settings;

    public function __construct() {
        $this->settings = VELTRIXA_Recently_Viewed_Settings::get_settings();
    }

    public function register_hooks() {
        add_action(
            'wp_enqueue_scripts',
            array( $this, 'enqueue_assets' )
        );

        add_action(
            'woocommerce_after_single_product_summary',
            array( $this, 'auto_display' ),
            25
        );

        add_shortcode(
            'veltrixa_recently_viewed',
            array( $this, 'shortcode' )
        );

        add_action(
            'wp_ajax_' . self::AJAX_ACTION,
            array( $this, 'ajax_products' )
        );

        add_action(
            'wp_ajax_nopriv_' . self::AJAX_ACTION,
            array( $this, 'ajax_products' )
        );
    }

    public function enqueue_assets() {
        if ( is_admin() ) {
            return;
        }

        $base = VELTRIXA_PLUGIN_URL . 'modules/recently-viewed/assets/';
        $path = __DIR__ . '/assets/';

        $css = $path . 'css/recently-viewed.css';
        $js  = $path . 'js/recently-viewed.js';

        wp_enqueue_style(
            'wcs-recently-viewed',
            $base . 'css/recently-viewed.css',
            array(),
            file_exists( $css ) ? filemtime( $css ) : '1.0.0'
        );

        wp_enqueue_script(
            'wcs-recently-viewed',
            $base . 'js/recently-viewed.js',
            array(),
            file_exists( $js ) ? filemtime( $js ) : '1.0.0',
            true
        );

        $product_id = is_product()
            ? get_queried_object_id()
            : 0;

        wp_localize_script(
            'wcs-recently-viewed',
            'veltrixaRecentlyViewed',
            array(
                'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
                'productId'     => absint( $product_id ),
                'limit'         => absint( $this->settings['limit'] ),
                'showCurrent'   => 'yes' === $this->settings['show_current'],
                'requireConsent'=> 'yes' === $this->settings['require_consent'],
                'cookieName'    => 'wcs_recently_viewed',
                'nonce' => wp_create_nonce( 'veltrixa_recently_viewed_products' ),
            )
        );
    }

    public function auto_display() {
        if ( 'yes' !== $this->settings['auto_display'] ) {
            return;
        }

        echo $this->render_container(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    public function shortcode( $atts = array() ) {
        return $this->render_container();
    }

    private function render_container() {
        $title = $this->settings['title'];

        return sprintf(
            '<section class="wcs-rv" data-wcs-recently-viewed hidden>
                <h2 class="wcs-rv__title">%s</h2>
                <div class="wcs-rv__products" aria-live="polite"></div>
            </section>',
            esc_html( $title )
        );
    }

    public function ajax_products() {
        if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
            wp_send_json_error( array( 'message' => 'Method not allowed.' ), 405 );
        }
        if ( ! isset( $_POST['nonce'] ) || ! is_string( $_POST['nonce'] ) ) {
            wp_send_json_error( array( 'message' => 'Invalid security token.' ), 403 );
        }

        if ( ! check_ajax_referer( 'veltrixa_recently_viewed_products', 'nonce', false ) ) {
            wp_send_json_error(
                array( 'message' => 'Invalid security token.' ),
                403
            );
        }

        if ( ! function_exists( 'wc_get_product' ) ) {
            wp_send_json_error( array( 'message' => 'WooCommerce unavailable.' ), 503 );
        }

        if ( isset( $_POST['product_ids'] ) && ( ! is_array( $_POST['product_ids'] ) || count( $_POST['product_ids'] ) > 20 ) ) {
            wp_send_json_error( array( 'message' => 'Invalid product selection.' ), 400 );
        }

        $raw = isset( $_POST['product_ids'] ) ? wp_unslash( $_POST['product_ids'] ) : array();

        foreach ( $raw as $raw_id ) {
            if ( ! is_scalar( $raw_id ) ) {
                wp_send_json_error(
                    array( 'message' => 'Invalid product ID.' ),
                    400
                );
            }
        }

        $ids = array_slice(
            array_values( array_unique( array_filter( array_map( 'absint', array_map( 'sanitize_text_field', $raw ) ) ) ) ),
            0,
            20
        );

        // Prime bounded product caches without changing history order or visibility checks.
        if ( $ids && function_exists( '_prime_post_caches' ) ) {
            _prime_post_caches( $ids, true, true );
        }

        $limit = absint( $this->settings['limit'] );
        $items = array();

        foreach ( $ids as $id ) {
            if ( count( $items ) >= $limit ) {
                break;
            }

            $product = wc_get_product( $id );

            if (
                ! $product
                || 'publish' !== $product->get_status()
                || ! $product->is_visible()
            ) {
                continue;
            }

            $items[] = array(
                'id'    => $product->get_id(),
                'url'   => esc_url_raw( get_permalink( $product->get_id() ) ),
                'name'  => $product->get_name(),
                'image' => wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) ),
                'price' => wp_kses_post( $product->get_price_html() ),
            );
        }

        wp_send_json_success( array( 'products' => $items ) );
    }
}
