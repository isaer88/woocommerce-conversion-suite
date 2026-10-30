<?php
/**
 * Recently Viewed module.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_Recently_Viewed_Module extends VELTRIXA_Module {

    protected $id = 'recently-viewed';
    protected $name = 'Recently Viewed';
    protected $description = 'Display recently viewed WooCommerce products.';
    protected $version = '1.0.0';
    protected $icon = 'dashicons-visibility';
    protected $settings_page = 'wcs-recently-viewed';

    public function register_admin_hooks() {
        if ( ! is_admin() ) {
            return;
        }

        require_once __DIR__ . '/class-wcs-recently-viewed-settings.php';

        $settings = new VELTRIXA_Recently_Viewed_Settings();
        $settings->register_hooks();
    }

    public function register_hooks() {
        require_once __DIR__ . '/class-wcs-recently-viewed-settings.php';
        require_once __DIR__ . '/class-wcs-recently-viewed.php';

        $frontend = new VELTRIXA_Recently_Viewed();
        $frontend->register_hooks();
    }
}

return new VELTRIXA_Recently_Viewed_Module();
