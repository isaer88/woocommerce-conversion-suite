<?php
/**
 * WhatsApp Order frontend.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_WhatsApp_Order {

    private $settings;

    public function __construct() {
        $this->settings = VELTRIXA_WhatsApp_Order_Settings::get_settings();
    }

    public function register_hooks() {
        if ( 'yes' !== $this->settings['enabled'] ) {
            return;
        }

        if (
            ! preg_match(
                '/^[1-9][0-9]{6,14}$/',
                (string) $this->settings['phone']
            )
        ) {
            return;
        }

        add_shortcode(
            'wcs_whatsapp_order',
            array( $this, 'shortcode' )
        );

        if ( 'yes' === $this->settings['auto_display'] ) {
            add_action(
                'woocommerce_after_add_to_cart_form',
                array( $this, 'auto_display' ),
                25
            );
        }

        add_action(
            'wp_enqueue_scripts',
            array( $this, 'enqueue_assets' )
        );
    }

    private function get_product() {
        if (
            ! function_exists( 'is_product' )
            || ! is_product()
            || ! function_exists( 'wc_get_product' )
        ) {
            return false;
        }

        $product = wc_get_product( get_queried_object_id() );

        if (
            ! $product
            || ! (
                $product->is_type( 'simple' )
                || $product->is_type( 'variable' )
            )
            || 'publish' !== $product->get_status()
            || ! $product->is_visible()
            || ! $product->is_purchasable()
            || ! $product->is_in_stock()
        ) {
            return false;
        }

        if (
            $product->is_type( 'simple' )
            && ! is_numeric( $product->get_price() )
        ) {
            return false;
        }

        return $product;
    }

    public function auto_display() {
        echo $this->render_button(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    public function shortcode( $atts = array() ) {
        return $this->render_button();
    }

    private function render_button() {
        $product = $this->get_product();

        if ( ! $product ) {
            return '';
        }

        $is_variable = $product->is_type( 'variable' );

        $data = array(
            'productId'   => $product->get_id(),
            'productName' => $product->get_name(),
            'productUrl'  => get_permalink( $product->get_id() ),
            'sku'         => $product->get_sku(),
            'price'       => $is_variable
                ? ''
                : wp_strip_all_tags(
                    wc_price(
                        wc_get_price_to_display( $product )
                    )
                ),
            'variable'    => $is_variable,
        );

        ob_start();
        ?>
        <div class="wcs-wa-order">
            <button
                type="button"
                class="wcs-wa-order__button"
                data-wcs-wa-order
                data-product="<?php echo esc_attr( wp_json_encode( $data ) ); ?>"
                <?php disabled( $is_variable ); ?>
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    width="21"
                    height="21"
                    fill="currentColor"
                    aria-hidden="true"
                >
                    <path d="M20.52 3.48A11.87 11.87 0 0 0 12.06 0C5.5 0 .15 5.34.15 11.91c0 2.1.55 4.15 1.6 5.96L.05 24l6.28-1.65a11.88 11.88 0 0 0 5.73 1.46h.01c6.56 0 11.9-5.34 11.91-11.91a11.83 11.83 0 0 0-3.46-8.42ZM12.07 21.8a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.73.98.99-3.64-.24-.38a9.86 9.86 0 0 1-1.52-5.26c0-5.46 4.44-9.9 9.9-9.9a9.84 9.84 0 0 1 7 2.9 9.83 9.83 0 0 1 2.9 7.01c0 5.46-4.45 9.88-9.91 9.88Zm5.43-7.4c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.62-.92-2.22-.24-.58-.48-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.2 5.08 4.49.71.31 1.27.49 1.7.63.71.23 1.36.2 1.87.12.57-.08 1.76-.72 2.01-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z"/>
                </svg>
                <span>
                    <?php echo esc_html( $this->settings['button_text'] ); ?>
                </span>
            </button>

            <?php if ( $is_variable ) : ?>
                <p class="wcs-wa-order__hint" aria-live="polite">
                    <?php esc_html_e(
                        'Select product options first.',
                        'veltrixa-toolkit-for-woocommerce'
                    ); ?>
                </p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function enqueue_assets() {
        if ( ! $this->get_product() ) {
            return;
        }

        $base = VELTRIXA_PLUGIN_URL . 'modules/whatsapp-order/assets/';

        $css = __DIR__ . '/assets/css/whatsapp-order.css';
        $js  = __DIR__ . '/assets/js/whatsapp-order.js';

        wp_enqueue_style(
            'wcs-whatsapp-order',
            $base . 'css/whatsapp-order.css',
            array(),
            file_exists( $css ) ? filemtime( $css ) : '1.0.0'
        );

        wp_enqueue_script(
            'wcs-whatsapp-order',
            $base . 'js/whatsapp-order.js',
            array( 'jquery' ),
            file_exists( $js ) ? filemtime( $js ) : '1.0.0',
            true
        );

        $config = array(
            'phone'      => $this->settings['phone'],
            'message'    => $this->settings['message'],
            'newTab'     => 'yes' === $this->settings['open_new_tab'],
            'currency'   => get_woocommerce_currency(),
            'symbol'     => get_woocommerce_currency_symbol(),
            'decimals'   => wc_get_price_decimals(),
            'decimalSep' => wc_get_price_decimal_separator(),
            'thousandSep'=> wc_get_price_thousand_separator(),
            'priceFormat'=> get_woocommerce_price_format(),
        );

        wp_add_inline_script(
            'wcs-whatsapp-order',
            'window.wcsWhatsAppOrder = ' .
            wp_json_encode(
                $config,
                JSON_HEX_TAG |
                JSON_HEX_AMP |
                JSON_HEX_APOS |
                JSON_HEX_QUOT
            ) . ';',
            'before'
        );

        $background = sanitize_hex_color(
            $this->settings['button_color']
        ) ?: '#25D366';

        $color = sanitize_hex_color(
            $this->settings['text_color']
        ) ?: '#ffffff';

        wp_add_inline_style(
            'wcs-whatsapp-order',
            sprintf(
                '.wcs-wa-order {
                    --wcs-wa-bg: %s;
                    --wcs-wa-text: %s;
                }',
                $background,
                $color
            )
        );
    }
}
