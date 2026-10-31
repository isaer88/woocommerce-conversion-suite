<?php
/**
 * Exit Intent popup view.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

$title       = isset( $settings['title'] ) ? $settings['title'] : '';
$message     = isset( $settings['message'] ) ? $settings['message'] : '';
$button_text = isset( $settings['button_text'] ) ? $settings['button_text'] : '';
$button_url  = isset( $settings['button_url'] ) ? $settings['button_url'] : '';

$has_cta = (
    '' !== trim( $button_text )
    && '' !== trim( $button_url )
);
?>

<div
    id="wcs-exit-intent"
    class="wcs-exit-intent"
    hidden
>
    <div
        class="wcs-exit-intent__overlay"
        data-wcs-ei-close
        aria-hidden="true"
    ></div>

    <div
        class="wcs-exit-intent__dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="wcs-exit-intent-title"
        aria-describedby="wcs-exit-intent-description"
        tabindex="-1"
    >
        <button
            type="button"
            class="wcs-exit-intent__close"
            data-wcs-ei-close
            aria-label="<?php echo esc_attr__( 'Close popup', 'veltrixa-toolkit-for-woocommerce' ); ?>"
        >
            <span aria-hidden="true">&times;</span>
        </button>

        <div class="wcs-exit-intent__content">
            <div class="wcs-exit-intent__icon" aria-hidden="true">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="48"
                    height="48"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <rect x="3" y="8" width="18" height="13" rx="2"/>
                    <path d="M12 8v13M3 12h18"/>
                    <path d="M12 8H8a2.5 2.5 0 1 1 2.5-2.5L12 8Z"/>
                    <path d="M12 8h4a2.5 2.5 0 1 0-2.5-2.5L12 8Z"/>
                </svg>
            </div>

            <h2 id="wcs-exit-intent-title">
                <?php echo esc_html( $title ); ?>
            </h2>

            <p id="wcs-exit-intent-description">
                <?php echo nl2br( esc_html( $message ) ); ?>
            </p>

            <?php if ( $has_cta ) : ?>
                <a
                    class="wcs-exit-intent__button"
                    href="<?php echo esc_url( $button_url ); ?>"
                >
                    <?php echo esc_html( $button_text ); ?>
                </a>
            <?php endif; ?>

            <button
                type="button"
                class="wcs-exit-intent__dismiss"
                data-wcs-ei-close
            >
                Continue shopping
            </button>
        </div>
    </div>
</div>
