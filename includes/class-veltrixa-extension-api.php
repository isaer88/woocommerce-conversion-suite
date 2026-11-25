<?php
/** Public extension contract. @package Veltrixa_Toolkit */
defined( 'ABSPATH' ) || exit;
final class VELTRIXA_Extension_API {
    const VERSION = '1.0.0';
    private static $ready = false;
    public static function initialize() {
        if ( self::$ready ) { return; }
        self::$ready = true;
        do_action( 'veltrixa_extension_api_ready', self::VERSION );
    }
    public static function is_ready() { return self::$ready; }
    public static function get_version() { return self::VERSION; }
    public static function get_menu_slug() { return VELTRIXA_Admin::MENU_SLUG; }
    public static function get_capability() { return 'manage_woocommerce'; }
}
