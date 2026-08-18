# Repository Map

## Propósito

Este documento describe la arquitectura, los módulos principales, las fuentes canónicas y los flujos de datos de Tecnimontacargas.

Las instrucciones obligatorias están en `/AGENTS.md`.

## Vista general

```text
Tecnimontacargas WordPress
        │
        │ HTTPS
        ▼
Firebase Cloud Functions
        │
        ▼
Firestore / Inventario
```

### WordPress

Responsable de:

- Sitio público y contenido comercial.
- Páginas educativas.
- Formularios y cuenta de usuario.
- SEO y navegación.
- Presentación del inventario.
- Quiz de selección de equipos.

### Inventario/Firebase

Responsable de:

- Fuente real de equipos.
- Disponibilidad y estado.
- Marcas, modelos e imágenes.
- Selección de equipos destacados.
- Cloud Functions consumidas por WordPress.

WordPress consume estos datos, pero no es su fuente canónica.

## Estructura principal

```text
project/
├── wp-content/
│   ├── themes/
│   │   └── blocksy-child/
│   └── plugins/
│       ├── tm-chatbot-fase1/
│       ├── tm-equipos-destacados-v2/
│       ├── tm-popup-bienvenida/
│       └── tm-quiz-equipo-ideal/
├── scripts/
│   └── sync-production.sh
├── production-snapshot/
├── docs/
├── .agents/
├── .codex/
└── AGENTS.md
```

## Tema activo

Ruta: `wp-content/themes/blocksy-child/`

```text
blocksy-child/
├── functions.php
├── style.css
├── theme.json
├── inc/
│   ├── tmd-inventory-api.php
│   ├── tmd-seo.php
│   ├── tmd-account.php
│   └── tmd-equipment-type-guides.php
├── assets/
│   ├── js/tmd-inventory-api.js
│   └── css/tmd-inventory-api.css
└── template-parts/
    ├── tmd-header.php
    └── tmd-footer.php
```

| Archivo | Responsabilidad |
|---|---|
| `inc/tmd-inventory-api.php` | Integración WordPress con Cloud Functions de inventario |
| `assets/js/tmd-inventory-api.js` | Filtros, paginación e interacción en navegador |
| `assets/css/tmd-inventory-api.css` | Presentación visual del inventario |
| `inc/tmd-seo.php` | Ajustes SEO propios complementarios |
| `inc/tmd-account.php` | Personalizaciones de cuenta y WooCommerce |
| `inc/tmd-equipment-type-guides.php` | Guías educativas de equipos |
| `template-parts/tmd-header.php` | Header y navegación principal |
| `template-parts/tmd-footer.php` | Footer |
| `functions.php` | Registro y coordinación de módulos del tema |

## Plugins propios

### `tm-chatbot-fase1`

Chatbot público e interfaz de contacto inicial.

### `tm-equipos-destacados-v2`

Consulta y presentación de equipos destacados provenientes de Inventario/Firebase.

### `tm-popup-bienvenida`

Popup de bienvenida. Su activación es una decisión operativa.

### `tm-quiz-equipo-ideal`

Responsable de:

- Preguntas y navegación del quiz.
- Evaluación de respuestas.
- Selección de recomendaciones.
- Shortcode.
- Presentación de resultados.
- Integración con formularios o solicitudes.

El shortcode debe registrarse desde este plugin.

## Flujos principales

### Inventario general

```text
Firestore
   ↓
listarEquiposWordpress
   ↓
tmd-inventory-api.php
   ├── hasta 12 tarjetas HTML iniciales
   └── modelo público JSON del catálogo
        ↓
   tmd-inventory-api.js
        ├── filtros
        ├── paginación
        └── solo tarjetas de la página visible en el DOM
```

### Equipos destacados

```text
Firestore
   ↓
listarEquiposDestacadosWordpress
   ↓
tm-equipos-destacados-v2
   ↓
Componente público
```

### Quiz

```text
Usuario
   ↓
Shortcode
   ↓
tm-quiz-equipo-ideal
   ├── captura respuestas
   ├── evalúa restricciones
   ├── determina categoría
   └── muestra recomendación
        ↓
Formulario o solicitud comercial
```

### Formularios y correo

```text
Formulario
   ↓
Contact Form 7
   ├── Flamingo
   └── wp_mail()
        ↓
Proveedor configurado
        ↓
Bandeja real
```

### SEO

```text
Contenido WordPress
   ↓
Rank Math
   ├── title
   ├── description
   ├── canonical
   ├── robots
   ├── Open Graph
   ├── schema
   └── sitemap
```

## Infraestructura

```text
VPS
└── Docker Compose
    ├── OpenLiteSpeed + WordPress
    ├── MariaDB
    └── phpMyAdmin restringido
```

Los procedimientos operativos están en `docs/runbooks/`.

## Fuentes canónicas

| Información | Fuente canónica |
|---|---|
| Código | Git |
| Tema público | `blocksy-child` |
| Quiz | `tm-quiz-equipo-ideal` |
| Equipos destacados | `tm-equipos-destacados-v2` |
| Inventario | Inventario/Firebase |
| Contenido WordPress | MariaDB |
| Multimedia | WordPress uploads |
| SEO | Rank Math y HTML público verificado |
| Infraestructura | Docker Compose productivo |
| Reglas de agentes | `AGENTS.md` |
| Tareas concretas | `docs/specs/` |
| Estado temporal | `docs/status/CURRENT_STATE.md` |

## Elementos no canónicos

- `tmd-site-kit/`: histórico e inactivo.
- `.codex-tmp/`: temporal.
- `production-snapshot/`: auditoría parcial; no sustituye base de datos, uploads ni backups.

## Límites arquitectónicos

- WordPress presenta inventario; Firebase lo gobierna.
- Rank Math gobierna metadatos SEO.
- Los plugins propios encapsulan funcionalidades independientes.
- El tema hijo contiene presentación e integraciones generales.
- El tema padre y WordPress core no se modifican.
- Los datos persistentes de WordPress viven en MariaDB.
- Los snapshots son mecanismos de auditoría, no fuentes primarias.
