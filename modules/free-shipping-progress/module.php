<?php
/**
 * Free Shipping Progress module.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_Free_Shipping_Progress_Module extends WCS_Module {

    protected $id = 'free-shipping-progress';
    protected $name = 'Free Shipping Progress';
    protected $description = 'Show progress toward a free shipping threshold.';
    protected $version = '1.0.0';
    protected $icon = 'dashicons-car';
    protected $settings_page = 'wcs-free-shipping-progress';

    public function register_admin_hooks() {
        if ( ! is_admin() ) {
            return;
        }

        require_once __DIR__ . '/class-wcs-free-shipping-progress-settings.php';

        $settings = new WCS_Free_Shipping_Progress_Settings();
        $settings->register_hooks();
    }

    public function register_hooks() {
        require_once __DIR__ . '/class-wcs-free-shipping-progress-settings.php';
        require_once __DIR__ . '/class-wcs-free-shipping-progress.php';

        $frontend = new WCS_Free_Shipping_Progress();
        $frontend->register_hooks();
    }
}

return new WCS_Free_Shipping_Progress_Module();
