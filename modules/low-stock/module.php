<?php
/**
 * Low Stock module.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_Low_Stock_Module extends VELTRIXA_Module {

    protected $id = 'low-stock';
    protected $name = 'Low Stock';
    protected $description = 'Display real low-stock alerts on product pages.';
    protected $version = '1.0.0';
    protected $icon = 'dashicons-warning';
    protected $settings_page = 'wcs-low-stock';

    public function register_admin_hooks() {
        if ( ! is_admin() ) {
            return;
        }

        require_once __DIR__ . '/class-wcs-low-stock-settings.php';

        ( new VELTRIXA_Low_Stock_Settings() )->register_hooks();
    }

    public function register_hooks() {
        require_once __DIR__ . '/class-wcs-low-stock-settings.php';
        require_once __DIR__ . '/class-wcs-low-stock.php';

        ( new VELTRIXA_Low_Stock() )->register_hooks();
    }
}

return new VELTRIXA_Low_Stock_Module();
