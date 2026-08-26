<?php
/**
 * Header global TM-Dual con mega menú.
 */

$equipos = [
    [
        'title' => 'Estibadores y Apiladores',
        'url' => home_url('/equipos/tipos/estibadores-y-apiladores/'),
        'image' => 'menu-estibadores-apiladores.webp',
        'items' => [
            ['label' => 'Estibadores manuales', 'url' => home_url('/equipos/tipos/estibadores-manuales/')],
            ['label' => 'Estibadores eléctricos', 'url' => home_url('/equipos/tipos/estibadores-electricos/')],
            ['label' => 'Apiladores eléctricos', 'url' => home_url('/equipos/tipos/apiladores-electricos/')],
        ],
    ],
    [
        'title' => 'Reach / Retráctiles',
        'url' => home_url('/equipos/tipos/reach-retractiles/'),
        'image' => 'menu-reach-retractiles.webp',
        'items' => [
            ['label' => 'Retráctiles de mástil móvil', 'url' => home_url('/equipos/tipos/retractiles-de-mastil-movil/')],
            ['label' => 'Pantógrafo sencillo', 'url' => home_url('/equipos/tipos/pantografo-sencillo/')],
            ['label' => 'Pantógrafo doble profundidad', 'url' => home_url('/equipos/tipos/pantografo-doble-profundidad/')],
        ],
    ],
    [
        'title' => 'Tomapedidos',
        'url' => home_url('/equipos/tipos/tomapedidos/'),
        'image' => 'menu-tomapedidos.webp',
        'items' => [
            ['label' => 'Tomapedidos de alto nivel', 'url' => home_url('/equipos/tipos/tomapedidos-de-alto-nivel/')],
        ],
    ],
    [
        'title' => 'Contrabalanceados',
        'url' => home_url('/equipos/tipos/contrabalanceados/'),
        'image' => 'menu-contrabalanceados.webp',
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
        'image' => 'energy-baterias-plomo.webp',
        'items' => [
            ['label' => 'Baterías inundadas', 'url' => home_url('/energia/baterias/plomo/')],
        ],
    ],
    [
        'title' => 'BMS',
        'url' => home_url('/energia/bms/'),
        'image' => 'energy-bms.webp',
        'items' => [
            ['label' => 'Monitoreo de batería', 'url' => home_url('/energia/bms/#monitoreo-bateria')],
            ['label' => 'Estado y rendimiento', 'url' => home_url('/energia/bms/#estado-rendimiento')],
            ['label' => 'Diagnóstico BMS', 'url' => home_url('/energia/bms/#diagnostico-bms')],
        ],
    ],
    [
        'title' => 'Cargadores',
        'url' => home_url('/energia/cargadores/'),
        'image' => 'energy-cargadores.png',
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

$tmd_account_url       = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/mi-cuenta/');
$tmd_account_logged_in = is_user_logged_in();
$tmd_account_label     = $tmd_account_logged_in ? 'Mi cuenta' : 'Ingresar o registrarse';
?>
<header class="tmd-mm-header" role="banner">
  <div class="tmd-mm-wrap" id="tmdMegaMenu" data-current-panel="">
    <nav class="tmd-mm-navbar" aria-label="Menú principal de Tecnimontacargas">
      <a class="tmd-mm-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Tecnimontacargas">
        <img
          src="https://tecnimontacargas.com/wp-content/uploads/2026/08/logo-blanco.webp"
          alt="Tecnimontacargas"
          width="190"
          height="50"
          decoding="async"
          fetchpriority="high"
        >
      </a>

      <button
        class="tmd-mm-mobile-toggle"
        type="button"
        data-mobile-toggle
        aria-label="Abrir menú"
        aria-controls="tmd-mm-mobile-drawer"
        aria-expanded="false"
      >
        <i class="ti ti-menu-2" aria-hidden="true"></i>
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
            <a class="tmd-mm-img" href="<?php echo esc_url($item['url']); ?>" aria-label="<?php echo esc_attr($item['title']); ?>">
              <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/mega-menu/' . $item['image']); ?>" alt="" decoding="async">
            </a>
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
            <a class="tmd-mm-img" href="<?php echo esc_url($item['url']); ?>" aria-label="<?php echo esc_attr($item['title']); ?>">
              <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/mega-menu/' . $item['image']); ?>" alt="" decoding="async">
            </a>
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
          <a class="tmd-mm-img tmd-mm-img--maintenance" href="<?php echo esc_url(home_url('/mantenimiento/')); ?>" aria-label="Mantenimientos"></a>
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
            <span class="tmd-mm-img tmd-mm-img--<?php echo esc_attr(sanitize_title($col['title'])); ?>"></span>
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

    <div class="tmd-mm-mobile-backdrop" data-mobile-backdrop aria-hidden="true" hidden></div>

    <aside
      class="tmd-mm-mobile-drawer"
      id="tmd-mm-mobile-drawer"
      aria-label="Navegación móvil de Tecnimontacargas"
      aria-hidden="true"
      hidden
      inert
    >
      <nav class="tmd-mm-mobile-nav" aria-label="Menú móvil">
        <a class="tmd-mm-mobile-root-link" href="<?php echo esc_url(home_url('/')); ?>">
          <span>Inicio</span>
        </a>

        <section class="tmd-mm-mobile-section" data-mobile-section="equipos">
          <div class="tmd-mm-mobile-section-row">
            <a class="tmd-mm-mobile-section-link" href="<?php echo esc_url(home_url('/equipos/')); ?>">Equipos</a>
            <button
              class="tmd-mm-mobile-section-toggle"
              type="button"
              data-mobile-section-toggle="equipos"
              data-mobile-section-label="Equipos"
              aria-controls="tmd-mm-mobile-panel-equipos"
              aria-expanded="false"
              aria-label="Mostrar opciones de Equipos"
            >
              <i class="ti ti-chevron-down" aria-hidden="true"></i>
            </button>
          </div>
          <div class="tmd-mm-mobile-submenu" id="tmd-mm-mobile-panel-equipos" hidden>
            <?php foreach ($equipos as $item) : ?>
              <div class="tmd-mm-mobile-group">
                <a class="tmd-mm-mobile-group-title" href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['title']); ?></a>
                <ul>
                  <?php foreach ($item['items'] as $subitem) : ?>
                    <li><a href="<?php echo esc_url($subitem['url']); ?>"><?php echo esc_html($subitem['label']); ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endforeach; ?>
            <div class="tmd-mm-mobile-submenu-actions">
              <a href="<?php echo esc_url(home_url('/equipos/')); ?>">Ver catálogo de equipos</a>
              <a href="<?php echo esc_url(home_url('/encuentra-tu-equipo/')); ?>">Encuentra tu equipo</a>
            </div>
          </div>
        </section>

        <section class="tmd-mm-mobile-section" data-mobile-section="energia">
          <div class="tmd-mm-mobile-section-row">
            <a class="tmd-mm-mobile-section-link" href="<?php echo esc_url(home_url('/energia/')); ?>">Energía</a>
            <button
              class="tmd-mm-mobile-section-toggle"
              type="button"
              data-mobile-section-toggle="energia"
              data-mobile-section-label="Energía"
              aria-controls="tmd-mm-mobile-panel-energia"
              aria-expanded="false"
              aria-label="Mostrar opciones de Energía"
            >
              <i class="ti ti-chevron-down" aria-hidden="true"></i>
            </button>
          </div>
          <div class="tmd-mm-mobile-submenu" id="tmd-mm-mobile-panel-energia" hidden>
            <?php foreach ($energia as $item) : ?>
              <div class="tmd-mm-mobile-group">
                <a class="tmd-mm-mobile-group-title" href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['title']); ?></a>
                <ul>
                  <?php foreach ($item['items'] as $subitem) : ?>
                    <li><a href="<?php echo esc_url($subitem['url']); ?>"><?php echo esc_html($subitem['label']); ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endforeach; ?>
            <div class="tmd-mm-mobile-submenu-actions">
              <a href="<?php echo esc_url(home_url('/energia/')); ?>">Ver catálogo de energía</a>
            </div>
          </div>
        </section>

        <section class="tmd-mm-mobile-section" data-mobile-section="mant">
          <div class="tmd-mm-mobile-section-row">
            <a class="tmd-mm-mobile-section-link" href="<?php echo esc_url(home_url('/mantenimiento/')); ?>">Servicios</a>
            <button
              class="tmd-mm-mobile-section-toggle"
              type="button"
              data-mobile-section-toggle="mant"
              data-mobile-section-label="Servicios"
              aria-controls="tmd-mm-mobile-panel-mant"
              aria-expanded="false"
              aria-label="Mostrar opciones de Servicios"
            >
              <i class="ti ti-chevron-down" aria-hidden="true"></i>
            </button>
          </div>
          <div class="tmd-mm-mobile-submenu" id="tmd-mm-mobile-panel-mant" hidden>
            <div class="tmd-mm-mobile-group">
              <a class="tmd-mm-mobile-group-title" href="<?php echo esc_url(home_url('/mantenimiento/')); ?>">Mantenimientos</a>
              <ul>
                <li><a href="<?php echo esc_url(home_url('/mantenimiento/mantenimiento-preventivo/')); ?>">Preventivo</a></li>
                <li><a href="<?php echo esc_url(home_url('/mantenimiento/mantenimiento-correctivo/')); ?>">Correctivo</a></li>
              </ul>
            </div>
          </div>
        </section>

        <section class="tmd-mm-mobile-section" data-mobile-section="nosotros">
          <div class="tmd-mm-mobile-section-row">
            <a class="tmd-mm-mobile-section-link" href="<?php echo esc_url(home_url('/nosotros/quienes-somos/')); ?>">Nosotros</a>
            <button
              class="tmd-mm-mobile-section-toggle"
              type="button"
              data-mobile-section-toggle="nosotros"
              data-mobile-section-label="Nosotros"
              aria-controls="tmd-mm-mobile-panel-nosotros"
              aria-expanded="false"
              aria-label="Mostrar opciones de Nosotros"
            >
              <i class="ti ti-chevron-down" aria-hidden="true"></i>
            </button>
          </div>
          <div class="tmd-mm-mobile-submenu" id="tmd-mm-mobile-panel-nosotros" hidden>
            <?php foreach ($nosotros as $col) : ?>
              <div class="tmd-mm-mobile-group">
                <a class="tmd-mm-mobile-group-title" href="<?php echo esc_url($col['url']); ?>"><?php echo esc_html($col['title']); ?></a>
                <ul>
                  <?php foreach ($col['items'] as $link) : ?>
                    <li><a href="<?php echo esc_url($link['url']); ?>"><?php echo esc_html($link['label']); ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endforeach; ?>
          </div>
        </section>

        <div class="tmd-mm-mobile-actions">
          <a href="<?php echo esc_url(home_url('/?s=')); ?>">
            <i class="ti ti-search" aria-hidden="true"></i>
            <span>Buscar</span>
          </a>
          <a href="<?php echo esc_url($tmd_account_url); ?>">
            <i class="ti ti-user" aria-hidden="true"></i>
            <span><?php echo esc_html($tmd_account_label); ?></span>
          </a>
        </div>
      </nav>
    </aside>
  </div>
</header>
