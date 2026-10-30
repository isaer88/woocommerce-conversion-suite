<?php
/**
 * Frequently Bought Together settings view.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

$settings = VELTRIXA_FBT_Settings::get_settings();
$option   = VELTRIXA_FBT_Settings::OPTION_NAME;
?>

<div class="wrap">
    <h1>Frequently Bought Together Settings</h1>

    <p>Configure complementary product recommendations.</p>

    <form method="post" action="options.php">
        <?php settings_fields( 'wcs_fbt_group' ); ?>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="wcs-fbt-title">Section title</label>
                </th>
                <td>
                    <input
                        id="wcs-fbt-title"
                        type="text"
                        class="regular-text"
                        name="<?php echo esc_attr( $option ); ?>[title]"
                        value="<?php echo esc_attr( $settings['title'] ); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="wcs-fbt-max">Maximum related products</label>
                </th>
                <td>
                    <select
                        id="wcs-fbt-max"
                        name="<?php echo esc_attr( $option ); ?>[max_products]"
                    >
                        <?php for ( $i = 1; $i <= 3; $i++ ) : ?>
                            <option
                                value="<?php echo esc_attr( $i ); ?>"
                                <?php selected( $settings['max_products'], $i ); ?>
                            >
                                <?php echo esc_html( $i ); ?>
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
                            name="<?php echo esc_attr( $option ); ?>[auto_display]"
                            value="yes"
                            <?php checked( $settings['auto_display'], 'yes' ); ?>
                        >
                        Display on single product pages
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="wcs-fbt-button">Button text</label>
                </th>
                <td>
                    <input
                        id="wcs-fbt-button"
                        type="text"
                        class="regular-text"
                        name="<?php echo esc_attr( $option ); ?>[button_text]"
                        value="<?php echo esc_attr( $settings['button_text'] ); ?>"
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

    <code>[wcs_fbt]</code>

    <p class="description">
        The shortcode is intended for single product pages.
        Disable Automatic display to avoid duplicate sections.
    </p>
</div>
