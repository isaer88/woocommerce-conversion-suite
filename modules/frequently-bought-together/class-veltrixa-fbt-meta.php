<?php
/**
 * FBT product metadata compatibility.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_FBT_Meta {

    const META_KEY = '_veltrixa_fbt_products';
    const LEGACY_META_KEY = '_wcs_fbt_products';

    public static function get_products( $product ) {
        if ( ! $product instanceof WC_Product ) {
            return array();
        }

        if ( $product->meta_exists( self::META_KEY ) ) {
            $ids = $product->get_meta( self::META_KEY, true );
        } else {
            $ids = $product->get_meta( self::LEGACY_META_KEY, true );
        }

        return is_array( $ids ) ? $ids : array();
    }
}