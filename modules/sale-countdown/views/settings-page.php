<?php
/**
 * Sale Countdown settings view.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

$veltrixa_sc_settings = VELTRIXA_Sale_Countdown_Settings::get_settings();
$veltrixa_sc_option   = VELTRIXA_Sale_Countdown_Settings::OPTION_NAME;
?>

<div class="wrap wcs-module-settings">
    <p class="wcs-back"><a href="<?php echo esc_url( admin_url( 'admin.php?page=' . VELTRIXA_Admin::MENU_SLUG ) ); ?>">&larr; <?php esc_html_e( 'Back to Dashboard', 'veltrixa-toolkit-for-woocommerce' ); ?></a></p>
    <h1>Sale Countdown Settings</h1>

    <p>
        Display a countdown timer for simple products
        with an active scheduled sale.
    </p>

    <form method="post" action="options.php">
        <?php settings_fields( 'veltrixa_sale_countdown_group' ); ?>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="wcs-sc-title">Countdown title</label>
                </th>
                <td>
                    <input
                        id="wcs-sc-title"
                        type="text"
                        class="regular-text"
                        name="<?php echo esc_attr( $veltrixa_sc_option ); ?>[title]"
                        value="<?php echo esc_attr( $veltrixa_sc_settings['title'] ); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th scope="row">Automatic display</th>
                <td>
                    <label>
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr( $veltrixa_sc_option ); ?>[auto_display]"
                            value="yes"
                            <?php checked( $veltrixa_sc_settings['auto_display'], 'yes' ); ?>
                        >
                        Show countdown on single product pages
                    </label>
                </td>
            </tr>

            <?php
            $veltrixa_sc_colors = array(
                'background'   => 'Background color',
                'text_color'   => 'Text color',
                'accent_color' => 'Countdown numbers color',
            );
            ?>

            <?php foreach ( $veltrixa_sc_colors as $veltrixa_sc_key => $veltrixa_sc_label ) : ?>
                <tr>
                    <th scope="row">
                        <label for="wcs-sc-<?php echo esc_attr( $veltrixa_sc_key ); ?>">
                            <?php echo esc_html( $veltrixa_sc_label ); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            id="wcs-sc-<?php echo esc_attr( $veltrixa_sc_key ); ?>"
                            type="color"
                            name="<?php echo esc_attr( $veltrixa_sc_option ); ?>[<?php echo esc_attr( $veltrixa_sc_key ); ?>]"
                            value="<?php echo esc_attr( $veltrixa_sc_settings[ $veltrixa_sc_key ] ); ?>"
                        >
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <?php submit_button( 'Save Settings' ); ?>
    </form>

    <hr>

    <h2>How to enable the countdown</h2>

    <p>
        Go to Products, edit a simple product,
        set its Sale price, click Schedule,
        and choose an end date.
    </p>

    <h2>Shortcode</h2>

    <code>[veltrixa_sale_countdown]</code>

    <p class="description">
        Use this shortcode on a single product page.
        Disable Automatic display to prevent duplicates.
    </p>
</div>
