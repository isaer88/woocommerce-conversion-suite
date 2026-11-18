<?php
/**
 * Trust Badges settings view.
 *
 * @package Veltrixa_Toolkit
 */

defined( 'ABSPATH' ) || exit;

$veltrixa_tb_settings = VELTRIXA_Trust_Badges_Settings::get_settings();
$veltrixa_tb_option   = VELTRIXA_Trust_Badges_Settings::OPTION_NAME;
$veltrixa_tb_icons    = VELTRIXA_Trust_Badges_Settings::icons();
$veltrixa_tb_defaults = VELTRIXA_Trust_Badges_Settings::default_badges();
?>

<div class="wrap">
    <h1>Trust Badges Settings</h1>

    <p>
        Configure the trust messages displayed near the
        Add to Cart button on WooCommerce product pages.
    </p>

    <div class="notice notice-info inline">
        <p>
            Enable only messages that accurately describe
            your store's payment, shipping, returns,
            support or quality policies.
        </p>
    </div>

    <form method="post" action="options.php">
        <?php settings_fields( 'veltrixa_trust_badges_group' ); ?>

        <h2>General Settings</h2>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">Automatic display</th>
                <td>
                    <label>
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr( $veltrixa_tb_option ); ?>[auto_display]"
                            value="yes"
                            <?php checked( $veltrixa_tb_settings['auto_display'], 'yes' ); ?>
                        >
                        Display after Add to Cart form
                    </label>
                </td>
            </tr>

            <?php
            $veltrixa_tb_colors = array(
                'background' => 'Background color',
                'text_color' => 'Text color',
                'icon_color' => 'Icon color',
            );
            ?>

            <?php foreach ( $veltrixa_tb_colors as $veltrixa_tb_key => $veltrixa_tb_label ) : ?>
                <tr>
                    <th scope="row">
                        <label for="wcs-tb-<?php echo esc_attr( $veltrixa_tb_key ); ?>">
                            <?php echo esc_html( $veltrixa_tb_label ); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            id="wcs-tb-<?php echo esc_attr( $veltrixa_tb_key ); ?>"
                            type="color"
                            name="<?php echo esc_attr( $veltrixa_tb_option ); ?>[<?php echo esc_attr( $veltrixa_tb_key ); ?>]"
                            value="<?php echo esc_attr( $veltrixa_tb_settings[ $veltrixa_tb_key ] ); ?>"
                        >
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <hr>

        <h2>Badges</h2>

        <p>Configure up to six independent trust messages.</p>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th style="width:70px;">Enable</th>
                    <th style="width:100px;">Badge</th>
                    <th style="width:180px;">Icon</th>
                    <th>Text</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ( $veltrixa_tb_defaults as $veltrixa_tb_index => $veltrixa_tb_default ) : ?>
                    <?php
                    $veltrixa_tb_badge = isset( $veltrixa_tb_settings['badges'][ $veltrixa_tb_index ] )
                        && is_array( $veltrixa_tb_settings['badges'][ $veltrixa_tb_index ] )
                            ? wp_parse_args(
                                $veltrixa_tb_settings['badges'][ $veltrixa_tb_index ],
                                $veltrixa_tb_default
                            )
                            : $veltrixa_tb_default;
                    ?>

                    <tr>
                        <td>
                            <input
                                type="checkbox"
                                aria-label="<?php echo esc_attr( sprintf( 'Enable badge %d', $veltrixa_tb_index + 1 ) ); ?>"
                                name="<?php echo esc_attr( $veltrixa_tb_option ); ?>[badges][<?php echo esc_attr( $veltrixa_tb_index ); ?>][enabled]"
                                value="yes"
                                <?php checked( $veltrixa_tb_badge['enabled'], 'yes' ); ?>
                            >
                        </td>

                        <td>
                            <strong>
                                <?php echo esc_html( $veltrixa_tb_index + 1 ); ?>
                            </strong>
                        </td>

                        <td>
                            <select
                                name="<?php echo esc_attr( $veltrixa_tb_option ); ?>[badges][<?php echo esc_attr( $veltrixa_tb_index ); ?>][icon]"
                                aria-label="<?php echo esc_attr( sprintf( 'Icon for badge %d', $veltrixa_tb_index + 1 ) ); ?>"
                            >
                                <?php foreach ( $veltrixa_tb_icons as $veltrixa_tb_value => $veltrixa_tb_label ) : ?>
                                    <option
                                        value="<?php echo esc_attr( $veltrixa_tb_value ); ?>"
                                        <?php selected( $veltrixa_tb_badge['icon'], $veltrixa_tb_value ); ?>
                                    >
                                        <?php echo esc_html( $veltrixa_tb_label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>

                        <td>
                            <input
                                type="text"
                                class="regular-text"
                                maxlength="120"
                                name="<?php echo esc_attr( $veltrixa_tb_option ); ?>[badges][<?php echo esc_attr( $veltrixa_tb_index ); ?>][text]"
                                value="<?php echo esc_attr( $veltrixa_tb_badge['text'] ); ?>"
                                aria-label="<?php echo esc_attr( sprintf( 'Text for badge %d', $veltrixa_tb_index + 1 ) ); ?>"
                            >
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php submit_button( 'Save Settings' ); ?>
    </form>

    <hr>

    <h2>Shortcode</h2>

    <p>
        Use the following shortcode on a single product page:
    </p>

    <code>[veltrixa_trust_badges]</code>

    <p class="description">
        Disable Automatic display if you place the shortcode
        manually to prevent duplicate badge sections.
    </p>
</div>
