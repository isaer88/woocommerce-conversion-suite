<?php
/**
 * One Click Order modal.
 */

defined( 'ABSPATH' ) || exit;

$max_quantity = $product->is_sold_individually()
    ? 1
    : (
        $product->managing_stock()
            ? min( 20, max( 1, (int) $product->get_stock_quantity() ) )
            : 20
    );
?>

<div id="wcs-oco-modal" class="wcs-oco" hidden>
    <div class="wcs-oco__overlay" data-wcs-oco-close></div>

    <div
        class="wcs-oco__dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="wcs-oco-title"
        tabindex="-1"
    >
        <button
            type="button"
            class="wcs-oco__close"
            data-wcs-oco-close
            aria-label="Close Quick Order"
        >&times;</button>

        <h2 id="wcs-oco-title">
            <?php echo esc_html( $settings['modal_title'] ); ?>
        </h2>

        <p class="wcs-oco__intro">
            <?php echo esc_html( $settings['intro_text'] ); ?>
        </p>

        <div class="wcs-oco__product">
            <strong><?php echo esc_html( $product->get_name() ); ?></strong>
            <span>
                <?php
                echo wp_kses_post( $product->get_price_html() );
                ?>
                / item
            </span>
        </div>

        <form id="wcs-oco-form">
            <input
                type="hidden"
                name="product_id"
                value="<?php echo esc_attr( $product->get_id() ); ?>"
            >

            <div class="wcs-oco__field">
                <label for="wcs-oco-name">Your name *</label>
                <input
                    id="wcs-oco-name"
                    type="text"
                    name="customer_name"
                    autocomplete="name"
                    maxlength="200"
                    required
                >
            </div>

            <div class="wcs-oco__field">
                <label for="wcs-oco-phone">Phone number *</label>
                <input
                    id="wcs-oco-phone"
                    type="tel"
                    name="customer_phone"
                    autocomplete="tel"
                    maxlength="25"
                    required
                >
            </div>

            <div class="wcs-oco__field">
                <label for="wcs-oco-quantity">Quantity *</label>
                <input
                    id="wcs-oco-quantity"
                    type="number"
                    name="quantity"
                    min="1"
                    max="<?php echo esc_attr( $max_quantity ); ?>"
                    value="1"
                    required
                >
            </div>

            <p class="wcs-oco__notice">
                This is an order request, not an online payment.
                Shipping costs and the final payable amount
                will be confirmed by the store.
            </p>

            <button
                type="submit"
                class="wcs-oco__submit"
            >
                Submit Order Request
            </button>

            <p
                id="wcs-oco-feedback"
                class="wcs-oco__feedback"
                role="status"
                aria-live="polite"
            ></p>
        </form>
    </div>
</div>
