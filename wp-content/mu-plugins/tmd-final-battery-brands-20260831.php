<?php
/** Add the two battery brands requested in the final web review. */
defined('ABSPATH') || exit;

add_filter('the_content', static function (string $content): string {
    if (!is_page(47) || !str_contains($content, 'tmd-brand-carousel__slide')) {
        return $content;
    }

    if (str_contains($content, 'data-tmd-brand="Coexito"') && str_contains($content, 'data-tmd-brand="Duncan"')) {
        return $content;
    }

    $barbillon = '<figure class="tmd-brand-carousel__slide"><img src="https://tecnimontacargas.com/wp-content/themes/blocksy-child/assets/images/brands/barbillon-aliado.webp" alt="Barbillon" loading="lazy"></figure>';
    if (!str_contains($content, $barbillon)) {
        return $content;
    }

    $brands = <<<'HTML'
      <figure class="tmd-brand-carousel__slide tmd-brand-carousel__slide--wordmark" data-tmd-brand="Coexito"><span aria-label="Coexito">COEXITO</span></figure>
      <figure class="tmd-brand-carousel__slide tmd-brand-carousel__slide--wordmark" data-tmd-brand="Duncan"><span aria-label="Duncan">DUNCAN</span></figure>
HTML;

    return str_replace($barbillon, $barbillon . "\n" . $brands, $content);
}, 105);

add_action('wp_head', static function (): void {
    if (!is_page(47)) {
        return;
    }
    ?>
    <style id="tmd-final-battery-brands">
      .tmd-brand-carousel__slide--wordmark{align-items:center;display:flex;justify-content:center;min-height:86px;padding:1rem 1.35rem}
      .tmd-brand-carousel__slide--wordmark span{color:#262e4f;font-size:clamp(1.15rem,2vw,1.55rem);font-weight:800;letter-spacing:.06em;white-space:nowrap}
    </style>
    <?php
}, 105);
