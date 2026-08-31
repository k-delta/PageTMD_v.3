<?php
/** Final visual adjustments from the August 31 review. */
defined('ABSPATH') || exit;

add_filter('the_content', static function (string $content): string {
    if (!is_page(278) || str_contains($content, 'tmd-about__team-photo')) {
        return $content;
    }

    $src = esc_url(get_stylesheet_directory_uri() . '/assets/img/about/equipo-tmdual.webp');
    $section = '<section class="tmd-about__team-photo" aria-labelledby="tmd-about-team-title">'
        . '<div class="tmd-about__team-photo-copy"><p class="tmd-about__eyebrow">Nuestro equipo</p>'
        . '<h2 id="tmd-about-team-title">Las personas que mueven nuestra operación</h2>'
        . '<p>Un equipo humano y técnico comprometido con el servicio, la seguridad y la continuidad de las operaciones de nuestros clientes.</p></div>'
        . '<figure><img src="' . $src . '" alt="Equipo de TECNIMONTACARGAS DUAL S.A.S." loading="lazy" decoding="async"></figure>'
        . '</section>';

    return $content . $section;
}, 120);

add_action('wp_head', static function (): void {
    if (!is_page(278)) {
        return;
    }
    ?>
    <style id="tmd-final-team-photo">
      .tmd-about__team-photo{display:grid;grid-template-columns:minmax(260px,.75fr) minmax(0,1.25fr);gap:clamp(2rem,6vw,5rem);align-items:center;padding:clamp(3.5rem,7vw,6rem) clamp(1.25rem,5vw,4rem);background:#f4f7fb;border-top:1px solid rgba(18,140,235,.16)}
      .tmd-about__team-photo-copy h2{color:#262e4f;font-size:clamp(2rem,4vw,3.2rem);line-height:1.05}
      .tmd-about__team-photo-copy p:last-child{color:#5e748b;line-height:1.7;text-align:justify}
      .tmd-about__team-photo figure{margin:0;overflow:hidden;border-radius:12px;box-shadow:0 18px 45px rgba(38,46,79,.16)}
      .tmd-about__team-photo img{display:block;width:100%;height:auto}
      @media(max-width:760px){.tmd-about__team-photo{grid-template-columns:1fr}}
    </style>
    <?php
}, 120);

add_action('wp_footer', static function (): void {
    ?>
    <script id="tmd-final-maintenance-menu">
    document.addEventListener('DOMContentLoaded',function(){
      document.querySelectorAll('a').forEach(function(link){
        var text=(link.textContent||'').trim();
        if(text==='Preventivo'){link.textContent='Mantenimiento preventivo';}
        if(text==='Correctivo'){link.textContent='Mantenimiento correctivo';}
      });
    });
    </script>
    <?php
}, 120);
