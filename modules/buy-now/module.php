
<?php
/**
 * Buy Now module bootstrap.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_Buy_Now_Module extends WCS_Module {

    protected $id = 'buy-now';
    protected $name = 'Buy Now';
    protected $description = 'Direct checkout from product pages.';
    protected $version = '1.0.1';
    protected $icon = 'dashicons-cart';
    protected $settings_page = 'wcs-buy-now';

    public function register_admin_hooks() {
        if ( ! is_admin() ) {
            return;
        }

        require_once __DIR__ . '/class-wcs-buy-now-settings.php';

        $settings = new WCS_Buy_Now_Settings();
        $settings->register_hooks();
    }

    public function register_hooks() {
        require_once __DIR__ . '/class-wcs-buy-now.php';

        $frontend = new WCS_Buy_Now();
        $frontend->register_hooks();
    }
}

return new WCS_Buy_Now_Module();
