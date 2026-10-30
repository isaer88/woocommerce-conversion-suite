<?php
/**
 * Plugin Name: Veltrixa Toolkit for WooCommerce
 * Plugin URI: https://github.com/isaer88/woocommerce-conversion-suite
 * Description: A modular WooCommerce toolkit with checkout shortcuts, product enhancements, and conversion-focused features.
 * Version: 0.1.2
 * Author: Oleksandr Yeremenko
 * Text Domain: veltrixa-toolkit-for-woocommerce
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 8.0
 * WC tested up to: 11.2
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

/**
 * Declare compatibility with WooCommerce features.
 */
add_action(
    'before_woocommerce_init',
    static function () {

        if (
            ! class_exists(
                \Automattic\WooCommerce\Utilities\FeaturesUtil::class
            )
        ) {
            return;
        }

        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            __FILE__,
            true
        );

        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'cart_checkout_blocks',
            __FILE__,
            false
        );
    }
);

define( 'WCS_VERSION', '0.1.2' );
define( 'WCS_PLUGIN_FILE', __FILE__ );
define( 'WCS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WCS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WCS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WCS_OCO_DB_VERSION', '1.0.0' );

/**
 * Load One Click Order idempotency storage.
 */
require_once WCS_PLUGIN_PATH . 'includes/class-wcs-oco-idempotency.php';

/**
 * Install or upgrade One Click Order storage.
 */
function wcs_install_oco_idempotency() {
    require_once WCS_PLUGIN_PATH . 'includes/class-wcs-oco-idempotency.php';

    WCS_OCO_Idempotency::install();

    global $wpdb;

    $table = WCS_OCO_Idempotency::table_name();

    // Confirm that dbDelta actually created the table.
    $found = $wpdb->get_var(
        $wpdb->prepare(
            'SHOW TABLES LIKE %s',
            $wpdb->esc_like( $table )
        )
    );

    if ( $found !== $table ) {
        if ( function_exists( 'wc_get_logger' ) ) {
            wc_get_logger()->error(
                'Failed to create idempotency table.',
                array( 'source' => 'wcs-one-click-order' )
            );
        }

        return false;
    }

    update_option(
        'wcs_oco_table_version',
        WCS_OCO_DB_VERSION,
        false
    );

    return true;
}

register_activation_hook(
    __FILE__,
    'wcs_install_oco_idempotency'
);

/**
 * Remove scheduled OCO cleanup on plugin deactivation.
 */
function wcs_deactivate_oco_cleanup() {
    wp_clear_scheduled_hook( 'wcs_oco_cleanup_completed' );
}

register_deactivation_hook(
    __FILE__,
    'wcs_deactivate_oco_cleanup'
);

/**
 * Upgrade storage on sites where the plugin is already active.
 */
function wcs_maybe_upgrade_oco_storage() {
    if (
        get_option( 'wcs_oco_table_version' )
        === WCS_OCO_DB_VERSION
    ) {
        return;
    }

    wcs_install_oco_idempotency();
}

add_action(
    'admin_init',
    'wcs_maybe_upgrade_oco_storage'
);

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
			<strong>Conversion Suite for WooCommerce</strong>
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