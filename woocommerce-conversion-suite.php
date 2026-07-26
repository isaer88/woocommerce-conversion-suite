<?php
/**
 * Plugin Name: WooCommerce Conversion Suite
 * Plugin URI: https://example.com/woocommerce-conversion-suite
 * Description: Набір інструментів для підвищення конверсії та продажів у WooCommerce.
 * Version: 0.1.0
 * Author: Oleksandr Yeremenko
 * Author URI: https://example.com
 * Text Domain: woocommerce-conversion-suite
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 8.0
 * WC tested up to: 10.0
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

define( 'WCS_VERSION', '0.1.0' );
define( 'WCS_PLUGIN_FILE', __FILE__ );
define( 'WCS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WCS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WCS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Перевіряє наявність WooCommerce.
 */
function wcs_is_woocommerce_active() {
	return class_exists( 'WooCommerce' );
}

/**
 * Показує повідомлення, якщо WooCommerce не активований.
 */
function wcs_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<strong>WooCommerce Conversion Suite</strong>
			потребує встановленого та активованого WooCommerce.
		</p>
	</div>
	<?php
}

/**
 * Запуск плагіна.
 */
function wcs_run_plugin() {
	if ( ! wcs_is_woocommerce_active() ) {
		add_action( 'admin_notices', 'wcs_woocommerce_missing_notice' );
		return;
	}

	require_once WCS_PLUGIN_PATH . 'includes/class-wcs-plugin.php';

	WCS_Plugin::instance()->run();
}

add_action( 'plugins_loaded', 'wcs_run_plugin' );