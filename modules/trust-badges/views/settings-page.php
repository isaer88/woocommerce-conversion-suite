<?php
/**
 * Trust Badges settings view.
 *
 * @package WooCommerce_Conversion_Suite
 */

defined( 'ABSPATH' ) || exit;

$settings = WCS_Trust_Badges_Settings::get_settings();
$option   = WCS_Trust_Badges_Settings::OPTION_NAME;
$icons    = WCS_Trust_Badges_Settings::icons();
$defaults = WCS_Trust_Badges_Settings::default_badges();
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
        <?php settings_fields( 'wcs_trust_badges_group' ); ?>

        <h2>General Settings</h2>

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
                </td>
            </tr>

            <?php
            $colors = array(
                'background' => 'Background color',
                'text_color' => 'Text color',
                'icon_color' => 'Icon color',
            );
            ?>

            <?php foreach ( $colors as $key => $label ) : ?>
                <tr>
                    <th scope="row">
                        <label for="wcs-tb-<?php echo esc_attr( $key ); ?>">
                            <?php echo esc_html( $label ); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            id="wcs-tb-<?php echo esc_attr( $key ); ?>"
                            type="color"
                            name="<?php echo esc_attr( $option ); ?>[<?php echo esc_attr( $key ); ?>]"
                            value="<?php echo esc_attr( $settings[ $key ] ); ?>"
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
                <?php foreach ( $defaults as $index => $default ) : ?>
                    <?php
                    $badge = isset( $settings['badges'][ $index ] )
                        && is_array( $settings['badges'][ $index ] )
                            ? wp_parse_args(
                                $settings['badges'][ $index ],
                                $default
                            )
                            : $default;
                    ?>

                    <tr>
                        <td>
                            <input
                                type="checkbox"
                                aria-label="<?php echo esc_attr( sprintf( 'Enable badge %d', $index + 1 ) ); ?>"
                                name="<?php echo esc_attr( $option ); ?>[badges][<?php echo esc_attr( $index ); ?>][enabled]"
                                value="yes"
                                <?php checked( $badge['enabled'], 'yes' ); ?>
                            >
                        </td>

                        <td>
                            <strong>
                                <?php echo esc_html( $index + 1 ); ?>
                            </strong>
                        </td>

                        <td>
                            <select
                                name="<?php echo esc_attr( $option ); ?>[badges][<?php echo esc_attr( $index ); ?>][icon]"
                                aria-label="<?php echo esc_attr( sprintf( 'Icon for badge %d', $index + 1 ) ); ?>"
                            >
                                <?php foreach ( $icons as $value => $label ) : ?>
                                    <option
                                        value="<?php echo esc_attr( $value ); ?>"
                                        <?php selected( $badge['icon'], $value ); ?>
                                    >
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>

                        <td>
                            <input
                                type="text"
                                class="regular-text"
                                maxlength="120"
                                name="<?php echo esc_attr( $option ); ?>[badges][<?php echo esc_attr( $index ); ?>][text]"
                                value="<?php echo esc_attr( $badge['text'] ); ?>"
                                aria-label="<?php echo esc_attr( sprintf( 'Text for badge %d', $index + 1 ) ); ?>"
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

    <code>[wcs_trust_badges]</code>

    <p class="description">
        Disable Automatic display if you place the shortcode
        manually to prevent duplicate badge sections.
    </p>
</div>
