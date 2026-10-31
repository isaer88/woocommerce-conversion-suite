<?php
/**
 * WhatsApp Order module.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_WhatsApp_Order_Module extends VELTRIXA_Module {

    protected $id = 'whatsapp-order';
    protected $name = 'WhatsApp Order';
    protected $description = 'Let customers contact your store about products via WhatsApp.';
    protected $version = '1.0.0';
    protected $icon = 'dashicons-whatsapp';
    protected $settings_page = 'wcs-whatsapp-order';

    public function register_admin_hooks() {
        if ( ! is_admin() ) {
            return;
        }

        require_once __DIR__ . '/class-wcs-whatsapp-order-settings.php';

        ( new VELTRIXA_WhatsApp_Order_Settings() )->register_hooks();
    }

    public function register_hooks() {
        require_once __DIR__ . '/class-wcs-whatsapp-order-settings.php';
        require_once __DIR__ . '/class-wcs-whatsapp-order.php';

        ( new VELTRIXA_WhatsApp_Order() )->register_hooks();
    }
}

return new VELTRIXA_WhatsApp_Order_Module();
