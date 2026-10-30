<?php
/**
 * Recently Viewed settings view.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

$settings = VELTRIXA_Recently_Viewed_Settings::get_settings();
$option   = VELTRIXA_Recently_Viewed_Settings::OPTION_NAME;
?>

<div class="wrap">
    <h1>Recently Viewed Settings</h1>

    <p>Configure how recently viewed products appear in your store.</p>

    <form method="post" action="options.php">
        <?php settings_fields( 'wcs_recently_viewed_group' ); ?>

        <table class="form-table" role="presentation">

            <tr>
                <th scope="row">
                    <label for="wcs-rv-title">Section title</label>
                </th>
                <td>
                    <input
                        id="wcs-rv-title"
                        type="text"
                        class="regular-text"
                        name="<?php echo esc_attr( $option ); ?>[title]"
                        value="<?php echo esc_attr( $settings['title'] ); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="wcs-rv-limit">Number of products</label>
                </th>
                <td>
                    <input
                        id="wcs-rv-limit"
                        type="number"
                        min="4"
                        max="12"
                        name="<?php echo esc_attr( $option ); ?>[limit]"
                        value="<?php echo esc_attr( $settings['limit'] ); ?>"
                    >
                    <p class="description">Between 4 and 12 products.</p>
                </td>
            </tr>

            <tr>
                <th scope="row">Show current product</th>
                <td>
                    <label>
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr( $option ); ?>[show_current]"
                            value="yes"
                            <?php checked( $settings['show_current'], 'yes' ); ?>
                        >
                        Include the current product in the list.
                    </label>
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
                        Display below the product summary.
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row">Require tracking consent</th>
                <td>
                    <label>
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr( $option ); ?>[require_consent]"
                            value="yes"
                            <?php checked( $settings['require_consent'], 'yes' ); ?>
                        >
                        Only run after explicit consent.
                    </label>
                    <p class="description">
                        Requires integration with your cookie consent manager.
                        Set window.wcsRecentlyViewedConsent to true
                        before the module script runs.
                    </p>
                </td>
            </tr>

        </table>

        <?php submit_button( 'Save Settings' ); ?>
    </form>

    <hr>

    <h2>Shortcode</h2>
    <p>Use this shortcode to display the section manually:</p>

    <code>[wcs_recently_viewed]</code>

    <p class="description">
        Disable Automatic display if you only want to use the shortcode.
    </p>
</div>
