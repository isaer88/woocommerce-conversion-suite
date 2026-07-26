<?php
/**
 * Публічна частина плагіна.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

class WCS_Public {

	/**
	 * Підключає стилі та скрипти на сайті.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! function_exists( 'is_woocommerce' ) ) {
			return;
		}

		if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) {
			return;
		}

		wp_enqueue_style(
			'wcs-public',
			WCS_PLUGIN_URL . 'public/css/public.css',
			array(),
			WCS_VERSION
		);

		wp_enqueue_script(
			'wcs-public',
			WCS_PLUGIN_URL . 'public/js/public.js',
			array(),
			WCS_VERSION,
			true
		);
	}
}