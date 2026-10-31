<?php
/**
 * Recently Viewed frontend.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_Recently_Viewed {

    const AJAX_ACTION = 'wcs_recently_viewed_products';

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
            'wcs_recently_viewed',
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
            'wcsRecentlyViewed',
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
        if ( ! check_ajax_referer( 'veltrixa_recently_viewed_products', 'nonce', false ) ) {
            wp_send_json_error(
                array( 'message' => 'Invalid security token.' ),
                403
            );
        }

        if ( ! function_exists( 'wc_get_product' ) ) {
            wp_send_json_error( array( 'message' => 'WooCommerce unavailable.' ), 503 );
        }

        $raw = isset( $_POST['product_ids'] )
            ? wp_unslash( $_POST['product_ids'] )
            : array();

        if ( ! is_array( $raw ) || count( $raw ) > 20 ) {
            wp_send_json_error(
                array( 'message' => 'Invalid product IDs. Maximum 20 products allowed.' ),
                400
            );
        }

        foreach ( $raw as $raw_id ) {
            if ( ! is_scalar( $raw_id ) ) {
                wp_send_json_error(
                    array( 'message' => 'Invalid product ID.' ),
                    400
                );
            }
        }

        $ids = array_slice(
            array_values( array_unique( array_filter( array_map( 'absint', $raw ) ) ) ),
            0,
            20
        );

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
                'url'   => get_permalink( $product->get_id() ),
                'name'  => $product->get_name(),
                'image' => $product->get_image( 'woocommerce_thumbnail' ),
                'price' => $product->get_price_html(),
            );
        }

        wp_send_json_success( array( 'products' => $items ) );
    }
}
