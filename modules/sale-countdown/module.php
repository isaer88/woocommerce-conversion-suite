<?php
/**
 * Sale Countdown module.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_Sale_Countdown_Module extends WCS_Module {

    protected $id = 'sale-countdown';
    protected $name = 'Sale Countdown';
    protected $description = 'Countdown timer for scheduled WooCommerce sales.';
    protected $version = '1.0.0';
    protected $icon = 'dashicons-clock';
    protected $settings_page = 'wcs-sale-countdown';

    public function register_admin_hooks() {
        if ( ! is_admin() ) {
            return;
        }

        require_once __DIR__ . '/class-wcs-sale-countdown-settings.php';

        ( new WCS_Sale_Countdown_Settings() )->register_hooks();
    }

    public function register_hooks() {
        require_once __DIR__ . '/class-wcs-sale-countdown-settings.php';
        require_once __DIR__ . '/class-wcs-sale-countdown.php';

        ( new WCS_Sale_Countdown() )->register_hooks();
    }
}

return new WCS_Sale_Countdown_Module();
