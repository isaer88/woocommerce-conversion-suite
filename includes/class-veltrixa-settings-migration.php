<?php
/**
 * Legacy settings migration.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

/**
 * Copy legacy settings without overwriting existing Veltrixa settings.
 *
 * @return void
 */
function veltrixa_migrate_legacy_settings() {
    $option_names = array(
        'enabled_modules',
        'buy_now_settings',
        'exit_intent_settings',
        'free_shipping_progress_settings',
        'fbt_settings',
        'low_stock_settings',
        'one_click_order_settings',
        'quick_checkout_settings',
        'recently_viewed_settings',
        'sale_countdown_settings',
        'sticky_add_to_cart_settings',
        'trust_badges_settings',
        'whatsapp_order_settings',
    );

    foreach ( $option_names as $name ) {
        $old_key = 'wcs_' . $name;
        $new_key = 'veltrixa_' . $name;

        $existing = get_option( $new_key, null );

        if ( null !== $existing ) {
            continue;
        }

        $legacy = get_option( $old_key, null );

        if ( null === $legacy ) {
            continue;
        }

        add_option( $new_key, $legacy, '', false );
    }
}
