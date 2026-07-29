<?php
/**
 * Header global TM-Dual con mega menú.
 */

$equipos = [
    [
        'title' => 'Estibadores y Apiladores',
        'url' => home_url('/equipos/tipos/estibadores-y-apiladores/'),
        'items' => [
            ['label' => 'Estibadores manuales', 'url' => home_url('/equipos/tipos/estibadores-manuales/')],
            ['label' => 'Estibadores eléctricos', 'url' => home_url('/equipos/tipos/estibadores-electricos/')],
            ['label' => 'Apiladores eléctricos', 'url' => home_url('/equipos/tipos/apiladores-electricos/')],
        ],
    ],
    [
        'title' => 'Reach / Retráctiles',
        'url' => home_url('/equipos/tipos/reach-retractiles/'),
        'items' => [
            ['label' => 'Retráctiles de mástil móvil', 'url' => home_url('/equipos/tipos/retractiles-de-mastil-movil/')],
            ['label' => 'Pantógrafo sencillo', 'url' => home_url('/equipos/tipos/pantografo-sencillo/')],
            ['label' => 'Pantógrafo doble profundidad', 'url' => home_url('/equipos/tipos/pantografo-doble-profundidad/')],
        ],
    ],
    [
        'title' => 'Tomapedidos',
        'url' => home_url('/equipos/tipos/tomapedidos/'),
        'items' => [
            ['label' => 'Tomapedidos de alto nivel', 'url' => home_url('/equipos/tipos/tomapedidos-de-alto-nivel/')],
        ],
    ],
    [
        'title' => 'Contrabalanceados',
        'url' => home_url('/equipos/tipos/contrabalanceados/'),
        'items' => [
            ['label' => 'Eléctricos de 3 ruedas', 'url' => home_url('/equipos/tipos/electricos-de-3-ruedas/')],
            ['label' => 'Eléctricos de 4 ruedas', 'url' => home_url('/equipos/tipos/electricos-de-4-ruedas/')],
        ],
    ],
];

$energia = [
    [
        'title' => 'Baterías de plomo',
        'url' => home_url('/energia/baterias/plomo/'),
        'items' => [
            ['label' => 'Baterías inundadas', 'url' => home_url('/energia/baterias/plomo/')],
        ],
    ],
    [
        'title' => 'BMS',
        'url' => home_url('/energia/bms/'),
        'items' => [
            ['label' => 'Monitoreo de batería', 'url' => home_url('/energia/bms/#monitoreo-bateria')],
            ['label' => 'Estado y rendimiento', 'url' => home_url('/energia/bms/#estado-rendimiento')],
            ['label' => 'Diagnóstico BMS', 'url' => home_url('/energia/bms/#diagnostico-bms')],
        ],
    ],
    [
        'title' => 'Cargadores',
        'url' => home_url('/energia/cargadores/'),
        'items' => [
            ['label' => 'Para baterías de plomo-ácido', 'url' => home_url('/energia/cargadores/')],
        ],
    ],
];

$nosotros = [
    [
        'title' => 'Compañía',
        'url' => home_url('/nosotros/quienes-somos/'),
        'items' => [
            ['label' => 'Quiénes somos', 'url' => home_url('/nosotros/quienes-somos/')],
            ['label' => 'Blog', 'url' => home_url('/nosotros/blog/')],
            ['label' => 'Trabaja con nosotros', 'url' => home_url('/nosotros/trabaja-con-nosotros/')],
        ],
    ],
    [
        'title' => 'Socios & Atención',
        'url' => home_url('/nosotros/contacto/'),
        'items' => [
            ['label' => 'Alianzas', 'url' => home_url('/nosotros/alianzas/')],
            ['label' => 'Quiero ser proveedor', 'url' => home_url('/nosotros/proveedores/')],
            ['label' => 'Contacto', 'url' => home_url('/nosotros/contacto/')],
            ['label' => 'PQR', 'url' => home_url('/nosotros/legal/pqr/')],
        ],
    ],
    [
        'title' => 'Legal',
        'url' => home_url('/nosotros/legal/'),
        'items' => [
            ['label' => 'Política de privacidad', 'url' => home_url('/nosotros/legal/politica-de-privacidad/')],
            ['label' => 'Política SG-SST', 'url' => home_url('/nosotros/legal/politica-sg-sst/')],
            ['label' => 'Política de calidad', 'url' => home_url('/nosotros/legal/politica-de-calidad/')],
        ],
    ],
];

