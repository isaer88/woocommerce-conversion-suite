<?php
/**
 * Exit Intent module bootstrap.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_Exit_Intent_Module extends WCS_Module {

    protected $id = 'exit-intent';
    protected $name = 'Exit Intent';
    protected $description = 'Display a configurable exit-intent popup.';
    protected $version = '1.0.0';
    protected $icon = 'dashicons-external';
    protected $settings_page = 'wcs-exit-intent';

    public function register_admin_hooks() {
        if ( ! is_admin() ) {
            return;
        }

        require_once __DIR__ . '/class-wcs-exit-intent-settings.php';

        ( new WCS_Exit_Intent_Settings() )->register_hooks();
    }

    public function register_hooks() {
        require_once __DIR__ . '/class-wcs-exit-intent-settings.php';
        require_once __DIR__ . '/class-wcs-exit-intent.php';

        ( new WCS_Exit_Intent() )->register_hooks();
    }
}

return new WCS_Exit_Intent_Module();
