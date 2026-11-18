<?php
/**
 * Quick Checkout settings view.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

$veltrixa_qc_settings = VELTRIXA_Quick_Checkout_Settings::get_settings();
$veltrixa_qc_option   = VELTRIXA_Quick_Checkout_Settings::OPTION_NAME;

$veltrixa_qc_fields = array(
    'hide_company' => array(
        'label'       => 'Hide company fields',
        'description' => 'Remove company fields from billing and shipping.',
    ),
    'hide_address_2' => array(
        'label'       => 'Hide address line 2',
        'description' => 'Remove secondary address fields.',
    ),
    'hide_order_notes' => array(
        'label'       => 'Hide order notes',
        'description' => 'Hide the optional order comments field.',
    ),
    'optional_postcode' => array(
        'label'       => 'Make postcode optional',
        'description' => 'May not be compatible with every shipping method.',
    ),
    'require_phone' => array(
        'label'       => 'Require phone number',
        'description' => 'Make the billing phone field mandatory.',
    ),
);
?>

<div class="wrap">
    <h1>Quick Checkout Settings</h1>

    <p>Configure the classic WooCommerce checkout form.</p>

    <form method="post" action="options.php">
        <?php settings_fields( 'veltrixa_quick_checkout_group' ); ?>

        <table class="form-table" role="presentation">
            <?php foreach ( $veltrixa_qc_fields as $veltrixa_qc_key => $veltrixa_qc_field ) : ?>
                <tr>
                    <th scope="row">
                        <?php echo esc_html( $veltrixa_qc_field['label'] ); ?>
                    </th>
                    <td>
                        <label>
                            <input
                                type="checkbox"
                                name="<?php echo esc_attr( $veltrixa_qc_option ); ?>[<?php echo esc_attr( $veltrixa_qc_key ); ?>]"
                                value="yes"
                                <?php checked( $veltrixa_qc_settings[ $veltrixa_qc_key ], 'yes' ); ?>
                            >
                            <?php echo esc_html( $veltrixa_qc_field['description'] ); ?>
                        </label>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <p class="description">
            Billing email remains required for checkout compatibility.
            Shipping and payment validation remain managed by WooCommerce.
        </p>

        <?php submit_button( 'Save Settings' ); ?>
    </form>

    <p>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=woocommerce-conversion-suite' ) ); ?>">
            &larr; Back to Conversion Suite
        </a>
    </p>
</div>
