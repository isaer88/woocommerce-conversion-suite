<?php
/**
 * Low Stock settings view.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

$settings = WCS_Low_Stock_Settings::get_settings();
$option   = WCS_Low_Stock_Settings::OPTION_NAME;
?>

<div class="wrap">
    <h1>Low Stock Settings</h1>

    <p>
        Display low-stock alerts using actual WooCommerce
        inventory quantities.
    </p>

    <form method="post" action="options.php">
        <?php settings_fields( 'wcs_low_stock_group' ); ?>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="wcs-ls-threshold">
                        Low stock threshold
                    </label>
                </th>
                <td>
                    <input
                        id="wcs-ls-threshold"
                        type="number"
                        min="1"
                        max="9999"
                        step="1"
                        name="<?php echo esc_attr( $option ); ?>[threshold]"
                        value="<?php echo esc_attr( $settings['threshold'] ); ?>"
                        required
                    >
                    <p class="description">
                        Show the notice when stock is greater than zero
                        and less than or equal to this threshold.
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="wcs-ls-message">
                        Alert message
                    </label>
                </th>
                <td>
                    <input
                        id="wcs-ls-message"
                        type="text"
                        class="large-text"
                        name="<?php echo esc_attr( $option ); ?>[message]"
                        value="<?php echo esc_attr( $settings['message'] ); ?>"
                    >
                    <p class="description">
                        Use {stock} to display the remaining quantity.
                    </p>
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
                        Show automatically on single product pages
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row">Stock progress bar</th>
                <td>
                    <label>
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr( $option ); ?>[show_bar]"
                            value="yes"
                            <?php checked( $settings['show_bar'], 'yes' ); ?>
                        >
                        Display progress bar
                    </label>
                </td>
            </tr>

            <?php
            $colors = array(
                'background' => 'Background color',
                'text_color' => 'Text color',
                'bar_color'  => 'Progress bar and border color',
            );
            ?>

            <?php foreach ( $colors as $key => $label ) : ?>
                <tr>
                    <th scope="row">
                        <label for="wcs-ls-<?php echo esc_attr( $key ); ?>">
                            <?php echo esc_html( $label ); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            id="wcs-ls-<?php echo esc_attr( $key ); ?>"
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

    <h2>How to test</h2>

    <p>
        Edit a simple product, open Product data &rarr; Inventory,
        enable stock management and set a quantity below the
        configured threshold.
    </p>

    <h2>Shortcode</h2>

    <code>[wcs_low_stock]</code>

    <p class="description">
        Intended for single product pages.
        Disable Automatic display to avoid duplicate notices.
    </p>
</div>
