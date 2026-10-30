<?php
/**
 * Main plugin controller.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_Plugin {

    private static $instance = null;

    private $module_manager = null;

    private $did_run = false;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {}

    public function run() {
        if ( $this->did_run ) {
            return;
        }

        $this->did_run = true;

        $this->load_dependencies();
        $this->initialize_core();
        $this->register_modules();
        $this->initialize_modules();
    }

    private function load_dependencies() {
        require_once VELTRIXA_PLUGIN_PATH . 'includes/class-wcs-module.php';
        require_once VELTRIXA_PLUGIN_PATH . 'includes/class-wcs-module-manager.php';
        require_once VELTRIXA_PLUGIN_PATH . 'admin/class-wcs-admin.php';
        require_once VELTRIXA_PLUGIN_PATH . 'public/class-wcs-public.php';
    }

    private function initialize_core() {
        $this->module_manager = new VELTRIXA_Module_Manager();

        $admin  = new VELTRIXA_Admin();
        $public = new VELTRIXA_Public();

        add_action(
            'admin_menu',
            array( $admin, 'register_menu' )
        );

        add_action(
            'admin_enqueue_scripts',
            array( $admin, 'enqueue_assets' )
        );

        add_action(
            'wp_enqueue_scripts',
            array( $public, 'enqueue_assets' )
        );
    }

    /**
     * Automatically discover modules.
     *
     * Every module.php must return a VELTRIXA_Module instance.
     */
    private function register_modules() {
        if ( ! $this->module_manager ) {
            return;
        }

        $directory = VELTRIXA_PLUGIN_PATH . 'modules/';

        if ( ! is_dir( $directory ) ) {
            return;
        }

        $files = glob( $directory . '*/module.php' );

        if ( ! is_array( $files ) ) {
            return;
        }

        sort( $files, SORT_STRING );

        foreach ( $files as $file ) {
            $module = require $file;

            if ( $module instanceof VELTRIXA_Module ) {
                $this->module_manager->register_module( $module );
            }
        }
    }

    private function initialize_modules() {
        if ( ! $this->module_manager ) {
            return;
        }

        $this->module_manager->initialize_admin_modules();
        $this->module_manager->initialize_modules();
    }

    public function get_module_manager() {
        return $this->module_manager;
    }
}