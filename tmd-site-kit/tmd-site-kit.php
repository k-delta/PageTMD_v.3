<?php
/**
 * Plugin Name: TMD Site Kit
 * Description: Componentes configurables para Tecni Montacargas Dual sin tocar el tema.
 * Version: 0.1.1
 * Author: TMD
 */

if (!defined('ABSPATH')) {
    exit;
}

const TMD_SITE_KIT_VERSION = '0.1.1';

function tmd_site_kit_defaults(): array
{
    return [
        'phone' => '573015556180',
        'whatsapp_text' => 'Hola, quiero recibir asesoria sobre equipos, repuestos o servicio tecnico de Tecni Montacargas Dual.',
        'email' => 'info@tmdual.com',
        'maps_query' => 'Carrera 108 No.22F-21, Bogota, Colombia',
        'linkedin_url' => 'https://www.linkedin.com/company/108105080/',
    ];
}

function tmd_site_kit_option(string $key): string
{
    $defaults = tmd_site_kit_defaults();
    $value = get_option('tmd_site_kit_' . $key, $defaults[$key] ?? '');

    return is_string($value) && $value !== '' ? $value : ($defaults[$key] ?? '');
}

function tmd_site_kit_enqueue_assets(): void
{
    wp_enqueue_style(
        'tmd-work-sans',
        'https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'tmd-site-kit',
        plugins_url('tmd-site-kit.css', __FILE__),
        [],
        TMD_SITE_KIT_VERSION
    );

    wp_enqueue_script(
        'tmd-site-kit',
        plugins_url('tmd-site-kit.js', __FILE__),
        [],
        TMD_SITE_KIT_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'tmd_site_kit_enqueue_assets');

function tmd_site_kit_register_settings(): void
{
    foreach (array_keys(tmd_site_kit_defaults()) as $key) {
        register_setting('tmd_site_kit', 'tmd_site_kit_' . $key, [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => tmd_site_kit_defaults()[$key],
        ]);
    }
}
add_action('admin_init', 'tmd_site_kit_register_settings');

function tmd_site_kit_settings_page(): void
{
    add_options_page(
        'TMD Site Kit',
        'TMD Site Kit',
        'manage_options',
        'tmd-site-kit',
        'tmd_site_kit_render_settings_page'
    );
}
add_action('admin_menu', 'tmd_site_kit_settings_page');

function tmd_site_kit_render_settings_page(): void
{
    $fields = [
        'phone' => 'WhatsApp en formato internacional',
        'whatsapp_text' => 'Mensaje inicial de WhatsApp',
        'email' => 'Correo',
        'maps_query' => 'Direccion para Google Maps',
        'linkedin_url' => 'URL de LinkedIn',
    ];
    ?>
    <div class="wrap">
        <h1>TMD Site Kit</h1>
        <form method="post" action="options.php">
            <?php settings_fields('tmd_site_kit'); ?>
            <table class="form-table" role="presentation">
                <?php foreach ($fields as $key => $label) : ?>
                    <tr>
                        <th scope="row"><label for="tmd_site_kit_<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></th>
                        <td>
                            <input
                                class="regular-text"
                                id="tmd_site_kit_<?php echo esc_attr($key); ?>"
                                name="tmd_site_kit_<?php echo esc_attr($key); ?>"
                                value="<?php echo esc_attr(tmd_site_kit_option($key)); ?>"
                            />
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
