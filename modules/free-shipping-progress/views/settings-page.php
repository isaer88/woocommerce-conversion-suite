<?php
/**
 * Free Shipping Progress settings view.
 */

defined( 'ABSPATH' ) || exit;

$veltrixa_fsp_settings = VELTRIXA_Free_Shipping_Progress_Settings::get_settings();
$veltrixa_fsp_option   = VELTRIXA_Free_Shipping_Progress_Settings::OPTION_NAME;
?>

<div class="wrap wcs-module-settings">
    <p class="wcs-back"><a href="<?php echo esc_url( admin_url( 'admin.php?page=' . VELTRIXA_Admin::MENU_SLUG ) ); ?>">&larr; <?php esc_html_e( 'Back to Dashboard', 'veltrixa-toolkit-for-woocommerce' ); ?></a></p>
    <h1>Free Shipping Progress Settings</h1>

    <p>Display cart progress toward a configured free shipping threshold.</p>

    <form method="post" action="options.php">
        <?php settings_fields( 'veltrixa_free_shipping_progress_group' ); ?>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="wcs-fsp-threshold">Free shipping threshold</label>
                </th>
                <td>
                    <input
                        id="wcs-fsp-threshold"
                        type="number"
                        min="0.01"
                        step="0.01"
                        name="<?php echo esc_attr( $veltrixa_fsp_option ); ?>[threshold]"
                        value="<?php echo esc_attr( $veltrixa_fsp_settings['threshold'] ); ?>"
                        required
                    >
                    <p class="description">
                        Enter the threshold in the store's base currency.
                        Configure the corresponding WooCommerce shipping method separately.
                    </p>
                </td>
            </tr>

            <?php
            $veltrixa_fsp_toggles = array(
                'show_cart'     => 'Display on cart page',
                'show_checkout' => 'Display on checkout page',
            );
            ?>

            <?php foreach ( $veltrixa_fsp_toggles as $veltrixa_fsp_key => $veltrixa_fsp_label ) : ?>
                <tr>
                    <th scope="row"><?php echo esc_html( $veltrixa_fsp_label ); ?></th>
                    <td>
                        <label>
                            <input
                                type="checkbox"
                                name="<?php echo esc_attr( $veltrixa_fsp_option ); ?>[<?php echo esc_attr( $veltrixa_fsp_key ); ?>]"
                                value="yes"
                                <?php checked( $veltrixa_fsp_settings[ $veltrixa_fsp_key ], 'yes' ); ?>
                            >
                            Enabled
                        </label>
                    </td>
                </tr>
            <?php endforeach; ?>

            <tr>
                <th scope="row">Progress message</th>
                <td>
                    <input
                        type="text"
                        class="large-text"
                        name="<?php echo esc_attr( $veltrixa_fsp_option ); ?>[message]"
                        value="<?php echo esc_attr( $veltrixa_fsp_settings['message'] ); ?>"
                    >
                    <p class="description">Use {amount} for the remaining amount.</p>
                </td>
            </tr>

            <tr>
                <th scope="row">Success message</th>
                <td>
                    <input
                        type="text"
                        class="large-text"
                        name="<?php echo esc_attr( $veltrixa_fsp_option ); ?>[success]"
                        value="<?php echo esc_attr( $veltrixa_fsp_settings['success'] ); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th scope="row">Progress bar color</th>
                <td>
                    <input
                        type="color"
                        name="<?php echo esc_attr( $veltrixa_fsp_option ); ?>[bar_color]"
                        value="<?php echo esc_attr( $veltrixa_fsp_settings['bar_color'] ); ?>"
                    >
                </td>
            </tr>
        </table>

        <?php submit_button( 'Save Settings' ); ?>
    </form>

    <hr>

    <h2>Shortcode</h2>
    <code>[veltrixa_free_shipping_progress]</code>
</div>
