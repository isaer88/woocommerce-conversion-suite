
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

    /**
     * Register a module.
     */
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

        if ( ! in_array( $id, $enabled, true ) ) {
            $enabled[] = $id;
        }

        return update_option( self::OPTION_NAME, $enabled );
    }

    public static function disable_module( $id ) {
        $id = sanitize_key( $id );

        $enabled = array_values(
            array_diff(
                self::get_enabled_modules(),
                array( $id )
            )
        );

        return update_option( self::OPTION_NAME, $enabled );
    }

    /**
     * Register admin hooks for every module.
     */
    public function initialize_admin_modules() {
        if ( ! is_admin() ) {
            return;
        }

        foreach ( $this->modules as $module ) {
            $module->register_admin_hooks();
        }
    }

    /**
     * Initialize enabled frontend modules.
     */
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
