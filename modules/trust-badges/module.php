<?php
/**
 * Trust Badges module.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_Trust_Badges_Module extends WCS_Module {

    protected $id = 'trust-badges';
    protected $name = 'Trust Badges';
    protected $description = 'Display configurable trust messages near Add to Cart.';
    protected $version = '1.0.0';
    protected $icon = 'dashicons-shield';
    protected $settings_page = 'wcs-trust-badges';

    public function register_admin_hooks() {
        if ( ! is_admin() ) {
            return;
        }

        require_once __DIR__ . '/class-wcs-trust-badges-settings.php';

        ( new WCS_Trust_Badges_Settings() )->register_hooks();
    }

    public function register_hooks() {
        require_once __DIR__ . '/class-wcs-trust-badges-settings.php';
        require_once __DIR__ . '/class-wcs-trust-badges.php';

        ( new WCS_Trust_Badges() )->register_hooks();
    }
}

return new WCS_Trust_Badges_Module();
