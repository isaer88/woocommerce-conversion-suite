
<?php
/**
 * Veltrixa Toolkit dashboard.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

$veltrixa_dashboard_manager = VELTRIXA_Plugin::instance()->get_module_manager();

$veltrixa_dashboard_registered = $veltrixa_dashboard_manager
    ? $veltrixa_dashboard_manager->get_modules()
    : array();

$veltrixa_dashboard_planned = array(
    'sticky-add-to-cart'         => 'Sticky Add to Cart',
    'quick-checkout'             => 'Quick Checkout',
    'recently-viewed'            => 'Recently Viewed',
    'frequently-bought-together' => 'Frequently Bought Together',
    'free-shipping-progress'     => 'Free Shipping Progress',
    'sale-countdown'             => 'Sale Countdown',
    'exit-intent'                => 'Exit Intent',
    'trust-badges'               => 'Trust Badges',
    'low-stock'                  => 'Low Stock',
    'one-click-order'            => 'One Click Order',
    'whatsapp-order'             => 'WhatsApp Order',
);

$veltrixa_dashboard_planned = array_diff_key( $veltrixa_dashboard_planned, $veltrixa_dashboard_registered );

$veltrixa_dashboard_active_count = 0;

foreach ( $veltrixa_dashboard_registered as $veltrixa_dashboard_module ) {
    if ( $veltrixa_dashboard_module->is_enabled() ) {
        ++$veltrixa_dashboard_active_count;
    }
}
?>

<div class="wrap wcs-admin-page">
    <h1>Veltrixa Toolkit for WooCommerce</h1>

    <p>
        Manage conversion tools for your WooCommerce store.
    </p>

    <div class="wcs-dashboard-summary">
        <div class="wcs-summary-card">
            <strong><?php echo esc_html( count( $veltrixa_dashboard_registered ) ); ?></strong>
            <span>Installed modules</span>
        </div>

        <div class="wcs-summary-card">
            <strong id="wcs-active-count"><?php echo esc_html( $veltrixa_dashboard_active_count ); ?></strong>
            <span>Active modules</span>
        </div>

        <div class="wcs-summary-card">
            <strong><?php echo esc_html( count( $veltrixa_dashboard_planned ) ); ?></strong>
            <span>Coming soon</span>
        </div>
    </div>

    <div id="wcs-dashboard-notice" class="wcs-dashboard-notice" role="status" aria-live="polite" hidden></div>

    <h2>Available Modules</h2>

    <div class="wcs-modules-grid">
        <?php foreach ( $veltrixa_dashboard_registered as $veltrixa_dashboard_module ) : ?>
            <?php
            $veltrixa_dashboard_id      = $veltrixa_dashboard_module->get_id();
            $veltrixa_dashboard_enabled = $veltrixa_dashboard_module->is_enabled();
            $veltrixa_dashboard_url     = $veltrixa_dashboard_module->get_settings_url();
            ?>

            <div class="wcs-module-card" data-module-id="<?php echo esc_attr( $veltrixa_dashboard_id ); ?>">
                <div class="wcs-module-card__header">
                    <h2><?php echo esc_html( $veltrixa_dashboard_module->get_name() ); ?></h2>

                    <?php if ( ! $veltrixa_dashboard_module->is_pro() ) : ?>
                        <label class="wcs-switch">
                            <input
                                type="checkbox"
                                class="wcs-module-toggle"
                                data-module-id="<?php echo esc_attr( $veltrixa_dashboard_id ); ?>"
                                aria-label="<?php echo esc_attr( 'Enable ' . $veltrixa_dashboard_module->get_name() ); ?>"
                                <?php checked( $veltrixa_dashboard_enabled ); ?>
                            >
                            <span class="wcs-switch__slider"></span>
                        </label>
                    <?php else : ?>
                        <span class="wcs-module-badge">PRO</span>
                    <?php endif; ?>
                </div>

                <p><?php echo esc_html( $veltrixa_dashboard_module->get_description() ); ?></p>

                <div class="wcs-module-card__footer">
                    <span class="wcs-module-status <?php echo $veltrixa_dashboard_enabled ? 'is-active' : 'is-inactive'; ?>">
                        <?php echo $veltrixa_dashboard_enabled ? 'Active' : 'Inactive'; ?>
                    </span>

                    <?php if ( $veltrixa_dashboard_url ) : ?>
                        <a class="button button-secondary" href="<?php echo esc_url( $veltrixa_dashboard_url ); ?>">
                            Settings
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ( empty( $veltrixa_dashboard_registered ) ) : ?>
        <p>No installed modules found.</p>
    <?php endif; ?>

    <h2 class="wcs-section-heading">Coming Soon</h2>

    <div class="wcs-modules-grid">
        <?php foreach ( $veltrixa_dashboard_planned as $veltrixa_dashboard_id => $veltrixa_dashboard_name ) : ?>
            <div class="wcs-module-card wcs-module-card--planned">
                <div class="wcs-module-card__header">
                    <h2><?php echo esc_html( $veltrixa_dashboard_name ); ?></h2>
                    <span class="wcs-module-badge">Soon</span>
                </div>

                <p>This module is under development.</p>

                <div class="wcs-module-card__footer">
                    <span class="wcs-module-status is-planned">
                        Not installed
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
