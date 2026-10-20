<?php
/**
 * WooCommerce Conversion Suite admin.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

class WCS_Admin {

    public function register_menu() {
        add_submenu_page(
            'woocommerce',
            'Conversion Suite',
            'Conversion Suite',
            'manage_woocommerce',
            'woocommerce-conversion-suite',
            array( $this, 'render_settings_page' )
        );
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'woocommerce-conversion-suite' ) );
        }

        require WCS_PLUGIN_PATH . 'admin/views/settings-page.php';
    }

    public function enqueue_assets( $hook_suffix ) {
        if ( 'woocommerce_page_woocommerce-conversion-suite' !== $hook_suffix ) {
            return;
        }

        wp_enqueue_style(
            'wcs-admin',
            WCS_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            WCS_VERSION
        );

        wp_enqueue_script(
            'wcs-admin',
            WCS_PLUGIN_URL . 'admin/js/admin.js',
            array(),
            WCS_VERSION,
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
