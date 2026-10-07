<?php
/**
 * Páginas de agradecimiento para los formularios comerciales de baterías y montacargas.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Configuración canónica compartida por el shortcode y el creador WP-CLI.
 */
function tmd_commercial_thank_you_page_specs(): array
{
    return [
        'battery' => [
            'slug' => 'gracias-baterias',
            'title' => 'Gracias por tu solicitud de baterías',
            'shortcode' => '[tmd_commercial_thank_you type="battery"]',
            'robots' => ['noindex', 'follow'],
            'landing_page_id' => 1559,
            'contact_form_id' => 1557,
            'redirect_path' => '/gracias-baterias/',
        ],
        'forklift' => [
            'slug' => 'gracias-montacargas',
            'title' => 'Gracias por tu solicitud de montacargas',
            'shortcode' => '[tmd_commercial_thank_you type="forklift"]',
            'robots' => ['noindex', 'follow'],
            'landing_page_id' => 1558,
            'contact_form_id' => 1556,
            'redirect_path' => '/gracias-montacargas/',
        ],
    ];
}

/**
 * Renderiza una confirmación distinta para cada flujo comercial.
 *
 * @param array<string, mixed> $attributes Shortcode attributes.
 */
function tmd_commercial_thank_you_page($attributes): string
{
    $attributes = shortcode_atts([
        'type' => '',
    ], (array) $attributes, 'tmd_commercial_thank_you');
    $type = sanitize_key((string) $attributes['type']);
    $spec = tmd_commercial_thank_you_page_specs()[$type] ?? null;
    if (! is_array($spec)) {
        return '';
    }

    $heading_id = 'tmd-commercial-thank-you-heading-' . $type;
    ob_start();
    ?>
    <section class="tmd-commercial-thank-you" aria-labelledby="<?php echo esc_attr($heading_id); ?>">
      <h1 id="<?php echo esc_attr($heading_id); ?>"><?php echo esc_html($spec['title']); ?></h1>
      <p>Hemos recibido tu solicitud. Gracias por contactar a Tecnimontacargas.</p>
    </section>
    <?php

    return (string) ob_get_clean();
}
add_shortcode('tmd_commercial_thank_you', 'tmd_commercial_thank_you_page');

add_filter('body_class', static function (array $classes): array {
    $slugs = array_column(tmd_commercial_thank_you_page_specs(), 'slug');
    if (is_page($slugs)) {
        $classes[] = 'tmd-commercial-thank-you-page';
    }

    return $classes;
});

add_action('wp_enqueue_scripts', static function (): void {
    $slugs = array_column(tmd_commercial_thank_you_page_specs(), 'slug');
    if (! is_page($slugs)) {
        return;
    }

    $css = get_stylesheet_directory() . '/assets/css/tmd-commercial-landing-thank-you.css';
    wp_enqueue_style(
        'tmd-commercial-landing-thank-you',
        get_stylesheet_directory_uri() . '/assets/css/tmd-commercial-landing-thank-you.css',
        ['tm-work-sans'],
        file_exists($css) ? filemtime($css) : '1.0.0'
    );
});

add_action('wp_enqueue_scripts', static function (): void {
    $redirects = [];
    foreach (tmd_commercial_thank_you_page_specs() as $spec) {
        if (is_page((int) $spec['landing_page_id'])) {
            $redirects[(string) $spec['contact_form_id']] = $spec['redirect_path'];
            break;
        }
    }
    if (! $redirects) {
        return;
    }

    $script = get_stylesheet_directory() . '/assets/js/tmd-commercial-landing-thank-you-redirect.js';
    wp_enqueue_script(
        'tmd-commercial-landing-thank-you-redirect',
        get_stylesheet_directory_uri() . '/assets/js/tmd-commercial-landing-thank-you-redirect.js',
        ['contact-form-7'],
        file_exists($script) ? filemtime($script) : '1.0.0',
        true
    );
    wp_localize_script(
        'tmd-commercial-landing-thank-you-redirect',
        'tmdCommercialThankYouRedirects',
        $redirects
    );
}, 99);
