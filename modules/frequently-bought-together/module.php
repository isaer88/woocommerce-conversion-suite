<?php
/**
 * Frequently Bought Together module.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_FBT_Module extends WCS_Module {

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

        ( new WCS_FBT_Settings() )->register_hooks();
        ( new WCS_FBT_Product_Fields() )->register_hooks();
    }

    public function register_hooks() {
        require_once __DIR__ . '/class-wcs-fbt-settings.php';
        require_once __DIR__ . '/class-wcs-fbt.php';
        require_once __DIR__ . '/class-wcs-fbt-cart.php';

        ( new WCS_FBT() )->register_hooks();
        ( new WCS_FBT_Cart() )->register_hooks();
    }
}

return new WCS_FBT_Module();
