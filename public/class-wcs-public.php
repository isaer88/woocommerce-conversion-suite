<?php
/**
 * Публічна частина плагіна.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

class VELTRIXA_Public {

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
			VELTRIXA_PLUGIN_URL . 'public/css/public.css',
			array(),
			VELTRIXA_VERSION
		);

		wp_enqueue_script(
			'wcs-public',
			VELTRIXA_PLUGIN_URL . 'public/js/public.js',
			array(),
			VELTRIXA_VERSION,
			true
		);
	}
}