$tmd_account_url       = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/mi-cuenta/' );
$tmd_account_logged_in = is_user_logged_in();
$tmd_account_label     = $tmd_account_logged_in ? 'Mi cuenta' : 'Ingresar o registrarse';
?>
<header class="tmd-mm-header" role="banner">
  <div class="tmd-mm-wrap" id="tmdMegaMenu" data-current-panel="">
    <nav class="tmd-mm-navbar" aria-label="Menú principal de Tecnimontacargas">

      <a class="tmd-mm-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Tecnimontacargas">
        <img
          src="https://tecnimontacargas.com/wp-content/uploads/2026/07/logo-blanco.png"
          alt="Tecnimontacargas"
          width="190"
          height="50"
          decoding="async"
          fetchpriority="high"
        >
      </a>

      <button class="tmd-mm-mobile-toggle" type="button" data-mobile-toggle aria-label="Abrir menú">
        <i class="ti ti-menu-2"></i>
      </button>

      <a class="tmd-mm-home tmd-mm-nav-first" href="<?php echo esc_url(home_url('/')); ?>">INICIO</a>

      <button class="tmd-mm-nav-link" type="button" data-tmd-panel="equipos">
        EQUIPOS <span class="chev">▾</span>
      </button>

      <button class="tmd-mm-nav-link" type="button" data-tmd-panel="energia">
        ENERGÍA <span class="chev">▾</span>
      </button>

      <button class="tmd-mm-nav-link" type="button" data-tmd-panel="mant">
        SERVICIOS <span class="chev">▾</span>
      </button>

      <button class="tmd-mm-nav-link" type="button" data-tmd-panel="nosotros">
        NOSOTROS <span class="chev">▾</span>
      </button>

      <div class="tmd-mm-icons">
        <a class="tmd-mm-icon-btn" href="<?php echo esc_url(home_url('/?s=')); ?>" aria-label="Buscar"><i class="ti ti-search"></i></a>
        <a
          class="tmd-mm-icon-btn tmd-mm-account-btn<?php echo $tmd_account_logged_in ? ' is-authenticated' : ''; ?>"
          href="<?php echo esc_url($tmd_account_url); ?>"
          aria-label="<?php echo esc_attr($tmd_account_label); ?>"
          title="<?php echo esc_attr($tmd_account_label); ?>"
        >
          <i class="ti ti-user"></i>
          <?php if ($tmd_account_logged_in) : ?>
            <span class="tmd-mm-user-status" aria-hidden="true"></span>
          <?php endif; ?>
        </a>
      </div>

    </nav>

    <div class="tmd-mm-panel" id="tmd-mm-panel-equipos">
      <div class="tmd-mm-inner tmd-mm-grid-4">
        <?php foreach ($equipos as $item) : ?>
          <div class="tmd-mm-card">
            <a class="tmd-mm-img" href="<?php echo esc_url($item['url']); ?>" aria-label="<?php echo esc_attr($item['title']); ?>"></a>
            <a class="tmd-mm-title" href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['title']); ?></a>
            <ul class="tmd-mm-items">
              <?php foreach ($item['items'] as $subitem) : ?>
                <li><a href="<?php echo esc_url($subitem['url']); ?>"><?php echo esc_html($subitem['label']); ?></a></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="tmd-mm-panel-footer">
        <a class="tmd-mm-footer-link" href="<?php echo esc_url(home_url('/equipos/')); ?>"><i class="ti ti-list-details"></i> Ver catálogo de equipos</a>
        <span class="tmd-mm-footer-sep">|</span>
        <a class="tmd-mm-footer-link" href="<?php echo esc_url(home_url('/encuentra-tu-equipo/')); ?>"><i class="ti ti-adjustments-horizontal"></i> Encuentra tu equipo</a>
      </div>
    </div>

    <div class="tmd-mm-panel" id="tmd-mm-panel-energia">
      <div class="tmd-mm-inner tmd-mm-grid-4">
        <?php foreach ($energia as $item) : ?>
          <div class="tmd-mm-card">
            <a class="tmd-mm-img" href="<?php echo esc_url($item['url']); ?>" aria-label="<?php echo esc_attr($item['title']); ?>"></a>
            <a class="tmd-mm-title" href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['title']); ?></a>
            <?php if (! empty($item['description'])) : ?>
              <p class="tmd-mm-description"><?php echo esc_html($item['description']); ?></p>
            <?php endif; ?>
            <ul class="tmd-mm-items">
              <?php foreach ($item['items'] as $subitem) : ?>
                <li><a href="<?php echo esc_url($subitem['url']); ?>"><?php echo esc_html($subitem['label']); ?></a></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="tmd-mm-panel-footer">
        <a class="tmd-mm-footer-link" href="<?php echo esc_url(home_url('/energia/')); ?>"><i class="ti ti-list-details"></i> Ver catálogo de energía</a>
      </div>
    </div>

          <div class="tmd-mm-panel" id="tmd-mm-panel-mant">
        <div class="tmd-mm-inner tmd-mm-grid-3">
          <div class="tmd-mm-card">
            <a class="tmd-mm-img" href="<?php echo esc_url(home_url('/mantenimiento/')); ?>" aria-label="Mantenimientos"></a>
            <a class="tmd-mm-title" href="<?php echo esc_url(home_url('/mantenimiento/')); ?>">Mantenimientos</a>
            <ul class="tmd-mm-items">
              <li><a href="<?php echo esc_url(home_url('/mantenimiento/mantenimiento-preventivo/')); ?>">Preventivo</a></li>
              <li><a href="<?php echo esc_url(home_url('/mantenimiento/mantenimiento-correctivo/')); ?>">Correctivo</a></li>
            </ul>
          </div>
        </div>
      </div>

    <div class="tmd-mm-panel" id="tmd-mm-panel-nosotros">
      <div class="tmd-mm-inner tmd-mm-grid-3">
        <?php foreach ($nosotros as $col) : ?>
          <div class="tmd-mm-card">
            <span class="tmd-mm-img"></span>
            <a class="tmd-mm-title" href="<?php echo esc_url($col['url']); ?>"><?php echo esc_html($col['title']); ?></a>
            <ul class="tmd-mm-items">
              <?php foreach ($col['items'] as $link) : ?>
                <li><a href="<?php echo esc_url($link['url']); ?>"><?php echo esc_html($link['label']); ?></a></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="tmd-mm-panel-footer">
        <a class="tmd-mm-footer-link" href="<?php echo esc_url(home_url('/nosotros/contacto/')); ?>"><i class="ti ti-headset"></i> Contacto directo</a>
        <span class="tmd-mm-footer-sep">|</span>
        <a class="tmd-mm-footer-link" href="<?php echo esc_url(home_url('/nosotros/trabaja-con-nosotros/')); ?>"><i class="ti ti-briefcase"></i> Trabaja con nosotros</a>
      </div>
    </div>

  </div>
</header>
