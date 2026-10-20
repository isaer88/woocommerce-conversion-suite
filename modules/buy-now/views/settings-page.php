
<?php
/**
 * Buy Now settings page.
 */

defined( 'ABSPATH' ) || exit;

$settings = WCS_Buy_Now_Settings::get_settings();
$option   = WCS_Buy_Now_Settings::OPTION_NAME;
?>

<div class="wrap">
    <h1>Buy Now Settings</h1>

    <p>Configure the Buy Now button for WooCommerce products.</p>

    <form method="post" action="options.php">
        <?php settings_fields( 'wcs_buy_now_group' ); ?>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">Enable Buy Now</th>
                <td>
                    <label>
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr( $option ); ?>[enabled]"
                            value="yes"
                            <?php checked( $settings['enabled'], 'yes' ); ?>
                        >
                        Show the Buy Now button
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row">Button text</th>
                <td>
                    <input
                        type="text"
                        class="regular-text"
                        name="<?php echo esc_attr( $option ); ?>[button_text]"
                        value="<?php echo esc_attr( $settings['button_text'] ); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th scope="row">Redirect after adding to cart</th>
                <td>
                    <select name="<?php echo esc_attr( $option ); ?>[redirect_type]">
                        <option value="checkout" <?php selected( $settings['redirect_type'], 'checkout' ); ?>>
                            Checkout
                        </option>
                        <option value="cart" <?php selected( $settings['redirect_type'], 'cart' ); ?>>
                            Cart
                        </option>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">Clear cart before Buy Now</th>
                <td>
                    <label>
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr( $option ); ?>[clear_cart]"
                            value="yes"
                            <?php checked( $settings['clear_cart'], 'yes' ); ?>
                        >
                        Remove existing cart items before adding the selected product
                    </label>
                </td>
            </tr>
        </table>

        <?php submit_button( 'Save Settings' ); ?>
    </form>
</div>
