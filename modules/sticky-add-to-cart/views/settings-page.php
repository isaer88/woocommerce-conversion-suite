<?php
/**
 * Sticky Add to Cart settings view.
 */

defined( 'ABSPATH' ) || exit;

$veltrixa_sticky_settings = VELTRIXA_Sticky_Add_To_Cart_Settings::get_settings();
$veltrixa_sticky_option   = VELTRIXA_Sticky_Add_To_Cart_Settings::OPTION_NAME;
?>

<div class="wrap">
    <h1>Sticky Add to Cart Settings</h1>

    <p>Configure the floating purchase bar on WooCommerce product pages.</p>

    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php settings_fields( 'veltrixa_sticky_add_to_cart_group' ); ?>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">Bar position</th>
                <td>
                    <select name="<?php echo esc_attr( $veltrixa_sticky_option ); ?>[position]">
                        <option value="bottom" <?php selected( $veltrixa_sticky_settings['position'], 'bottom' ); ?>>
                            Bottom
                        </option>
                        <option value="top" <?php selected( $veltrixa_sticky_settings['position'], 'top' ); ?>>
                            Top
                        </option>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">Mobile devices</th>
                <td>
                    <label>
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr( $veltrixa_sticky_option ); ?>[show_mobile]"
                            value="yes"
                            <?php checked( $veltrixa_sticky_settings['show_mobile'], 'yes' ); ?>
                        >
                        Show sticky bar on mobile
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row">Product image</th>
                <td>
                    <label>
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr( $veltrixa_sticky_option ); ?>[show_image]"
                            value="yes"
                            <?php checked( $veltrixa_sticky_settings['show_image'], 'yes' ); ?>
                        >
                        Show product thumbnail
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="wcs-sticky-button-text">Button text</label>
                </th>
                <td>
                    <input
                        type="text"
                        id="wcs-sticky-button-text"
                        class="regular-text"
                        name="<?php echo esc_attr( $veltrixa_sticky_option ); ?>[button_text]"
                        value="<?php echo esc_attr( $veltrixa_sticky_settings['button_text'] ); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="wcs-sticky-background">Background color</label>
                </th>
                <td>
                    <input
                        type="color"
                        id="wcs-sticky-background"
                        name="<?php echo esc_attr( $veltrixa_sticky_option ); ?>[background]"
                        value="<?php echo esc_attr( $veltrixa_sticky_settings['background'] ); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="wcs-sticky-button-color">Button color</label>
                </th>
                <td>
                    <input
                        type="color"
                        id="wcs-sticky-button-color"
                        name="<?php echo esc_attr( $veltrixa_sticky_option ); ?>[button_color]"
                        value="<?php echo esc_attr( $veltrixa_sticky_settings['button_color'] ); ?>"
                    >
                </td>
            </tr>
        </table>

        <?php submit_button( 'Save Settings' ); ?>
    </form>

    <p>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=woocommerce-conversion-suite' ) ); ?>">
            &larr; Back to Conversion Suite
        </a>
    </p>
</div>
