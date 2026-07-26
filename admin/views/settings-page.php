<?php
/**
 * Сторінка налаштувань.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

$modules = array(
	'buy-now'                     => 'Buy Now',
	'sticky-add-to-cart'          => 'Sticky Add to Cart',
	'quick-checkout'              => 'Quick Checkout',
	'recently-viewed'             => 'Recently Viewed',
	'frequently-bought-together'  => 'Frequently Bought Together',
	'free-shipping-progress'      => 'Free Shipping Progress',
	'sale-countdown'              => 'Sale Countdown',
	'exit-intent'                 => 'Exit Intent',
	'trust-badges'                => 'Trust Badges',
	'low-stock'                   => 'Low Stock',
	'one-click-order'             => 'One Click Order',
	'whatsapp-order'              => 'WhatsApp Order',
);
?>

<div class="wrap wcs-admin-page">
	<h1>WooCommerce Conversion Suite</h1>

	<p>
		Інструменти для збільшення конверсії та продажів WooCommerce.
	</p>

	<div class="wcs-modules-grid">
		<?php foreach ( $modules as $module_key => $module_name ) : ?>
			<div class="wcs-module-card">
				<div class="wcs-module-card__header">
					<h2><?php echo esc_html( $module_name ); ?></h2>

					<label class="wcs-switch">
						<input
							type="checkbox"
							name="wcs_modules[]"
							value="<?php echo esc_attr( $module_key ); ?>"
							disabled
						>

						<span class="wcs-switch__slider"></span>
					</label>
				</div>

				<p>Модуль перебуває в розробці.</p>
			</div>
		<?php endforeach; ?>
	</div>
</div>