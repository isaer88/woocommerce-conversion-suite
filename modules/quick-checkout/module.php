<?php
/**
 * Quick Checkout module bootstrap.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_Quick_Checkout_Module extends VELTRIXA_Module {

    protected $id = 'quick-checkout';
    protected $name = 'Quick Checkout';
    protected $description = 'Simplify the classic WooCommerce checkout form.';
    protected $version = '1.0.0';
    protected $icon = 'dashicons-yes-alt';
    protected $settings_page = 'wcs-quick-checkout';

    public function register_admin_hooks() {
        if ( ! is_admin() ) {
            return;
        }

        require_once __DIR__ . '/class-wcs-quick-checkout-settings.php';

        $settings = new VELTRIXA_Quick_Checkout_Settings();
        $settings->register_hooks();
    }

    public function register_hooks() {
        require_once __DIR__ . '/class-wcs-quick-checkout-settings.php';
        require_once __DIR__ . '/class-wcs-quick-checkout.php';

        $frontend = new VELTRIXA_Quick_Checkout();
        $frontend->register_hooks();
    }
}

return new VELTRIXA_Quick_Checkout_Module();
