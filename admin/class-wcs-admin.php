<?php
/**
 * Veltrixa Toolkit admin.
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
        $dashboard = 'toplevel_page_' . self::MENU_SLUG === $hook_suffix;
        $module_page = false;
        $manager = VELTRIXA_Plugin::instance()->get_module_manager();
        if ( $manager ) {
            foreach ( $manager->get_modules() as $module ) {
                $url = $module->get_settings_url();
                $query = wp_parse_url( $url, PHP_URL_QUERY );
                if ( ! is_string( $query ) ) {
                    continue;
                }
                parse_str( $query, $args );
                if ( isset( $args['page'] ) && is_string( $args['page'] ) && get_plugin_page_hookname( $args['page'], self::MENU_SLUG ) === $hook_suffix ) {
                    $module_page = true;
                    break;
                }
            }
        }
        if ( ! $dashboard && ! $module_page ) {
            return;
        }

        wp_enqueue_style(
            'wcs-admin',
            VELTRIXA_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            VELTRIXA_VERSION
        );

        if ( ! $dashboard ) {
            return;
        }

        wp_enqueue_script(
            'wcs-admin',
            VELTRIXA_PLUGIN_URL . 'admin/js/admin.js',
            array(),
            VELTRIXA_VERSION,
            true
        );

        wp_localize_script(
            'wcs-admin',
            'veltrixaAdmin',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'veltrixa_toggle_module' ),
                'saving'  => 'Saving...',
                'saved'   => 'Module settings saved.',
                'error'   => 'Unable to save. Please try again.',
            )
        );
    }
}
