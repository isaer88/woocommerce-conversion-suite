<?php
/**
 * One Click Order module bootstrap.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_One_Click_Order_Module extends VELTRIXA_Module {

    protected $id = 'one-click-order';
    protected $name = 'One Click Order';
    protected $description = 'Create quick WooCommerce order requests.';
    protected $version = '1.0.0';
    protected $icon = 'dashicons-cart';
    protected $settings_page = 'wcs-one-click-order';

    public function register_admin_hooks() {
        if ( ! is_admin() ) {
            return;
        }

        require_once __DIR__ . '/class-wcs-one-click-order-settings.php';

        ( new VELTRIXA_One_Click_Order_Settings() )->register_hooks();
    }

    public function register_hooks() {
        require_once __DIR__ . '/class-wcs-one-click-order-settings.php';
        require_once __DIR__ . '/class-wcs-one-click-order-ajax.php';
        require_once __DIR__ . '/class-wcs-one-click-order.php';

        ( new VELTRIXA_One_Click_Order() )->register_hooks();
        ( new VELTRIXA_One_Click_Order_Ajax() )->register_hooks();
    }
}

return new VELTRIXA_One_Click_Order_Module();
