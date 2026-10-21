<?php
/**
 * FBT product editor fields.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_FBT_Product_Fields {

    const META_KEY = '_wcs_fbt_products';

    public function register_hooks() {
        add_action(
            'woocommerce_product_options_general_product_data',
            array( $this, 'render_fields' )
        );

        add_action(
            'woocommerce_admin_process_product_object',
            array( $this, 'save_fields' )
        );
    }

    public function render_fields() {
        global $product_object;

        if ( ! $product_object instanceof WC_Product ) {
            return;
        }

        $selected = $product_object->get_meta(
            self::META_KEY,
            true
        );

        $selected = is_array( $selected ) ? $selected : array();

        echo '<div class="options_group show_if_simple">';

        echo '<p class="form-field">';
        echo '<label for="wcs_fbt_products">Frequently Bought Together</label>';

        echo '<select
            id="wcs_fbt_products"
            name="wcs_fbt_products[]"
            class="wc-product-search"
            multiple="multiple"
            style="width:50%;"
            data-placeholder="Search simple products..."
            data-action="woocommerce_json_search_products_and_variations"
            data-exclude="' . esc_attr( $product_object->get_id() ) . '">';

        foreach ( $selected as $product_id ) {
            $product = wc_get_product( absint( $product_id ) );

            if ( ! $product || ! $product->is_type( 'simple' ) ) {
                continue;
            }

            echo '<option value="' . esc_attr( $product->get_id() ) . '" selected="selected">';
            echo esc_html( $product->get_formatted_name() );
            echo '</option>';
        }

        echo '</select>';

        echo '<span class="description" style="display:block;margin-left:162px;">';
        $settings = get_option( 'wcs_fbt_settings', array() );
        $limit = isset( $settings['max_products'] )
            ? max( 1, min( 3, absint( $settings['max_products'] ) ) )
            : 3;

        echo esc_html(
            sprintf(
                'Select up to %d simple products to recommend with this product.',
                $limit
            )
        );
        echo '</span>';

        echo '</p>';
        echo '</div>';
    }

    public function save_fields( $product ) {
        if ( ! $product instanceof WC_Product ) {
            return;
        }

        if ( ! $product->is_type( 'simple' ) ) {
            $product->delete_meta_data( self::META_KEY );
            return;
        }

        $raw = isset( $_POST['wcs_fbt_products'] )
            ? wp_unslash( $_POST['wcs_fbt_products'] )
            : array();

        $raw = is_array( $raw ) ? $raw : array();

        $ids = array();

        foreach ( $raw as $id ) {
            $id = absint( $id );

            if (
                ! $id
                || $id === $product->get_id()
                || in_array( $id, $ids, true )
            ) {
                continue;
            }

            $related = wc_get_product( $id );

            if (
                ! $related
                || ! $related->is_type( 'simple' )
                || 'publish' !== $related->get_status()
            ) {
                continue;
            }

            $ids[] = $id;

            if ( count( $ids ) >= 3 ) {
                break;
            }
        }

        $product->update_meta_data( self::META_KEY, $ids );
    }
}
