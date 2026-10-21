<?php
/**
 * Sticky Add to Cart module bootstrap.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_Sticky_Add_To_Cart_Module extends WCS_Module {

    protected $id = 'sticky-add-to-cart';
    protected $name = 'Sticky Add to Cart';
    protected $description = 'A floating purchase bar on product pages.';
    protected $version = '1.1.0';
    protected $icon = 'dashicons-cart';
    protected $settings_page = 'wcs-sticky-add-to-cart';

    public function register_admin_hooks() {
        if ( ! is_admin() ) {
            return;
        }

        require_once __DIR__ . '/class-wcs-sticky-add-to-cart-settings.php';

        $settings = new WCS_Sticky_Add_To_Cart_Settings();
        $settings->register_hooks();
    }

    public function register_hooks() {
        require_once __DIR__ . '/class-wcs-sticky-add-to-cart-settings.php';
        require_once __DIR__ . '/class-wcs-sticky-add-to-cart.php';

        $frontend = new WCS_Sticky_Add_To_Cart();
        $frontend->register_hooks();
    }
}

return new WCS_Sticky_Add_To_Cart_Module();