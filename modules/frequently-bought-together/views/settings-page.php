<?php
/**
 * Frequently Bought Together settings view.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

$veltrixa_fbt_settings = VELTRIXA_FBT_Settings::get_settings();
$veltrixa_fbt_option   = VELTRIXA_FBT_Settings::OPTION_NAME;
?>

<div class="wrap wcs-module-settings">
    <p class="wcs-back"><a href="<?php echo esc_url( admin_url( 'admin.php?page=' . VELTRIXA_Admin::MENU_SLUG ) ); ?>">&larr; <?php esc_html_e( 'Back to Dashboard', 'veltrixa-toolkit-for-woocommerce' ); ?></a></p>
    <h1>Frequently Bought Together Settings</h1>

    <p>Configure complementary product recommendations.</p>

    <form method="post" action="options.php">
        <?php settings_fields( 'veltrixa_fbt_group' ); ?>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="veltrixa-fbt-title">Section title</label>
                </th>
                <td>
                    <input
                        id="veltrixa-fbt-title"
                        type="text"
                        class="regular-text"
                        name="<?php echo esc_attr( $veltrixa_fbt_option ); ?>[title]"
                        value="<?php echo esc_attr( $veltrixa_fbt_settings['title'] ); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="veltrixa-fbt-max">Maximum related products</label>
                </th>
                <td>
                    <select
                        id="veltrixa-fbt-max"
                        name="<?php echo esc_attr( $veltrixa_fbt_option ); ?>[max_products]"
                    >
                        <?php for ( $veltrixa_fbt_i = 1; $veltrixa_fbt_i <= 3; $veltrixa_fbt_i++ ) : ?>
                            <option
                                value="<?php echo esc_attr( $veltrixa_fbt_i ); ?>"
                                <?php selected( $veltrixa_fbt_settings['max_products'], $veltrixa_fbt_i ); ?>
                            >
                                <?php echo esc_html( $veltrixa_fbt_i ); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">Automatic display</th>
                <td>
                    <label>
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr( $veltrixa_fbt_option ); ?>[auto_display]"
                            value="yes"
                            <?php checked( $veltrixa_fbt_settings['auto_display'], 'yes' ); ?>
                        >
                        Display on single product pages
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="veltrixa-fbt-button">Button text</label>
                </th>
                <td>
                    <input
                        id="veltrixa-fbt-button"
                        type="text"
                        class="regular-text"
                        name="<?php echo esc_attr( $veltrixa_fbt_option ); ?>[button_text]"
                        value="<?php echo esc_attr( $veltrixa_fbt_settings['button_text'] ); ?>"
                    >
                </td>
            </tr>
        </table>

        <?php submit_button( 'Save Settings' ); ?>
    </form>

    <hr>

    <h2>How to configure products</h2>

    <p>
        Edit a simple WooCommerce product and open
        Product data &rarr; General.
        Select complementary products in the
        Frequently Bought Together field.
    </p>

    <h2>Shortcode</h2>

    <code>[veltrixa_fbt]</code>

    <p class="description">
        The shortcode is intended for single product pages.
        Disable Automatic display to avoid duplicate sections.
    </p>
</div>
