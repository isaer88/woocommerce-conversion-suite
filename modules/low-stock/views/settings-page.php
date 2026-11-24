<?php
/**
 * Low Stock settings view.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

$veltrixa_ls_settings = VELTRIXA_Low_Stock_Settings::get_settings();
$veltrixa_ls_option   = VELTRIXA_Low_Stock_Settings::OPTION_NAME;
?>

<div class="wrap wcs-module-settings">
    <p class="wcs-back"><a href="<?php echo esc_url( admin_url( 'admin.php?page=' . VELTRIXA_Admin::MENU_SLUG ) ); ?>">&larr; <?php esc_html_e( 'Back to Dashboard', 'veltrixa-toolkit-for-woocommerce' ); ?></a></p>
    <h1>Low Stock Settings</h1>

    <p>
        Display low-stock alerts using actual WooCommerce
        inventory quantities.
    </p>

    <form method="post" action="options.php">
        <?php settings_fields( 'veltrixa_low_stock_group' ); ?>

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
                        name="<?php echo esc_attr( $veltrixa_ls_option ); ?>[threshold]"
                        value="<?php echo esc_attr( $veltrixa_ls_settings['threshold'] ); ?>"
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
                        name="<?php echo esc_attr( $veltrixa_ls_option ); ?>[message]"
                        value="<?php echo esc_attr( $veltrixa_ls_settings['message'] ); ?>"
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
                            name="<?php echo esc_attr( $veltrixa_ls_option ); ?>[auto_display]"
                            value="yes"
                            <?php checked( $veltrixa_ls_settings['auto_display'], 'yes' ); ?>
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
                            name="<?php echo esc_attr( $veltrixa_ls_option ); ?>[show_bar]"
                            value="yes"
                            <?php checked( $veltrixa_ls_settings['show_bar'], 'yes' ); ?>
                        >
                        Display progress bar
                    </label>
                </td>
            </tr>

            <?php
            $veltrixa_ls_colors = array(
                'background' => 'Background color',
                'text_color' => 'Text color',
                'bar_color'  => 'Progress bar and border color',
            );
            ?>

            <?php foreach ( $veltrixa_ls_colors as $veltrixa_ls_key => $veltrixa_ls_label ) : ?>
                <tr>
                    <th scope="row">
                        <label for="wcs-ls-<?php echo esc_attr( $veltrixa_ls_key ); ?>">
                            <?php echo esc_html( $veltrixa_ls_label ); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            id="wcs-ls-<?php echo esc_attr( $veltrixa_ls_key ); ?>"
                            type="color"
                            name="<?php echo esc_attr( $veltrixa_ls_option ); ?>[<?php echo esc_attr( $veltrixa_ls_key ); ?>]"
                            value="<?php echo esc_attr( $veltrixa_ls_settings[ $veltrixa_ls_key ] ); ?>"
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

    <code>[veltrixa_low_stock]</code>

    <p class="description">
        Intended for single product pages.
        Disable Automatic display to avoid duplicate notices.
    </p>
</div>
