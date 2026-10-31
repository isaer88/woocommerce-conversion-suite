
<?php
/**
 * Veltrixa Toolkit dashboard.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

$manager = VELTRIXA_Plugin::instance()->get_module_manager();

$registered = $manager
    ? $manager->get_modules()
    : array();

$planned = array(
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

$planned = array_diff_key( $planned, $registered );

$active_count = 0;

foreach ( $registered as $module ) {
    if ( $module->is_enabled() ) {
        ++$active_count;
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
            <strong><?php echo esc_html( count( $registered ) ); ?></strong>
            <span>Installed modules</span>
        </div>

        <div class="wcs-summary-card">
            <strong id="wcs-active-count"><?php echo esc_html( $active_count ); ?></strong>
            <span>Active modules</span>
        </div>

        <div class="wcs-summary-card">
            <strong><?php echo esc_html( count( $planned ) ); ?></strong>
            <span>Coming soon</span>
        </div>
    </div>

    <div id="wcs-dashboard-notice" class="wcs-dashboard-notice" role="status" aria-live="polite" hidden></div>

    <h2>Available Modules</h2>

    <div class="wcs-modules-grid">
        <?php foreach ( $registered as $module ) : ?>
            <?php
            $id      = $module->get_id();
            $enabled = $module->is_enabled();
            $url     = $module->get_settings_url();
            ?>

            <div class="wcs-module-card" data-module-id="<?php echo esc_attr( $id ); ?>">
                <div class="wcs-module-card__header">
                    <h2><?php echo esc_html( $module->get_name() ); ?></h2>

                    <?php if ( ! $module->is_pro() ) : ?>
                        <label class="wcs-switch">
                            <input
                                type="checkbox"
                                class="wcs-module-toggle"
                                data-module-id="<?php echo esc_attr( $id ); ?>"
                                aria-label="<?php echo esc_attr( 'Enable ' . $module->get_name() ); ?>"
                                <?php checked( $enabled ); ?>
                            >
                            <span class="wcs-switch__slider"></span>
                        </label>
                    <?php else : ?>
                        <span class="wcs-module-badge">PRO</span>
                    <?php endif; ?>
                </div>

                <p><?php echo esc_html( $module->get_description() ); ?></p>

                <div class="wcs-module-card__footer">
                    <span class="wcs-module-status <?php echo $enabled ? 'is-active' : 'is-inactive'; ?>">
                        <?php echo $enabled ? 'Active' : 'Inactive'; ?>
                    </span>

                    <?php if ( $url ) : ?>
                        <a class="button button-secondary" href="<?php echo esc_url( $url ); ?>">
                            Settings
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ( empty( $registered ) ) : ?>
        <p>No installed modules found.</p>
    <?php endif; ?>

    <h2 class="wcs-section-heading">Coming Soon</h2>

    <div class="wcs-modules-grid">
        <?php foreach ( $planned as $id => $name ) : ?>
            <div class="wcs-module-card wcs-module-card--planned">
                <div class="wcs-module-card__header">
                    <h2><?php echo esc_html( $name ); ?></h2>
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
