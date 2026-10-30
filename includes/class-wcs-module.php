<?php
/**
 * Base class for Conversion Suite modules.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

abstract class VELTRIXA_Module {

    protected $id = '';
    protected $name = '';
    protected $description = '';
    protected $version = '1.0.0';
    protected $icon = 'dashicons-admin-plugins';
    protected $is_pro = false;
    protected $settings_page = '';

    /**
     * Register frontend functionality.
     */
    abstract public function register_hooks();

    /**
     * Register admin functionality.
     *
     * Available even when the module is disabled.
     */
    public function register_admin_hooks() {}

    public function get_id() {
        return sanitize_key( $this->id );
    }

    public function get_name() {
        return $this->name;
    }

    public function get_description() {
        return $this->description;
    }

    public function get_version() {
        return $this->version;
    }

    public function get_icon() {
        return $this->icon;
    }

    public function is_pro() {
        return (bool) $this->is_pro;
    }

    public function get_settings_page() {
        return sanitize_key( $this->settings_page );
    }

    public function get_settings_url() {
        $page = $this->get_settings_page();

        if ( ! $page ) {
            return '';
        }

        return admin_url(
            'admin.php?page=' . rawurlencode( $page )
        );
    }

    public function is_enabled() {
        return class_exists( 'VELTRIXA_Module_Manager' )
            && VELTRIXA_Module_Manager::is_module_enabled( $this->get_id() );
    }

    public function to_array() {
        return array(
            'id'            => $this->get_id(),
            'name'          => $this->get_name(),
            'description'   => $this->get_description(),
            'version'       => $this->get_version(),
            'icon'          => $this->get_icon(),
            'is_pro'        => $this->is_pro(),
            'is_enabled'    => $this->is_enabled(),
            'settings_page' => $this->get_settings_page(),
            'settings_url'  => $this->get_settings_url(),
        );
    }
}
