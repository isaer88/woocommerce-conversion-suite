<?php
/**
 * WhatsApp Order settings.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_WhatsApp_Order_Settings {

    const OPTION_NAME = 'wcs_whatsapp_order_settings';
    const PAGE_SLUG   = 'wcs-whatsapp-order';

    public static function defaults() {
        return array(
            'enabled'        => 'no',
            'phone'          => '',
            'button_text'    => 'Order via WhatsApp',
            'message'        => "Hello! I would like to order:\n\nProduct: {product_name}\nVariation: {variation}\nQuantity: {quantity}\nPrice per item: {price}\nSKU: {sku}\nLink: {product_url}\n\nPlease confirm availability and the final total.",
            'auto_display'   => 'yes',
            'open_new_tab'   => 'yes',
            'button_color'   => '#25D366',
            'text_color'     => '#ffffff',
        );
    }

    public static function get_settings() {
        $saved = get_option( self::OPTION_NAME, array() );

        return wp_parse_args(
            is_array( $saved ) ? $saved : array(),
            self::defaults()
        );
    }

    public function register_hooks() {
        add_action( 'admin_menu', array( $this, 'register_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function register_page() {
        add_submenu_page(
            'woocommerce-conversion-suite',
            'WhatsApp Order Settings',
            'WhatsApp Order',
            'manage_woocommerce',
            self::PAGE_SLUG,
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'wcs_whatsapp_order_group',
            self::OPTION_NAME,
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize' ),
                'default'           => self::defaults(),
            )
        );
    }

    public function sanitize( $input ) {
        $input    = is_array( $input ) ? $input : array();
        $defaults = self::defaults();

        $result = array();

        foreach ( array( 'enabled', 'auto_display', 'open_new_tab' ) as $key ) {
            $result[ $key ] = (
                isset( $input[ $key ] )
                && 'yes' === $input[ $key ]
            ) ? 'yes' : 'no';
        }

        $phone = isset( $input['phone'] )
            ? (string) wp_unslash( $input['phone'] )
            : '';

        $result['phone'] = preg_replace( '/\D+/', '', $phone );

        if (
            '' !== $result['phone']
            && ! preg_match( '/^[1-9][0-9]{6,14}$/', $result['phone'] )
        ) {
            $result['phone'] = '';
            add_settings_error(
                self::OPTION_NAME,
                'invalid_phone',
                'Enter a valid international phone number, including the country code.',
                'error'
            );
        }

        $result['button_text'] = isset( $input['button_text'] )
            ? sanitize_text_field( wp_unslash( $input['button_text'] ) )
            : $defaults['button_text'];

        $result['message'] = isset( $input['message'] )
            ? sanitize_textarea_field( wp_unslash( $input['message'] ) )
            : $defaults['message'];

        foreach ( array( 'button_color', 'text_color' ) as $key ) {
            $color = isset( $input[ $key ] )
                ? sanitize_hex_color( $input[ $key ] )
                : false;

            $result[ $key ] = $color ?: $defaults[ $key ];
        }

        return $result;
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die(
                esc_html__( 'Permission denied.', 'veltrixa-toolkit-for-woocommerce' )
            );
        }

        require __DIR__ . '/views/settings-page.php';
    }
}
