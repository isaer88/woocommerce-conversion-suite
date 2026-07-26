<?php
/**
 * Адміністративна частина.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

class WCS_Admin {

	/**
	 * Додає сторінку плагіна в меню WooCommerce.
	 *
	 * @return void
	 */
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

	/**
	 * Виводить сторінку налаштувань.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		require WCS_PLUGIN_PATH . 'admin/views/settings-page.php';
	}

	/**
	 * Підключає стилі та скрипти адмін-панелі.
	 *
	 * @param string $hook_suffix Поточна сторінка.
	 *
	 * @return void
	 */
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
	}
}