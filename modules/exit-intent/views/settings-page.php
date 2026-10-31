<?php
/**
 * Exit Intent settings view.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

$settings = VELTRIXA_Exit_Intent_Settings::get_settings();
$option   = VELTRIXA_Exit_Intent_Settings::OPTION_NAME;
?>

<div class="wrap">
    <h1>Exit Intent Settings</h1>

    <p>
        Configure an exit-intent popup for WooCommerce
        product and shop pages.
    </p>

    <form method="post" action="options.php">
        <?php settings_fields( 'wcs_exit_intent_group' ); ?>

        <h2>General Settings</h2>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">Enable popup</th>
                <td>
                    <label>
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr( $option ); ?>[enabled]"
                            value="yes"
                            <?php checked( $settings['enabled'], 'yes' ); ?>
                        >
                        Enable Exit Intent popup
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="wcs-ei-title">Popup title</label>
                </th>
                <td>
                    <input
                        id="wcs-ei-title"
                        type="text"
                        class="regular-text"
                        maxlength="150"
                        name="<?php echo esc_attr( $option ); ?>[title]"
                        value="<?php echo esc_attr( $settings['title'] ); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="wcs-ei-message">Popup message</label>
                </th>
                <td>
                    <textarea
                        id="wcs-ei-message"
                        name="<?php echo esc_attr( $option ); ?>[message]"
                        rows="4"
                        class="large-text"
                    ><?php echo esc_textarea( $settings['message'] ); ?></textarea>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="wcs-ei-button-text">
                        Button text
                    </label>
                </th>
                <td>
                    <input
                        id="wcs-ei-button-text"
                        type="text"
                        class="regular-text"
                        name="<?php echo esc_attr( $option ); ?>[button_text]"
                        value="<?php echo esc_attr( $settings['button_text'] ); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="wcs-ei-button-url">
                        Button URL
                    </label>
                </th>
                <td>
                    <input
                        id="wcs-ei-button-url"
                        type="url"
                        class="regular-text"
                        name="<?php echo esc_attr( $option ); ?>[button_url]"
                        value="<?php echo esc_attr( $settings['button_url'] ); ?>"
                        placeholder="https://example.com/offers/"
                    >
                    <p class="description">
                        Only HTTP/HTTPS URLs are allowed.
                        Leave empty to hide the CTA button.
                    </p>
                </td>
            </tr>
        </table>

        <hr>

        <h2>Display Rules</h2>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="wcs-ei-delay">
                        Minimum delay (seconds)
                    </label>
                </th>
                <td>
                    <input
                        id="wcs-ei-delay"
                        type="number"
                        min="0"
                        max="120"
                        name="<?php echo esc_attr( $option ); ?>[delay]"
                        value="<?php echo esc_attr( $settings['delay'] ); ?>"
                    >
                    <p class="description">
                        The popup cannot appear before this
                        time has elapsed.
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">Mobile popup</th>
                <td>
                    <label>
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr( $option ); ?>[mobile_enabled]"
                            value="yes"
                            <?php checked( $settings['mobile_enabled'], 'yes' ); ?>
                        >
                        Enable timed popup on mobile devices
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="wcs-ei-mobile-delay">
                        Mobile delay (seconds)
                    </label>
                </th>
                <td>
                    <input
                        id="wcs-ei-mobile-delay"
                        type="number"
                        min="5"
                        max="300"
                        name="<?php echo esc_attr( $option ); ?>[mobile_delay]"
                        value="<?php echo esc_attr( $settings['mobile_delay'] ); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="wcs-ei-frequency">
                        Frequency limit (hours)
                    </label>
                </th>
                <td>
                    <input
                        id="wcs-ei-frequency"
                        type="number"
                        min="1"
                        max="720"
                        name="<?php echo esc_attr( $option ); ?>[frequency_hours]"
                        value="<?php echo esc_attr( $settings['frequency_hours'] ); ?>"
                    >
                    <p class="description">
                        Minimum time between popup impressions
                        in the same browser.
                    </p>
                </td>
            </tr>
        </table>

        <hr>

        <h2>Appearance</h2>

        <table class="form-table" role="presentation">
            <?php
            $colors = array(
                'background'        => 'Popup background',
                'text_color'        => 'Text color',
                'button_color'      => 'Button background',
                'button_text_color' => 'Button text color',
            );
            ?>

            <?php foreach ( $colors as $key => $label ) : ?>
                <tr>
                    <th scope="row">
                        <label for="wcs-ei-<?php echo esc_attr( $key ); ?>">
                            <?php echo esc_html( $label ); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            id="wcs-ei-<?php echo esc_attr( $key ); ?>"
                            type="color"
                            name="<?php echo esc_attr( $option ); ?>[<?php echo esc_attr( $key ); ?>]"
                            value="<?php echo esc_attr( $settings[ $key ] ); ?>"
                        >
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <?php submit_button( 'Save Settings' ); ?>
    </form>

    <hr>

    <h2>Testing Tips</h2>

    <p>
        For desktop testing, set Minimum delay to 0,
        open a product page in a private browser window,
        then move the mouse cursor beyond the top edge
        of the browser viewport.
    </p>

    <p>
        For mobile testing, enable Mobile popup and
        set Mobile delay to 5 seconds.
    </p>
</div>
