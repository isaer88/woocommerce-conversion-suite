<?php
/**
 * Sale Countdown settings view.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

$settings = VELTRIXA_Sale_Countdown_Settings::get_settings();
$option   = VELTRIXA_Sale_Countdown_Settings::OPTION_NAME;
?>

<div class="wrap">
    <h1>Sale Countdown Settings</h1>

    <p>
        Display a countdown timer for simple products
        with an active scheduled sale.
    </p>

    <form method="post" action="options.php">
        <?php settings_fields( 'wcs_sale_countdown_group' ); ?>

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
                        name="<?php echo esc_attr( $option ); ?>[title]"
                        value="<?php echo esc_attr( $settings['title'] ); ?>"
                    >
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
                        Show countdown on single product pages
                    </label>
                </td>
            </tr>

            <?php
            $colors = array(
                'background'   => 'Background color',
                'text_color'   => 'Text color',
                'accent_color' => 'Countdown numbers color',
            );
            ?>

            <?php foreach ( $colors as $key => $label ) : ?>
                <tr>
                    <th scope="row">
                        <label for="wcs-sc-<?php echo esc_attr( $key ); ?>">
                            <?php echo esc_html( $label ); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            id="wcs-sc-<?php echo esc_attr( $key ); ?>"
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

    <h2>How to enable the countdown</h2>

    <p>
        Go to Products, edit a simple product,
        set its Sale price, click Schedule,
        and choose an end date.
    </p>

    <h2>Shortcode</h2>

    <code>[wcs_sale_countdown]</code>

    <p class="description">
        Use this shortcode on a single product page.
        Disable Automatic display to prevent duplicates.
    </p>
</div>
