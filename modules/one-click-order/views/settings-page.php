<?php
/**
 * One Click Order settings page.
 */

defined( 'ABSPATH' ) || exit;

$settings = VELTRIXA_One_Click_Order_Settings::get_settings();
$option = VELTRIXA_One_Click_Order_Settings::OPTION_NAME;
?>

<div class="wrap">
    <h1>One Click Order Settings</h1>

    <p>
        Configure a quick order request form for simple
        WooCommerce products.
    </p>

    <div class="notice notice-warning inline">
        <p>
            <strong>Important:</strong>
            Quick Order creates on-hold orders without
            online payment or shipping calculation.
            Confirm delivery and the final payable amount
            with the customer before fulfillment.
        </p>
    </div>

    <form method="post" action="options.php">
        <?php settings_fields( 'wcs_one_click_order_group' ); ?>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">Enable Quick Order</th>
                <td>
                    <label>
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr( $option ); ?>[enabled]"
                            value="yes"
                            <?php checked( $settings['enabled'], 'yes' ); ?>
                        >
                        Enable quick order requests
                    </label>
                </td>
            </tr>

            <?php
            $fields = array(
                'button_text' => 'Button text',
                'modal_title' => 'Modal title',
                'intro_text' => 'Introductory message',
                'success_text' => 'Success message',
            );
            ?>

            <?php foreach ( $fields as $key => $label ) : ?>
                <tr>
                    <th scope="row">
                        <label for="wcs-oco-<?php echo esc_attr( $key ); ?>">
                            <?php echo esc_html( $label ); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            id="wcs-oco-<?php echo esc_attr( $key ); ?>"
                            type="text"
                            class="large-text"
                            name="<?php echo esc_attr( $option ); ?>[<?php echo esc_attr( $key ); ?>]"
                            value="<?php echo esc_attr( $settings[ $key ] ); ?>"
                        >
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php
            $colors = array(
                'button_color' => 'Button background',
                'text_color' => 'Button text color',
            );
            ?>

            <?php foreach ( $colors as $key => $label ) : ?>
                <tr>
                    <th scope="row">
                        <label for="wcs-oco-<?php echo esc_attr( $key ); ?>">
                            <?php echo esc_html( $label ); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            id="wcs-oco-<?php echo esc_attr( $key ); ?>"
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

    <h2>Supported Products</h2>

    <p>
        Simple, published, visible, purchasable products
        that are in stock and do not allow backorders.
        Taxable products are excluded when WooCommerce
        tax calculation is enabled.
    </p>

    <h2>Order Processing</h2>

    <p>
        Requests are created with the on-hold status.
        Review each order in WooCommerce → Orders,
        confirm shipping costs and the final amount,
        then contact the customer.
    </p>
</div>
