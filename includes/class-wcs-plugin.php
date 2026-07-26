<?php
/**
 * Основний клас плагіна.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

final class WCS_Plugin {

	/**
	 * Екземпляр класу.
	 *
	 * @var WCS_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Повертає екземпляр класу.
	 *
	 * @return WCS_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Забороняємо пряме створення класу.
	 */
	private function __construct() {}

	/**
	 * Запускає плагін.
	 *
	 * @return void
	 */
	public function run() {
		$this->load_dependencies();
		$this->register_hooks();
	}

	/**
	 * Підключає файли.
	 *
	 * @return void
	 */
	private function load_dependencies() {
		require_once WCS_PLUGIN_PATH . 'admin/class-wcs-admin.php';
		require_once WCS_PLUGIN_PATH . 'public/class-wcs-public.php';
	}

	/**
	 * Реєструє хуки.
	 *
	 * @return void
	 */
	private function register_hooks() {
		$admin  = new WCS_Admin();
		$public = new WCS_Public();

		add_action( 'admin_menu', array( $admin, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $admin, 'enqueue_assets' ) );

		add_action( 'wp_enqueue_scripts', array( $public, 'enqueue_assets' ) );
	}
}