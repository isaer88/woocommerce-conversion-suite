<?php
/**
 * Quick Checkout frontend.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_Quick_Checkout {

    private $settings = array();

    public function __construct() {
        $this->settings = VELTRIXA_Quick_Checkout_Settings::get_settings();
    }

    public function register_hooks() {
        add_filter(
            'woocommerce_checkout_fields',
            array( $this, 'filter_checkout_fields' ),
            30
        );

        add_filter(
            'woocommerce_enable_order_notes_field',
            array( $this, 'filter_order_notes' ),
            30
        );
    }

    public function filter_checkout_fields( $fields ) {
        if ( ! is_array( $fields ) ) {
            return $fields;
        }

        if ( 'yes' === $this->settings['hide_company'] ) {
            unset( $fields['billing']['billing_company'] );
            unset( $fields['shipping']['shipping_company'] );
        }

        if ( 'yes' === $this->settings['hide_address_2'] ) {
            unset( $fields['billing']['billing_address_2'] );
            unset( $fields['shipping']['shipping_address_2'] );
        }

        if ( 'yes' === $this->settings['optional_postcode'] ) {
            foreach ( array( 'billing', 'shipping' ) as $group ) {
                $key = $group . '_postcode';

                if ( isset( $fields[ $group ][ $key ] ) ) {
                    $fields[ $group ][ $key ]['required'] = false;
                }
            }
        }

        if ( isset( $fields['billing']['billing_phone'] ) ) {
            $fields['billing']['billing_phone']['required'] =
                'yes' === $this->settings['require_phone'];
        }

        if ( isset( $fields['billing']['billing_email'] ) ) {
            // Preserve email for order communication and guest checkout.
            if ( 'yes' === $this->settings['require_email'] ) {
                $fields['billing']['billing_email']['required'] = true;
            }
        }

        if ( 'yes' === $this->settings['hide_order_notes'] ) {
            unset( $fields['order']['order_comments'] );
        }

        return $fields;
    }

    public function filter_order_notes( $enabled ) {
        if ( 'yes' === $this->settings['hide_order_notes'] ) {
            return false;
        }

        return $enabled;
    }
}
