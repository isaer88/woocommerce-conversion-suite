<?php
/**
 * WhatsApp Order settings page.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

$settings = VELTRIXA_WhatsApp_Order_Settings::get_settings();
$option   = VELTRIXA_WhatsApp_Order_Settings::OPTION_NAME;
?>

<div class="wrap">
    <h1>WhatsApp Order Settings</h1>

    <p>
        Allow customers to contact your store about products
        through WhatsApp.
    </p>

    <div class="notice notice-info inline">
        <p>
            WhatsApp Order prepares a message for the customer.
            It does not automatically send the message or
            create a WooCommerce order.
        </p>
    </div>

    <?php settings_errors( $option ); ?>

    <form method="post" action="options.php">
        <?php settings_fields( 'wcs_whatsapp_order_group' ); ?>

        <h2>General Settings</h2>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">Enable WhatsApp Order</th>
                <td>
                    <label>
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr( $option ); ?>[enabled]"
                            value="yes"
                            <?php checked( $settings['enabled'], 'yes' ); ?>
                        >
                        Enable WhatsApp ordering
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="wcs-wa-phone">
                        WhatsApp phone number
                    </label>
                </th>
                <td>
                    <input
                        id="wcs-wa-phone"
                        type="tel"
                        class="regular-text"
                        name="<?php echo esc_attr( $option ); ?>[phone]"
                        value="<?php echo esc_attr( $settings['phone'] ); ?>"
                        placeholder="380XXXXXXXXX"
                        required
                    >
                    <p class="description">
                        International number with country code.
                        Example: 380XXXXXXXXX.
                        Do not include the + sign in the saved number.
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="wcs-wa-button-text">
                        Button text
                    </label>
                </th>
                <td>
                    <input
                        id="wcs-wa-button-text"
                        type="text"
                        class="regular-text"
                        name="<?php echo esc_attr( $option ); ?>[button_text]"
                        value="<?php echo esc_attr( $settings['button_text'] ); ?>"
                        maxlength="100"
                    >
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="wcs-wa-message">
                        Message template
                    </label>
                </th>
                <td>
                    <textarea
                        id="wcs-wa-message"
                        name="<?php echo esc_attr( $option ); ?>[message]"
                        rows="12"
                        class="large-text code"
                    ><?php echo esc_textarea( $settings['message'] ); ?></textarea>

                    <p class="description">
                        Supported placeholders:
                        <code>{product_name}</code>,
                        <code>{price}</code>,
                        <code>{quantity}</code>,
                        <code>{product_url}</code>,
                        <code>{sku}</code>,
                        <code>{variation}</code>.
                    </p>
                </td>
            </tr>
        </table>

        <hr>

        <h2>Display Settings</h2>

        <table class="form-table" role="presentation">
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
                        Display after Add to Cart form
                    </label>

                    <p class="description">
                        Disable this option if you want to use
                        the shortcode manually.
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">Open WhatsApp</th>
                <td>
                    <label>
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr( $option ); ?>[open_new_tab]"
                            value="yes"
                            <?php checked( $settings['open_new_tab'], 'yes' ); ?>
                        >
                        Open in a new tab
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="wcs-wa-button-color">
                        Button background
                    </label>
                </th>
                <td>
                    <input
                        id="wcs-wa-button-color"
                        type="color"
                        name="<?php echo esc_attr( $option ); ?>[button_color]"
                        value="<?php echo esc_attr( $settings['button_color'] ); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="wcs-wa-text-color">
                        Button text color
                    </label>
                </th>
                <td>
                    <input
                        id="wcs-wa-text-color"
                        type="color"
                        name="<?php echo esc_attr( $option ); ?>[text_color]"
                        value="<?php echo esc_attr( $settings['text_color'] ); ?>"
                    >
                </td>
            </tr>
        </table>

        <?php submit_button( 'Save Settings' ); ?>
    </form>

    <hr>

    <h2>Shortcode</h2>

    <p>
        Use this shortcode inside the single product page:
    </p>

    <p>
        <code>[wcs_whatsapp_order]</code>
    </p>

    <p>
        When using the shortcode, disable Automatic display
        to prevent duplicate buttons.
    </p>

    <h2>Supported Products</h2>

    <p>
        Simple and Variable WooCommerce products.
        For variable products, the customer must select
        an available variation before ordering.
    </p>
</div>
