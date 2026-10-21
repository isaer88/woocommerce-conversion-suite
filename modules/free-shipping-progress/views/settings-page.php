<?php
/**
 * Free Shipping Progress settings view.
 */

defined( 'ABSPATH' ) || exit;

$settings = WCS_Free_Shipping_Progress_Settings::get_settings();
$option   = WCS_Free_Shipping_Progress_Settings::OPTION_NAME;
?>

<div class="wrap">
    <h1>Free Shipping Progress Settings</h1>

    <p>Display cart progress toward a configured free shipping threshold.</p>

    <form method="post" action="options.php">
        <?php settings_fields( 'wcs_free_shipping_progress_group' ); ?>

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
                        name="<?php echo esc_attr( $option ); ?>[threshold]"
                        value="<?php echo esc_attr( $settings['threshold'] ); ?>"
                        required
                    >
                    <p class="description">
                        Enter the threshold in the store's base currency.
                        Configure the corresponding WooCommerce shipping method separately.
                    </p>
                </td>
            </tr>

            <?php
            $toggles = array(
                'show_cart'     => 'Display on cart page',
                'show_checkout' => 'Display on checkout page',
            );
            ?>

            <?php foreach ( $toggles as $key => $label ) : ?>
                <tr>
                    <th scope="row"><?php echo esc_html( $label ); ?></th>
                    <td>
                        <label>
                            <input
                                type="checkbox"
                                name="<?php echo esc_attr( $option ); ?>[<?php echo esc_attr( $key ); ?>]"
                                value="yes"
                                <?php checked( $settings[ $key ], 'yes' ); ?>
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
                        name="<?php echo esc_attr( $option ); ?>[message]"
                        value="<?php echo esc_attr( $settings['message'] ); ?>"
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
                        name="<?php echo esc_attr( $option ); ?>[success]"
                        value="<?php echo esc_attr( $settings['success'] ); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th scope="row">Progress bar color</th>
                <td>
                    <input
                        type="color"
                        name="<?php echo esc_attr( $option ); ?>[bar_color]"
                        value="<?php echo esc_attr( $settings['bar_color'] ); ?>"
                    >
                </td>
            </tr>
        </table>

        <?php submit_button( 'Save Settings' ); ?>
    </form>

    <hr>

    <h2>Shortcode</h2>
    <code>[wcs_free_shipping_progress]</code>
</div>
