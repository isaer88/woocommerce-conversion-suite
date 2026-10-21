<?php
/**
 * FBT product bundle template.
 *
 * Available variables:
 * $product, $related, $settings.
 */

defined( 'ABSPATH' ) || exit;

$products = array_merge( array( $product ), $related );
?>

<section class="wcs-fbt" data-wcs-fbt>
    <h2 class="wcs-fbt__title">
        <?php echo esc_html( $this->settings['title'] ); ?>
    </h2>

    <div
        class="wcs-fbt__products"
        data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
    >
        <?php foreach ( $products as $index => $item ) : ?>
            <label class="wcs-fbt__item">
                <input
                    type="checkbox"
                    class="wcs-fbt__checkbox"
                    value="<?php echo esc_attr( $item->get_id() ); ?>"
                    data-price="<?php echo esc_attr( wc_get_price_to_display( $item ) ); ?>"
                    <?php checked( true ); ?>
                    <?php disabled( 0 === $index ); ?>
                >

                <span class="wcs-fbt__image">
                    <?php echo wp_kses_post( $item->get_image( 'woocommerce_thumbnail' ) ); ?>
                </span>

                <span class="wcs-fbt__details">
                    <span class="wcs-fbt__name">
                        <?php echo esc_html( $item->get_name() ); ?>
                    </span>

                    <span class="wcs-fbt__price">
                        <?php echo wp_kses_post( $item->get_price_html() ); ?>
                    </span>

                    <?php if ( 0 === $index ) : ?>
                        <small>Main product</small>
                    <?php endif; ?>
                </span>
            </label>
        <?php endforeach; ?>
    </div>

    <div class="wcs-fbt__footer">
        <div class="wcs-fbt__total">
            <span>Total:</span>
            <strong data-wcs-fbt-total></strong>
        </div>

        <button
            type="button"
            class="button alt wcs-fbt__button"
            data-wcs-fbt-add
        >
            <?php echo esc_html( $this->settings['button_text'] ); ?>
        </button>
    </div>

    <p class="wcs-fbt__message" role="status" aria-live="polite"></p>
</section>
