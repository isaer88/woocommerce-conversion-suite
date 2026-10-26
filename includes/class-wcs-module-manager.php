<?php
/**
 * Conversion Suite module manager.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_Module_Manager {

    const OPTION_NAME = 'wcs_enabled_modules';

    private $modules = array();
    private $initialized = false;

    public function __construct() {
        add_action(
            'wp_ajax_wcs_toggle_module',
            array( $this, 'ajax_toggle_module' )
        );
    }

    public function register_module( WCS_Module $module ) {
        $id = $module->get_id();

        if ( ! $id || isset( $this->modules[ $id ] ) ) {
            return false;
        }

        $this->modules[ $id ] = $module;

        do_action( 'wcs_module_registered', $module );

        return true;
    }

    public function get_modules() {
        return $this->modules;
    }

    public function get_module( $id ) {
        $id = sanitize_key( $id );

        return isset( $this->modules[ $id ] )
            ? $this->modules[ $id ]
            : null;
    }

    public static function get_enabled_modules() {
        $enabled = get_option(
            self::OPTION_NAME,
            array( 'buy-now' )
        );

        if ( ! is_array( $enabled ) ) {
            return array();
        }

        return array_values(
            array_unique(
                array_filter(
                    array_map( 'sanitize_key', $enabled )
                )
            )
        );
    }

    public static function is_module_enabled( $id ) {
        return in_array(
            sanitize_key( $id ),
            self::get_enabled_modules(),
            true
        );
    }

    public static function enable_module( $id ) {
        $id = sanitize_key( $id );

        if ( ! $id ) {
            return false;
        }

        $enabled = self::get_enabled_modules();

        if ( in_array( $id, $enabled, true ) ) {
            return true;
        }

        $enabled[] = $id;

        update_option( self::OPTION_NAME, $enabled );

        return self::is_module_enabled( $id );
    }

    public static function disable_module( $id ) {
        $id = sanitize_key( $id );

        $enabled = self::get_enabled_modules();

        if ( ! in_array( $id, $enabled, true ) ) {
            return true;
        }

        $enabled = array_values(
            array_diff( $enabled, array( $id ) )
        );

        update_option( self::OPTION_NAME, $enabled );

        return ! self::is_module_enabled( $id );
    }

    public function ajax_toggle_module() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error(
                array( 'message' => 'Permission denied.' ),
                403
            );
        }

        if (
            ! check_ajax_referer(
                'wcs_toggle_module',
                'nonce',
                false
            )
        ) {
            wp_send_json_error(
                array( 'message' => 'Security check failed. Reload the page.' ),
                403
            );
        }

        $id = isset( $_POST['module_id'] )
            ? sanitize_key( wp_unslash( $_POST['module_id'] ) )
            : '';

        $enabled = isset( $_POST['enabled'] )
            ? sanitize_text_field( wp_unslash( $_POST['enabled'] ) )
            : '';

        $module = $this->get_module( $id );

        if ( ! $module ) {
            wp_send_json_error(
                array( 'message' => 'Module not found.' ),
                404
            );
        }

        if ( $module->is_pro() ) {
            wp_send_json_error(
                array( 'message' => 'This module requires Pro.' ),
                403
            );
        }

        if ( ! in_array( $enabled, array( '0', '1' ), true ) ) {
            wp_send_json_error(
                array( 'message' => 'Invalid module state.' ),
                400
            );
        }

        $success = '1' === $enabled
            ? self::enable_module( $id )
            : self::disable_module( $id );

        if ( ! $success ) {
            wp_send_json_error(
                array( 'message' => 'Could not save module state.' ),
                500
            );
        }

        wp_send_json_success(
            array(
                'module_id' => $id,
                'enabled'   => self::is_module_enabled( $id ),
                'message'   => 'Module state saved.',
            )
        );
    }

    public function initialize_admin_modules() {
        if ( ! is_admin() ) {
            return;
        }

        foreach ( $this->modules as $module ) {
            $module->register_admin_hooks();
        }
    }

    public function initialize_modules() {
        if ( $this->initialized ) {
            return;
        }

        $this->initialized = true;

        foreach ( $this->modules as $module ) {
            if ( ! $module->is_enabled() ) {
                continue;
            }

            $module->register_hooks();

            do_action( 'wcs_module_initialized', $module );
        }
    }
}
