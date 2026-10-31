<?php
/**
 * Trust Badges frontend.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

final class VELTRIXA_Trust_Badges {

    private $settings;

    public function __construct() {
        $this->settings = VELTRIXA_Trust_Badges_Settings::get_settings();
    }

    public function register_hooks() {
        add_action(
            'woocommerce_after_add_to_cart_form',
            array( $this, 'auto_display' ),
            20
        );

        add_shortcode(
            'wcs_trust_badges',
            array( $this, 'shortcode' )
        );

        add_action(
            'wp_enqueue_scripts',
            array( $this, 'enqueue_assets' )
        );
    }

    public function enqueue_assets() {
        if ( ! is_product() ) {
            return;
        }

        $file = __DIR__ . '/assets/css/trust-badges.css';

        wp_enqueue_style(
            'wcs-trust-badges',
            VELTRIXA_PLUGIN_URL . 'modules/trust-badges/assets/css/trust-badges.css',
            array(),
            file_exists( $file ) ? filemtime( $file ) : '1.0.0'
        );

        $background = sanitize_hex_color(
            $this->settings['background']
        ) ?: '#f8fafc';

        $text = sanitize_hex_color(
            $this->settings['text_color']
        ) ?: '#334155';

        $icon = sanitize_hex_color(
            $this->settings['icon_color']
        ) ?: '#15803d';

        wp_add_inline_style(
            'wcs-trust-badges',
            sprintf(
                '.wcs-trust-badges {
                    --wcs-tb-background: %s;
                    --wcs-tb-text: %s;
                    --wcs-tb-icon: %s;
                }',
                $background,
                $text,
                $icon
            )
        );
    }

    public function auto_display() {
        if ( 'yes' !== $this->settings['auto_display'] ) {
            return;
        }

        echo $this->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    public function shortcode() {
        return $this->render();
    }

    private function render() {
        if ( ! is_product() ) {
            return '';
        }

        $product = wc_get_product( get_queried_object_id() );

        if (
            ! $product
            || 'publish' !== $product->get_status()
            || ! $product->is_visible()
        ) {
            return '';
        }

        $badges = isset( $this->settings['badges'] )
            && is_array( $this->settings['badges'] )
                ? $this->settings['badges']
                : array();

        $active = array();

        foreach ( array_slice( $badges, 0, 6 ) as $badge ) {
            if (
                ! is_array( $badge )
                || ! isset( $badge['enabled'], $badge['text'], $badge['icon'] )
                || 'yes' !== $badge['enabled']
                || '' === trim( (string) $badge['text'] )
            ) {
                continue;
            }

            $active[] = $badge;
        }

        if ( empty( $active ) ) {
            return '';
        }

        ob_start();
        ?>
        <div
            class="wcs-trust-badges"
            aria-label="<?php echo esc_attr__( 'Store information', 'veltrixa-toolkit-for-woocommerce' ); ?>"
        >
            <?php foreach ( $active as $badge ) : ?>
                <div class="wcs-trust-badges__item">
                    <span class="wcs-trust-badges__icon" aria-hidden="true">
                        <?php
                        // SVG markup comes exclusively from fixed internal templates.
                        echo $this->get_icon( $badge['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        ?>
                    </span>

                    <span class="wcs-trust-badges__text">
                        <?php echo esc_html( $badge['text'] ); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php

        return ob_get_clean();
    }

    private function get_icon( $name ) {
        $paths = array(
            'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',

            'truck' => '<path d="M10 17h4V5H2v12h3"/><path d="M14 9h4l4 4v4h-3"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="16.5" cy="17.5" r="2.5"/>',

            'returns' => '<path d="M3 11a9 9 0 1 1 2.7 6.4"/><path d="M3 4v7h7"/>',

            'support' => '<path d="M3 14v-3a9 9 0 0 1 18 0v3"/><rect x="2" y="13" width="4" height="7" rx="2"/><rect x="18" y="13" width="4" height="7" rx="2"/><path d="M20 20a7 7 0 0 1-7 2h-2"/>',

            'lock' => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/><path d="M12 15v2"/>',

            'check' => '<circle cx="12" cy="12" r="10"/><path d="m8 12 3 3 5-6"/>',
        );

        $path = isset( $paths[ $name ] )
            ? $paths[ $name ]
            : $paths['check'];

        return '<svg xmlns="http://www.w3.org/2000/svg"
            width="28"
            height="28"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
            focusable="false"
            aria-hidden="true">'
            . $path .
            '</svg>';
    }
}
