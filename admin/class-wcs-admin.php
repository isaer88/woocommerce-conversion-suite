<?php
/**
 * WooCommerce Conversion Suite admin.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

class VELTRIXA_Admin {

    const MENU_SLUG = 'conversion-suite-for-woocommerce';

    public function register_menu() {
        add_menu_page(
            'Veltrixa Toolkit for WooCommerce',
            'Veltrixa Toolkit',
            'manage_woocommerce',
            self::MENU_SLUG,
            array( $this, 'render_settings_page' ),
            'dashicons-cart',
            56
        );

        add_submenu_page(
            self::MENU_SLUG,
            'Dashboard',
            'Dashboard',
            'manage_woocommerce',
            self::MENU_SLUG,
            array( $this, 'render_settings_page' )
        );
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die(
                esc_html__(
                    'Permission denied.',
                    'veltrixa-toolkit-for-woocommerce'
                )
            );
        }

        require VELTRIXA_PLUGIN_PATH . 'admin/views/settings-page.php';
    }

    public function enqueue_assets( $hook_suffix ) {
        if (
            'toplevel_page_' . self::MENU_SLUG
            !== $hook_suffix
        ) {
            return;
        }

        wp_enqueue_style(
            'wcs-admin',
            VELTRIXA_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            VELTRIXA_VERSION
        );

        wp_enqueue_script(
            'wcs-admin',
            VELTRIXA_PLUGIN_URL . 'admin/js/admin.js',
            array(),
            VELTRIXA_VERSION,
            true
        );

        wp_localize_script(
            'wcs-admin',
            'wcsAdmin',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'wcs_toggle_module' ),
                'saving'  => 'Saving...',
                'saved'   => 'Module settings saved.',
                'error'   => 'Unable to save. Please try again.',
            )
        );
    }
}
