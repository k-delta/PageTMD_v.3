<?php
defined('ABSPATH') || exit;

/*
 * Corrige los links del mega menú de Equipos.
 * Cada subítem apunta a la página padre de su sección.
 */

add_action('wp_footer', function () {
    if (is_admin()) {
        return;
    }
    ?>
    <script id="tmd-header-equipment-links-fix">
    (function () {
      const map = {
        // Estibadores y Apiladores
        "Estibadores y Apiladores": "/equipos/tipos/estibadores-y-apiladores/",
        "Estibadores manuales": "/equipos/tipos/estibadores-y-apiladores/",
        "Estibadores eléctricos": "/equipos/tipos/estibadores-y-apiladores/",
        "Apiladores eléctricos": "/equipos/tipos/estibadores-y-apiladores/",

        // Reach / Retráctiles
        "Reach / Retráctiles": "/equipos/tipos/reach-retractiles/",
        "Retráctiles de mástil móvil": "/equipos/tipos/reach-retractiles/",
        "Pantógrafo sencillo": "/equipos/tipos/reach-retractiles/",
        "Pantógrafo doble profundidad": "/equipos/tipos/reach-retractiles/",

        // Tomapedidos
        "Tomapedidos": "/equipos/tipos/tomapedidos/",
        "Tomapedidos de alto nivel": "/equipos/tipos/tomapedidos/",

        // Contrabalanceados
        "Contrabalanceados": "/equipos/tipos/contrabalanceados/",
        "Eléctricos de 3 ruedas": "/equipos/tipos/contrabalanceados/",
        "Eléctricos de 4 ruedas": "/equipos/tipos/contrabalanceados/"
      };

      function normalize(value) {
        return String(value || "")
          .normalize("NFD")
          .replace(/[\u0300-\u036f]/g, "")
          .replace(/\s+/g, " ")
          .trim()
          .toLowerCase();
      }

      const normalizedMap = {};

      Object.keys(map).forEach(function (label) {
        normalizedMap[normalize(label)] = map[label];
      });

      function fixEquipmentMenuLinks() {
        const links = document.querySelectorAll(
          "header a, .tmd-mm a, .tmd-mega-menu a, .tmd-mm-panel a, .tmd-mm-dropdown a"
        );

        links.forEach(function (link) {
          const text = normalize(link.textContent);
          const path = normalizedMap[text];

          if (!path) {
            return;
          }

          link.href = new URL(path, window.location.origin).href;
          link.setAttribute("data-tmd-equipment-link-fixed", "1");
        });
      }

      document.addEventListener("DOMContentLoaded", fixEquipmentMenuLinks);
      window.addEventListener("load", fixEquipmentMenuLinks);

      setTimeout(fixEquipmentMenuLinks, 300);
      setTimeout(fixEquipmentMenuLinks, 1000);
      setTimeout(fixEquipmentMenuLinks, 2500);
    })();
    </script>
    <?php
}, 300);
