<?php
/**
 * Frequently Bought Together module.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_FBT_Module extends VELTRIXA_Module {

    protected $id = 'frequently-bought-together';
    protected $name = 'Frequently Bought Together';
    protected $description = 'Offer manually selected complementary products.';
    protected $version = '1.0.0';
    protected $icon = 'dashicons-products';
    protected $settings_page = 'wcs-fbt';

    public function register_admin_hooks() {
        if ( ! is_admin() ) {
            return;
        }

        require_once __DIR__ . '/class-wcs-fbt-settings.php';
        require_once __DIR__ . '/class-wcs-fbt-product-fields.php';

        ( new VELTRIXA_FBT_Settings() )->register_hooks();
        ( new VELTRIXA_FBT_Product_Fields() )->register_hooks();
    }

    public function register_hooks() {
        require_once __DIR__ . '/class-wcs-fbt-settings.php';
        require_once __DIR__ . '/class-wcs-fbt.php';
        require_once __DIR__ . '/class-wcs-fbt-cart.php';

        ( new VELTRIXA_FBT() )->register_hooks();
        ( new VELTRIXA_FBT_Cart() )->register_hooks();
    }
}

return new VELTRIXA_FBT_Module();
