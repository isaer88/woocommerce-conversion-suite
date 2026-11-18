<?php
/**
 * FBT product bundle template.
 *
 * Available variables:
 * $product, $related, $settings.
 */

defined( 'ABSPATH' ) || exit;

$veltrixa_fbt_products = array_merge( array( $product ), $related );
?>

<section class="veltrixa-fbt" data-veltrixa-fbt>
    <h2 class="veltrixa-fbt__title">
        <?php echo esc_html( $this->settings['title'] ); ?>
    </h2>

    <div
        class="veltrixa-fbt__products"
        data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
    >
        <?php foreach ( $veltrixa_fbt_products as $veltrixa_fbt_index => $veltrixa_fbt_item ) : ?>
            <label class="veltrixa-fbt__item">
                <input
                    type="checkbox"
                    class="veltrixa-fbt__checkbox"
                    value="<?php echo esc_attr( $veltrixa_fbt_item->get_id() ); ?>"
                    data-price="<?php echo esc_attr( wc_get_price_to_display( $veltrixa_fbt_item ) ); ?>"
                    <?php checked( true ); ?>
                    <?php disabled( 0 === $veltrixa_fbt_index ); ?>
                >

                <span class="veltrixa-fbt__image">
                    <?php echo wp_kses_post( $veltrixa_fbt_item->get_image( 'woocommerce_thumbnail' ) ); ?>
                </span>

                <span class="veltrixa-fbt__details">
                    <span class="veltrixa-fbt__name">
                        <?php echo esc_html( $veltrixa_fbt_item->get_name() ); ?>
                    </span>

                    <span class="veltrixa-fbt__price">
                        <?php echo wp_kses_post( $veltrixa_fbt_item->get_price_html() ); ?>
                    </span>

                    <?php if ( 0 === $veltrixa_fbt_index ) : ?>
                        <small>Main product</small>
                    <?php endif; ?>
                </span>
            </label>
        <?php endforeach; ?>
    </div>

    <div class="veltrixa-fbt__footer">
        <div class="veltrixa-fbt__total">
            <span>Total:</span>
            <strong data-veltrixa-fbt-total></strong>
        </div>

        <button
            type="button"
            class="button alt veltrixa-fbt__button"
            data-veltrixa-fbt-add
        >
            <?php echo esc_html( $this->settings['button_text'] ); ?>
        </button>
    </div>

    <p class="veltrixa-fbt__message" role="status" aria-live="polite"></p>
</section>
