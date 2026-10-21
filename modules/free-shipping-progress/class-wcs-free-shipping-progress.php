<?php
/**
 * Free Shipping Progress frontend.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_Free_Shipping_Progress {

    private $settings;

    public function __construct() {
        $this->settings = WCS_Free_Shipping_Progress_Settings::get_settings();
    }

    public function register_hooks() {
        add_action(
            'woocommerce_before_cart',
            array( $this, 'display_cart' ),
            15
        );

        add_action(
            'woocommerce_before_checkout_form',
            array( $this, 'display_checkout' ),
            15
        );

        add_shortcode(
            'wcs_free_shipping_progress',
            array( $this, 'shortcode' )
        );

        add_filter(
            'woocommerce_add_to_cart_fragments',
            array( $this, 'cart_fragments' )
        );

        add_action(
            'wp_enqueue_scripts',
            array( $this, 'enqueue_assets' )
        );
    }

    public function enqueue_assets() {
        if ( is_admin() ) {
            return;
        }

        $file = __DIR__ . '/assets/css/free-shipping-progress.css';

        wp_enqueue_style(
            'wcs-free-shipping-progress',
            WCS_PLUGIN_URL . 'modules/free-shipping-progress/assets/css/free-shipping-progress.css',
            array(),
            file_exists( $file ) ? filemtime( $file ) : '1.0.0'
        );

        $color = sanitize_hex_color( $this->settings['bar_color'] );

        wp_add_inline_style(
            'wcs-free-shipping-progress',
            '.wcs-fsp__fill { background-color: ' .
            ( $color ?: '#2271b1' ) . '; }'
        );
    }

    public function display_cart() {
        if ( 'yes' === $this->settings['show_cart'] ) {
            echo $this->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }

    public function display_checkout() {
        if ( 'yes' === $this->settings['show_checkout'] ) {
            echo $this->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }

    public function shortcode() {
        return $this->render();
    }

    private function render() {
        if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
            return '';
        }

        if ( WC()->cart->is_empty() ) {
            return '';
        }

        $threshold = (float) $this->settings['threshold'];

        if ( $threshold <= 0 ) {
            return '';
        }

        $subtotal = max(
            0,
            (float) WC()->cart->get_cart_contents_total()
        );

        $remaining = max( 0, $threshold - $subtotal );

        $progress = min(
            100,
            max( 0, ( $subtotal / $threshold ) * 100 )
        );

        if ( $remaining > 0 ) {
            $message = str_replace(
                '{amount}',
                wp_strip_all_tags( wc_price( $remaining ) ),
                $this->settings['message']
            );
        } else {
            $message = $this->settings['success'];
        }

        ob_start();
        ?>
        <div class="wcs-fsp">
            <div class="wcs-fsp__message">
                <?php echo esc_html( $message ); ?>
            </div>

            <div
                class="wcs-fsp__track"
                role="progressbar"
                aria-label="Free shipping progress"
                aria-valuemin="0"
                aria-valuemax="100"
                aria-valuenow="<?php echo esc_attr( round( $progress ) ); ?>"
            >
                <div
                    class="wcs-fsp__fill"
                    style="width: <?php echo esc_attr( $progress ); ?>%;"
                ></div>
            </div>

            <div class="wcs-fsp__values">
                <span><?php echo wp_kses_post( wc_price( $subtotal ) ); ?></span>
                <span><?php echo wp_kses_post( wc_price( $threshold ) ); ?></span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function cart_fragments( $fragments ) {
        $fragments['.wcs-fsp'] = $this->render();

        return $fragments;
    }
}
