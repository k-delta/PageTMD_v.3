# DOCUMENTO DE REQUERIMIENTOS — TECNI MONTACARGAS
## Versión 6.6 — Documento Completo y Consolidado

**Fecha base:** 18 de febrero de 2026
**Actualizado:** 25 de febrero de 2026
**Proyecto:** Sitio Web Corporativo + E-commerce + Servicios
**Marca:** Tecni Montacargas TM-Dual
---

## ÍNDICE

1. [Resumen Ejecutivo](#1-resumen-ejecutivo)
2. [Especificaciones de Marca](#2-especificaciones-de-marca)
3. [Arquitectura de Información](#3-arquitectura-de-información)
4. [Épicas y Historias de Usuario](#4-épicas-y-historias-de-usuario)
5. [Integraciones Técnicas](#5-integraciones-técnicas)
6. [Requisitos No Funcionales](#6-requisitos-no-funcionales)
7. [Plan de Implementación](#7-plan-de-implementación)
8. [Checklist de Contenido](#8-checklist-de-contenido)
9. [Anexos](#9-anexos)

---

## 1. RESUMEN EJECUTIVO

### Alcance del Proyecto

Desarrollo de sitio web integral para Tecni Montacargas que incluye:

- Hero principal con badge de experiencia, H1 impactante, dos CTAs y logos de fabricantes
- Catálogo de equipos unificado (Compra y Alquiler) con mega menú por subtipo y filtros jerárquicos avanzados
- Sección "Equipos Destacados" con carrusel de 3 tarjetas, características técnicas visibles y sin precios
- Barra lateral derecha fija (sticky) con íconos de contacto rápido en todas las páginas
- Lógica de precio visible / botón Cotizar según configuración por equipo
- Tienda online de repuestos con pasarela de pagos (MercadoPago + PSE)
- Botón de agendamiento con enlace de redirección (sin integración técnica con Microsoft 365)
- Chatbot con IA para atención 24/7
- Quiz interactivo de 6 pasos con trazabilidad de necesidades
- Biblioteca educativa de 8 tipos de equipos (páginas `/equipos/que-es/[tipo]`)
- Sección de servicios técnicos especializados
- Directorio de contactos con redirección WhatsApp
- Portal de alianzas y políticas corporativas

### Diferenciadores Clave

| Característica | Beneficio |
|---|---|
| Hero con badge de experiencia y logos de marcas | Credibilidad inmediata — el visitante sabe con qué marcas trabaja TM antes de hacer scroll |
| Equipos destacados en carrusel (sin precios) | Muestra disponibilidad real sin comprometer estrategia comercial; características técnicas orientan al visitante |
| Barra lateral sticky de contacto rápido | El usuario puede contactar desde cualquier punto de la página sin buscar el botón de contacto |
| Sección de inicio con grilla visual de subtipos | Acceso directo al catálogo filtrado desde el home, estilo referencia ferretería |
| Carrusel "Historias de Éxito" en home | Prueba social con foto + testimonio + logo de empresa cliente — genera confianza inmediata |
| Blog con 6 categorías y preview en home | Posiciona a TM como referente del sector, mejora SEO y mantiene al cliente informado |
| Mega menú con 8 subtipos de equipos | Navegación visual e intuitiva |
| Toggle Comprar / Alquilar en un solo catálogo | Experiencia unificada, menos páginas que mantener |
| Quiz de 6 pasos con intención de compra/alquiler | El CTA (call to action, botones de llamada a la acción llamativos) final redirige con filtro exacto ya aplicado |
| Precio visible o botón Cotizar por equipo | Flexibilidad comercial sin tocar código |
| Biblioteca de tipos de equipo | Educa al usuario no técnico y mejora SEO orgánico |
| Agendamiento por enlace de redirección | Sin desarrollo técnico — el cliente configura su sistema de citas de forma independiente |
| Directorio WhatsApp | Contacto directo humano, genera confianza |
| Control total de WordPress entregado al cliente | El equipo técnico puede crear y gestionar contenido libremente |

---

## 2. ESPECIFICACIONES DE MARCA

### Identidad Visual — Manual TM-Dual

| Elemento | Especificación | Uso |
|---|---|---|
| Tipografía | Work Sans (Google Fonts) | Todo el sitio |
| Títulos H1 | Bold, 50pt, tracking -10, interlineado 50pt | Headers de página |
| Títulos H2 | Semibold, 32pt, tracking -10 | Secciones |
| Subtítulos | Light, 20pt, tracking 0 | Descripciones |
| Cuerpo | Light, 14-16pt, interlineado +2pt | Textos generales |
| Botones | Regular, 18pt, centrado | CTAs |

### Paleta de Colores

| Color | HEX | RGB | Uso | Proporción |
|---|---|---|---|---|
| Azul Empresarial | `#128CEB` | 18, 140, 235 | Primario, botones, enlaces | 35% |
| Azul Americano | `#262E4F` | 38, 46, 79 | Headers, fondos oscuros | 35% |
| Amarillo Imperial | `#FFC33C` | 255, 195, 60 | CTAs, acentos, badges | 20% |
| Azul Metálico | `#5E748B` | 94, 116, 139 | Secundario, iconos | 5% |
| Platino | `#E6E6E6` | 230, 230, 230 | Fondos, cards | 4% |
| Aceituna Negra | `#3C3C3C` | 60, 60, 60 | Textos, footer | 1% |

### Logo y Favicon

| Uso | Especificación |
|---|---|
| Logo header | Versión horizontal, mínimo 180×47px |
| Logo footer | Versión horizontal, 245×65px |
| Favicon | Isotipo azul `#128CEB`, 32×32px |
| App icon | Isotipo azul en fondo blanco redondeado |

---

## 3. ARQUITECTURA DE INFORMACIÓN

### Mapa de Sitio

```
├── INICIO
│   ├── Hero principal (HER-001)
│   │   ├── Badge "Más de 20 años en Colombia"
│   │   ├── H1 + subtítulo con marcas
│   │   ├── CTA principal + CTA secundario
│   │   └── Logos de fabricantes (Toyota, Hyster, Yale, Crown, JLG, Genie)
│   ├── Sección: Grilla de subtipos de equipo  ← VER §ÉPICA-HOME
│   ├── Sección: Equipos Destacados — Carrusel (EQD-001)
│   ├── Sección: Historias de Éxito (carrusel testimonial)  ← VER HOME-002
│   ├── Sección: Últimas Noticias / Blog preview (3 artículos)  ← VER BLG-004
│   ├── Servicios técnicos preview
│   └── CTA (call to action, botones de llamada a la acción llamativos) Contacto
│
├── COMPONENTE GLOBAL: Barra Lateral Izquierda Sticky (SID-001)  ← lado izquierdo, solo desktop/tablet
│   ├── Ícono Ubicación
│   ├── Ícono Chat / WhatsApp
│   ├── Ícono Correo
│   └── Ícono LinkedIn
│
├── COMPONENTE GLOBAL: Chat IA — Widget Flotante (IA-001)  ← esquina inferior derecha, todas las vistas
│
├── COMPONENTE GLOBAL: Popup de Bienvenida (POP-001)  ← solo home, solo visitantes nuevos, 1 vez
│
├── EQUIPOS  (Compra y Alquiler unificados)  ← 7 subtipos desde v6.0
│   ├── MEGA MENÚ  (hover/clic en «Equipos»)
│   │   [7 tarjetas visuales por subtipo → /equipos/que-es/[subtipo]]
│   ├── Listado con filtros jerárquicos (8 niveles)
│   ├── Ficha técnica de equipo
│   ├── Condiciones de alquiler
│   ├── Comparador de equipos
│   ├── Formulario de cotización / solicitud de alquiler
│   └── Biblioteca: Tipos de Equipo  (7 páginas /equipos/que-es/[tipo])
│       ├── /equipos/que-es/contrabalanceadas
│       ├── /equipos/que-es/retractiles
│       ├── /equipos/que-es/apiladores
│       ├── /equipos/que-es/transpaletas
│       ├── /equipos/que-es/recogepedidos
│       ├── /equipos/que-es/estibadores-manuales
│       └── /equipos/que-es/manlift
│
├── ENERGÍA  ← NUEVO v6.6 (mismo nivel que Equipos y Repuestos)
│   ├── BATERÍAS
│   │   ├── Baterías de plomo (plomo-ácido tubular, gel, AGM)
│   │   ├── Baterías de litio (Li-Ion)
│   │   ├── Ficha técnica de batería
│   │   └── Formulario de cotización / solicitud
│   └── CARGADORES
│       ├── Cargadores Renma
│       ├── Cargadores PowerAnderson
│       ├── Ficha técnica de cargador
│       └── Formulario de cotización / solicitud
│
├── REPUESTOS  (Tienda Online)
│   ├── Categorías técnicas (12 categorías)
│   ├── Listado con filtros
│   ├── Ficha de producto
│   ├── Carrito de compras
│   └── Checkout (MercadoPago + PSE)
│
├── SERVICIOS TÉCNICOS
│
├── QUIZ  "Encuentra tu equipo ideal"
│
├── NOSOTROS  ← agrupa Historia, Blog, Trabaja con Nosotros y Contacto (v6.0)
│   ├── Historia (narrativa visual minimalista — NOS-001)
│   ├── Galería de equipo/trabajadores
│   ├── Valores y pilares
│   ├── Alianzas comerciales (Barbillón)
│   ├── Políticas (SG-SST, Calidad, etc.)
│   ├── Directorio de contactos WhatsApp
│   ├── Blog / Novedades  ← movido bajo Nosotros
│   │   ├── /nosotros/blog — Listado con filtros por categoría
│   │   └── /nosotros/blog/[slug] — Artículo individual
│   ├── Trabaja con Nosotros  ← movido bajo Nosotros
│   │   ├── Landing editorial (4 bloques)
│   │   ├── Vacantes
│   │   └── Formulario de postulación
│   └── Contacto  ← movido bajo Nosotros
│
└── LEGAL
```

> **🚫 INTRANET — Excluida del alcance (v5.2)**
> Decisión del cliente: no se desarrollará intranet en este proyecto. Si en el futuro se requiere acceso a un sistema interno, se implementará mediante un **enlace de redirección simple** hacia el sistema externo existente.

---

## 4. ÉPICAS Y HISTORIAS DE USUARIO

---

### ÉPICA HOME: SECCIÓN DE INICIO

---

#### HER-001: Hero Principal del Home  ⭐ NUEVO v5.9
**Prioridad:** Alta | **Estimado:** 5 pts

> *Como visitante que llega al sitio por primera vez, quiero ver de inmediato quién es Tecni Montacargas, qué ofrece y con qué marcas trabaja, para generar confianza antes de explorar el catálogo.*

##### Referencia Visual

La sección replica el diseño del hero capturado por el cliente: fondo azul oscuro `#262E4F`, badge de experiencia en la parte superior, título grande en blanco, subtítulo con listado de marcas, dos botones de acción contrastantes y logos de fabricantes como fila inferior.

##### Estructura del Hero

```
┌──────────────────────────────────────────────────────────────────┐  ← fondo #262E4F
│                                                                  │
│           [ Más de 20 años de experiencia en Colombia ]          │
│                        ← badge/pill superior                     │
│                                                                  │
│        Soluciones en Montacargas                                 │
│        y Plataformas Elevadoras                                  │
│                                                                  │
│   Venta, alquiler y servicio técnico especializado               │
│   para la industria colombiana.                                  │
│   Toyota, Hyster, Yale, Crown, JLG y Genie.                      │
│                                                                  │
│   [ Encuentra tu equipo → ]    [ Ver catálogo ]                  │
│    ← botón amarillo #FFC33C    ← botón outline blanco            │
│                                                                  │
│   Toyota  Hyster  Yale  Crown  JLG  Genie                        │
│   ← logos de fabricantes en fila, fondo semitransparente        │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

##### Elementos del Hero

| Elemento | Especificación |
|---|---|
| Fondo | `#262E4F` (Azul Americano) — sólido o con textura/gradiente sutil |
| Badge superior | Texto: "Más de 20 años de experiencia en Colombia" · Fondo `rgba(255,255,255,0.1)` · Borde 1px `rgba(255,255,255,0.3)` · border-radius 20px · Work Sans Regular 13px · color blanco |
| Título H1 | "Soluciones en Montacargas y Plataformas Elevadoras" · Work Sans Bold · 48-56px desktop / 32px móvil · color `#FFFFFF` |
| Subtítulo | "Venta, alquiler y servicio técnico especializado para la industria colombiana. Toyota, Hyster, Yale, Crown, JLG y Genie." · Work Sans Light · 18px · color `rgba(255,255,255,0.80)` · max-width 560px |
| CTA principal | `[ Encuentra tu equipo → ]` · fondo `#FFC33C` · color `#262E4F` · Work Sans Bold 16px · border-radius 6px · padding 14px 28px · hover: brighten |
| CTA secundario | `[ Ver catálogo ]` · fondo transparente · borde 2px `rgba(255,255,255,0.6)` · color blanco · mismo tamaño · hover: fondo `rgba(255,255,255,0.1)` |
| Fila de logos | Toyota, Hyster, Yale, Crown, JLG, Genie · logos en blanco o versión monocromo claro · separados por líneas verticales `rgba(255,255,255,0.2)` · fondo `rgba(0,0,0,0.15)` · padding 16px · ancho completo |
| Altura mínima | 540px desktop / auto móvil con padding 80px vertical |
| Imagen de fondo | Opcional: foto de equipo real como overlay semitransparente (opacity 0.15-0.2) en lado derecho |

##### Texto Definitivo (placeholder — ajustar con el cliente)

- **Badge:** `Más de 20 años de experiencia en Colombia`
- **H1:** `Soluciones en Montacargas y Plataformas Elevadoras`
- **Subtítulo:** `Venta, alquiler y servicio técnico especializado para la industria colombiana. Toyota, Hyster, Yale, Crown, JLG y Genie.`
- **CTA 1:** `Encuentra tu equipo →` → navega a `/encuentra-tu-equipo` (Quiz)
- **CTA 2:** `Ver catálogo` → navega a `/equipos`

##### Criterios de Aceptación

- [ ] El hero ocupa el viewport completo o al menos 540px de alto en desktop
- [ ] El badge aparece sobre el título con estilo pill semitransparente
- [ ] Los dos botones están en la misma fila en desktop, apilados en móvil
- [ ] El CTA principal (`Encuentra tu equipo`) usa `#FFC33C` con texto oscuro
- [ ] El CTA secundario usa outline blanco visible sobre el fondo oscuro
- [ ] Los logos de fabricantes aparecen en una franja inferior del hero
- [ ] Todo el texto es legible (contraste AA mínimo) sobre el fondo `#262E4F`
- [ ] El hero es completamente responsive y se ve correctamente en iOS Safari y Android Chrome

---

#### EQD-001: Sección "Equipos Destacados" — Carrusel sin Precios  ⭐ NUEVO v5.9
**Prioridad:** Alta | **Estimado:** 5 pts

> *Como visitante del home, quiero ver una selección de equipos representativos con sus características técnicas principales, para tener una idea de la oferta sin necesitar ir al catálogo completo.*

##### Definición del Comportamiento

La sección muestra los equipos que el administrador marca como "destacados" en WordPress. En el home se presentan **únicamente 3 tarjetas** en formato carrusel horizontal. Las tarjetas son de altura moderada (compactas, no altas). **No se muestra el precio** en esta sección — en su lugar se muestran las características técnicas como chips (energía, capacidad, altura).

##### Wireframe Desktop — Carrusel de 3 Tarjetas

Cada tarjeta tiene layout **horizontal**: imagen a la izquierda, información del equipo a la derecha. Las 3 tarjetas se muestran en fila.

```
┌──────────────────────────────────────────────────────────────────────┐
│                      EQUIPOS DESTACADOS                              │
│            Selección de equipos disponibles en stock                 │
│                                              [ Ver todos → ]         │
├────────────────────────┬────────────────────────┬────────────────────┤
│ ┌──────────┬─────────┐ │ ┌──────────┬─────────┐ │ ┌──────────┬──────┐│
│ │          │[Usado]  │ │ │          │[Nuevo]  │ │ │          │[Rec.]││
│ │  [IMG    │ Toyota  │ │ │  [IMG    │ Yale    │ │ │  [IMG    │Crown ││
│ │  EQUIPO] │ 8FGU25  │ │ │  EQUIPO] │ ERC050  │ │ │  EQUIPO] │RR 572││
│ │          │ 2022    │ │ │          │ 2023    │ │ │          │2022  ││
│ │          │         │ │ │          │         │ │ │          │      ││
│ │          │[GLP]    │ │ │          │[Eléct.] │ │ │          │[Eléct││
│ │          │[2.5 ton]│ │ │          │[2.5 ton]│ │ │          │[1.4t]││
│ │          │[4.7 m]  │ │ │          │[5.0 m]  │ │ │          │[10.5m││
│ │          │         │ │ │          │         │ │ │          │      ││
│ │          │[Ver →]  │ │ │          │[Ver →]  │ │ │          │[Ver→]││
│ └──────────┴─────────┘ │ └──────────┴─────────┘ │ └──────────┴──────┘│
├────────────────────────┴────────────────────────┴────────────────────┤
│                   ●  ○  ○  ○    ← indicadores del carrusel           │
└──────────────────────────────────────────────────────────────────────┘
```

##### Estructura de Cada Tarjeta (layout horizontal)

| Zona | Elemento | Especificación |
|---|---|---|
| **Izquierda** | Imagen del equipo | 45% del ancho de la tarjeta · aspect-ratio 4/3 · object-fit contain · fondo `#F5F7FA` · border-radius 8px 0 0 8px |
| **Derecha** | Badge condición | Esquina superior derecha · "Usado" / "Nuevo" / "Reacondicionado" · Work Sans Bold 11px · colores: Usado `#5E748B`, Nuevo `#1E7E34`, Reacondicionado `#E65C00` |
| **Derecha** | Marca + Modelo | Work Sans SemiBold 15px · `#262E4F` · primera línea |
| **Derecha** | Año | Work Sans Light 13px · `#5E748B` · debajo del modelo |
| **Derecha** | Chips de características | Energía (GLP / Eléctrico / Diésel), Capacidad (ej: 2.5 ton), Altura (ej: 4.7 m) · pills con fondo `#EEF4FB`, color `#128CEB`, font 12px, border-radius 4px |
| **Derecha** | **Precio** | ❌ **NO SE MUESTRA** en la sección de equipos destacados del home |
| **Derecha** | CTA | `[ Ver ficha → ]` · fondo `#128CEB` · texto blanco · ancho completo del panel derecho · border-radius 6px · Work Sans Regular 14px · posición al fondo del panel derecho |

##### Wireframe Móvil — Tarjeta individual

En móvil la tarjeta mantiene el layout horizontal: imagen 40% ancho izquierda, información 60% ancho derecha.

```
┌────────────────────────────────────────┐
│ ┌──────────┬─────────────────────────┐ │
│ │          │ [Usado]                 │ │
│ │  [IMG]   │ Toyota 8FGU25           │ │
│ │          │ 2022                    │ │
│ │          │ [GLP] [2.5 ton] [4.7 m] │ │
│ │          │ [ Ver ficha → ]         │ │
│ └──────────┴─────────────────────────┘ │
└────────────────────────────────────────┘
```

##### Comportamiento del Carrusel

| Aspecto | Especificación |
|---|---|
| Visibles simultáneamente | 3 en desktop / 2 en tablet / 1 en móvil |
| Navegación | Indicadores (dots) debajo de las tarjetas |
| Flechas | Opcionales, sutil, fuera de las tarjetas |
| Autoplay | No |
| Swipe táctil | Sí en móvil |
| Cantidad de equipos | Entre 4 y 8 marcados como "destacados" — el carrusel los rota |
| Link "Ver todos" | Posición: esquina superior derecha de la sección · navega a `/equipos` |

##### Altura de Tarjeta

La tarjeta debe ser compacta. Altura total estimada: ~340px desktop. No debe generar scroll horizontal visible ni ser tan alta como la referencia de 4 columnas del catálogo completo.

##### Gestión desde WordPress

- El administrador marca cualquier equipo como "Destacado" con un checkbox en su ficha
- Los equipos marcados aparecen automáticamente en esta sección
- El orden se controla con el campo "Orden destacado"
- No requiere tocar código

##### Criterios de Aceptación

- [ ] La sección muestra exactamente 3 tarjetas visibles en desktop
- [ ] **No se muestra precio en ninguna tarjeta de esta sección**
- [ ] Se muestran los chips de características: energía, capacidad, altura
- [ ] El badge de condición (Usado/Nuevo/Reacondicionado) es visible sobre la imagen
- [ ] El botón "Ver ficha →" navega a la ficha técnica del equipo
- [ ] El link "Ver todos" navega al catálogo completo `/equipos`
- [ ] El carrusel funciona con swipe táctil en móvil
- [ ] Las tarjetas son compactas (no excesivamente altas)
- [ ] El administrador puede marcar/desmarcar equipos como destacados desde WordPress sin código

##### Posición en el Home

```
[HERO — HER-001]
[GRILLA DE SUBTIPOS — HOME-001]
[EQUIPOS DESTACADOS — EQD-001]   ← aquí
[HISTORIAS DE ÉXITO — HOME-002]
[SERVICIOS TÉCNICOS PREVIEW]
[ÚLTIMAS NOTICIAS — BLG-004]
[CTA CONTACTO / QUIZ]
[FOOTER con Contact Center Mini]
```

---

#### SID-001: Barra Lateral Izquierda Fija (Sticky) — Iconos de Contacto Rápido  ⭐ ACTUALIZADO v6.4
**Prioridad:** Media | **Estimado:** 3 pts

> *Como visitante en cualquier punto de la página, quiero poder contactar a Tecni Montacargas en un solo clic sin tener que hacer scroll hasta el footer o buscar la página de contacto.*

##### Descripción

Un componente flotante fijo en el **lado izquierdo** de la pantalla (`position: fixed`), centrado verticalmente, visible en desktop y tablet. Contiene **4 íconos** apilados verticalmente: ubicación, chat/WhatsApp, correo electrónico y LinkedIn. **No aparece en móvil.**

##### Wireframe

```
┌───────┐
│  📍   │  ← Ubicación / Google Maps
├───────┤
│  💬   │  ← Chat / WhatsApp
├───────┤
│  ✉️   │  ← Correo electrónico
├───────┤
│  in   │  ← LinkedIn
└───────┘
   │
left: 0, centrado verticalmente
```

##### Acciones de Cada Ícono

| Ícono | Acción | Destino |
|---|---|---|
| 📍 Ubicación | Abre Google Maps con la dirección de TM | `https://maps.google.com/?q=[dirección]` · target: `_blank` |
| 💬 Chat / WhatsApp | Abre WhatsApp con mensaje predefinido | `https://wa.me/57[número]?text=Hola,...` · target: `_blank` |
| ✉️ Correo | Abre cliente de correo o navega a `/nosotros/contacto` | `mailto:info@tecnimontacargas.com` o enlace a `/nosotros/contacto` |
| **in LinkedIn** | Abre la página de empresa de TM en LinkedIn | URL de LinkedIn de TM · target: `_blank` |

##### Especificaciones de Diseño

```
Contenedor:       position: fixed, left: 0, top: 50%, transform: translateY(-50%)
                  z-index: 999, border-radius: 0 8px 8px 0 (redondeado solo en lado derecho)
Fondo íconos:     #262E4F (Azul Americano)
Tamaño bloque:    48px × 48px por ícono
Íconos:           Color #FFFFFF, tamaño 20px, centrados
Separador:        Línea 1px rgba(255,255,255,0.15) entre cada ícono
Hover por ícono:  Fondo #128CEB (Azul Empresarial), transición 200ms
Tooltip:          Aparece a la DERECHA del ícono al hacer hover
                  (ej: "Escríbenos por WhatsApp")
Sombra:           box-shadow 4px 0 16px rgba(0,0,0,0.2)
```

##### Comportamiento Responsive

| Dispositivo | Comportamiento |
|---|---|
| Desktop (>1024px) | Visible · posición fija lado **izquierdo** · centrado verticalmente |
| Tablet (768-1024px) | Visible · misma posición · íconos ligeramente más pequeños (42px) |
| Móvil (<768px) | **Oculto completamente** — `display: none` |

##### Criterios de Aceptación

- [ ] El componente aparece en el **lado izquierdo** en desktop y tablet
- [ ] **No aparece en ninguna forma en móvil** (`display: none` en breakpoint < 768px)
- [ ] El ícono de WhatsApp abre la app con número y mensaje predefinido
- [ ] El ícono de ubicación abre Google Maps en nueva pestaña
- [ ] El ícono de correo abre el cliente de email o redirige a `/nosotros/contacto`
- [ ] El ícono de LinkedIn abre la página de empresa de TM en LinkedIn en nueva pestaña
- [ ] El hover cambia el fondo a `#128CEB` con tooltip a la **derecha** del ícono
- [ ] El componente no interfiere con el contenido ni bloquea CTAs importantes
- [ ] Los valores (número de WhatsApp, dirección, correo, URL de LinkedIn) son configurables desde WordPress sin código

---

#### HOME-001: Grilla Visual de Subtipos de Equipo en el Home
**Prioridad:** Alta | **Estimado:** 8 pts

> *Como visitante que llega al home, quiero ver de inmediato los tipos de equipo disponibles con imagen representativa, para hacer clic directamente en el que me interesa y llegar al catálogo ya filtrado.*

> ⚠️ **v6.0:** La grilla pasa de 8 a **7 subtipos** al eliminar "Montacargas 3 Ruedas". El layout en desktop se ajusta a **4 + 3** (primera fila completa, segunda fila con 3 celdas centradas) o puede reorganizarse como el cliente prefiera.

##### Las 7 celdas de la grilla

| # | Subtipo | Ítems de la lista (ejemplos visibles) | URL destino |
|---|---|---|---|
| 1 | Contrabalanceadas | › 3 ruedas  › 4 ruedas  › Alta capacidad  › Contenedores | `/equipos?tipo=contrabalanceadas` |
| 2 | Retráctiles | › Reach simple  › Doble reach  › Pasillo angosto  › VNA | `/equipos?tipo=retractiles` |
| 3 | Apiladores | › Manual  › Semieléctrico  › Eléctrico montado  › Compacto | `/equipos?tipo=apiladores` |
| 4 | Transpaletas | › Manual  › Eléctrica  › Bajo perfil  › Con báscula | `/equipos?tipo=transpaletas` |
| 5 | Recogepedidos | › Bajo nivel  › Medio nivel  › Alto nivel  › Combinado | `/equipos?tipo=recogepedidos` |
| 6 | Estibadores Manuales | › Hidráulico manual  › Tijera  › Sin motor  › Bajo costo | `/equipos?tipo=estibadores-manuales` |
| 7 | Manlift / Plataformas | › Tijera eléctrica  › Brazo articulado  › Telescópico  › Todo terreno | `/equipos?tipo=manlift` |

##### Wireframe Desktop (fila 1: 4 cols · fila 2: 3 cols centradas)

```
┌─────────────────────────────────────────────────────────────────────┐
│                    ENCUENTRA TU EQUIPO                              │
│              Haz clic en el tipo que necesitas                      │
├─────────────────┬─────────────────┬─────────────────┬──────────────┤
│  [FOTO]         │  [FOTO]         │  [FOTO]         │  [FOTO]      │
│─────────────────│─────────────────│─────────────────│──────────────│
│ Contrabalance.  │ Retráctiles     │ Apiladores      │ Transpaletas │
│ ══════════════  │ ══════════════  │ ══════════════  │ ════════════ │
│ › 3 ruedas      │ › Reach simple  │ › Manual        │ › Manual     │
│ › 4 ruedas      │ › Doble reach   │ › Semieléctrico │ › Eléctrica  │
│ › Alta cap.     │ › Pasillo ang.  │ › Elec. montado │ › Bajo perfil│
│ › Contenedores  │ › VNA           │ › Compacto      │ › Con báscula│
├─────────────────┼─────────────────┼─────────────────┴──────────────┤
│  [FOTO]         │  [FOTO]         │  [FOTO]                         │
│─────────────────│─────────────────│─────────────────                │
│ Recogepedidos   │ Estibadores     │ Manlift / Plataformas           │
│ ══════════════  │ ══════════════  │ ════════════════════════         │
│ › Bajo nivel    │ › Hidráulico    │ › Tijera eléctrica              │
│ › Medio nivel   │ › Tijera        │ › Brazo articulado              │
│ › Alto nivel    │ › Sin motor     │ › Telescópico                   │
│ › Combinado     │ › Bajo costo    │ › Todo terreno                  │
└─────────────────┴─────────────────┴─────────────────────────────────┘
                        [ Ver todos los equipos → ]
```

##### Especificaciones de Diseño

```
Contenedor:       Fondo blanco, padding 60px vertical en desktop / 32px en móvil
Título sección:   H2, centrado, "Azul Americano" #262E4F
Subtítulo:        Texto ligero, centrado, #5E748B
Grilla:           CSS Grid, gap 24px, 4 cols desktop / 2 cols tablet / 1 col móvil
Card:             Fondo blanco, border-radius 8px, box-shadow 0 2px 8px rgba(0,0,0,0.08)
                  Hover: box-shadow 0 6px 20px rgba(18,140,235,0.18), translateY(-3px)
                  Cursor: pointer, transición 200ms ease
Imagen:           aspect-ratio 16/9 o cuadrada, object-fit cover, border-radius top 8px
Línea separadora: 3px sólida, color #FFC33C (Amarillo Imperial), width 40px, margin 8px 0
Nombre subtipo:   Font-weight 700, 16px, #262E4F
Lista ítems:      Font-size 13px, color #3C3C3C, line-height 1.8
Flecha ›:         Color #128CEB
CTA inferior:     Botón centrado "Ver todos los equipos →", fondo #128CEB, texto blanco
```

##### Criterios de Aceptación

- [ ] La sección aparece en el home entre la sección de Equipos Destacados y el Hero
- [ ] **7 celdas** en desktop (fila 1: 4 · fila 2: 3 centradas), 2 cols en tablet, 1 col en móvil
- [ ] El clic en cualquier celda navega a `/equipos?tipo=[subtipo]` con el filtro aplicado
- [ ] El hover aplica elevación visual (box-shadow) sin ocultar contenido
- [ ] Las imágenes son reales de cada tipo de equipo
- [ ] La línea amarilla separadora aparece debajo del nombre de cada subtipo
- [ ] El botón "Ver todos los equipos" lleva al listado sin filtros `/equipos`
- [ ] La sección es completamente funcional en iOS Safari y Android Chrome

---

#### HOME-002: Sección "Historias de Éxito" — Carrusel Testimonial
**Prioridad:** Alta | **Estimado:** 5 pts

> *Como visitante que llega al home, quiero ver testimonios reales de clientes con foto y nombre de empresa, para generar confianza antes de tomar contacto o cotizar.*

##### Referencia Visual

| Elemento | Referencia original | Adaptación TM |
|---|---|---|
| Layout | 50% texto izquierda / 50% foto derecha | Mismo layout, fondo blanco con sombra suave |
| Comilla decorativa | `"` grande en azul | Color Azul Empresarial `#128CEB` |
| Texto testimonio | Párrafo largo, ~20px | Work Sans Light, `#3C3C3C`, 18-20px |
| Autor | Foto circular + nombre + empresa | Foto circular 56px + nombre + logo de empresa |
| Foto lateral | Imagen real del cliente en operación | Foto real de la operación del cliente |
| Navegación | Flechas `‹ ›` en círculo | Flechas en `#262E4F` hover `#128CEB` |
| Indicadores | Puntos lineales | Puntos, activo en `#FFC33C` |
| Transición | Slide horizontal | Slide horizontal, duración 400ms, ease-in-out |

##### Wireframe Desktop

```
                    HISTORIAS DE ÉXITO
        Lo que dicen nuestros clientes sobre nosotros

 ‹  ┌──────────────────────────────────────────────────────────┐  ›
    │                          │                               │
    │   "                      │     [FOTO REAL DE LA          │
    │   Texto del testimonio   │      OPERACIÓN DEL           │
    │   del cliente.           │      CLIENTE]                │
    │                          │                               │
    │   ┌────┐  Nombre Cliente │                               │
    │   │LOGO│  EMPRESA S.A.S. │                               │
    │   └────┘                 │                               │
    └──────────────────────────┴───────────────────────────────┘
               ○  ○  ●  ○  ○  ○
```

##### Comportamiento del Carrusel

| Aspecto | Especificación |
|---|---|
| Autoplay | Sí — avance automático cada 6 segundos |
| Pausa en hover | Sí |
| Transición | Slide horizontal, 400ms, ease-in-out |
| Loop | Sí |
| Cantidad recomendada | Entre 4 y 8 testimonios |
| Touch/Swipe móvil | Sí |

##### Criterios de Aceptación

- [ ] La sección aparece en el home en la posición definida
- [ ] El carrusel avanza automáticamente cada 6 segundos y se pausa al hacer hover
- [ ] Las flechas `‹ ›` y swipe táctil funcionan correctamente
- [ ] En desktop: layout 50/50 (texto izquierda, foto derecha)
- [ ] En móvil: layout apilado (foto arriba, texto abajo)
- [ ] La comilla decorativa `"` aparece en `#128CEB`
- [ ] El administrador puede gestionar testimonios desde el panel sin tocar código

---

### ÉPICA 1: CATÁLOGO DE EQUIPOS

#### CAT-001: Catálogo con Filtros Jerárquicos
**Prioridad:** Alta | **Estimado:** 13 pts

> *Como cliente potencial, quiero filtrar equipos mediante 8 niveles de filtros dependientes para encontrar exactamente el equipo que necesito.*

**Estructura de Filtros (Sidebar izquierdo):**

| Orden | Filtro | Tipo | Comportamiento |
|---|---|---|---|
| 1 | Tipo de Equipo | Radio obligatorio | Desbloquea subtipo |
| 2 | Subtipo | Dropdown dinámico | Opciones condicionales |
| 3 | Energía | Checkbox múltiple | Eléctrico, Diésel, GLP, Dual, Híbrido, Manual |
| 4 | Capacidad | Radio | Rangos de toneladas |
| 5 | Altura máxima | Radio | Rangos de metros |
| 6 | Uso | Checkbox | Interior, Exterior, Mixto |
| 7 | Fabricante | Dropdown dinámico | Solo marcas en inventario filtrado |
| 8 | Año | Slider + rangos | 2005-2026 |

**Criterios de Aceptación:**
- [ ] Filtro 1 obligatorio, desbloquea filtro 2
- [ ] Actualización AJAX sin recarga de página
- [ ] Contador de resultados en tiempo real
- [ ] Chips de filtros activos removibles
- [ ] URL parametrizada para compartir búsqueda
- [ ] Responsive: 4 cols desktop, 2 tablet, 1 móvil

---

#### CAT-002: Ficha Técnica de Equipo
**Prioridad:** Alta | **Estimado:** 5 pts

**Contenido:**
- Galería tipo carrusel con zoom (mínimo 4 fotos)
- Info rápida: Marca, modelo, año, tipo, subtipo, energía, capacidad, altura, uso
- Tabla de especificaciones técnicas detalladas
- Descripción comercial (2-3 párrafos)
- Condición: Nuevo / Usado / Reacondicionado + garantía
- Disponibilidad: En stock / Bajo pedido / Agotado
- Precio visible O botón Cotizar (según configuración — ver CAT-005)
- CTA (call to action, botones de llamada a la acción llamativos) Principal: "Solicitar Cotización" (siempre visible)
- CTA (call to action, botones de llamada a la acción llamativos) Secundario: "Agregar a comparador"
- Botón: "¿Qué es un [tipo de equipo]?" → `/equipos/que-es/[tipo]`
- Equipos relacionados (4 recomendaciones)

> ❌ **Restricción:** Sin descarga de PDF en ficha de equipo.

---

#### CAT-003: Solicitud de Cotización
**Prioridad:** Alta | **Estimado:** 5 pts

**Formulario modal:**
- Nombre completo *
- Empresa
- Email *
- Teléfono/WhatsApp *
- Ciudad *
- ¿Necesita financiamiento? (Sí / No)
- Mensaje adicional
- Checkbox aceptación política de datos *

**Flujo post-envío:**
1. Guardar lead en base de datos
2. Email a comercial asignado
3. Email de confirmación a cliente
4. (Opcional) WhatsApp a comercial

---

#### CAT-004: Comparador de Equipos
**Prioridad:** Media | **Estimado:** 8 pts

- Checkbox "Comparar" en cada tarjeta del listado
- Barra sticky inferior con selección activa
- Tabla comparativa: especificaciones en filas, equipos en columnas
- Highlight de mejor especificación por categoría
- Botón "Cotizar estos equipos" desde el comparador

---

#### CAT-005: Lógica Precio Visible / Botón Cotizar
**Prioridad:** Alta | **Estimado:** 3 pts

| Caso | Configuración en CMS/Admin | Lo que ve el usuario |
|---|---|---|
| Equipo con precio público | Campo precio completado + Mostrar precio = SÍ | Precio en tarjeta y ficha |
| Equipo sin precio público | Campo precio vacío O Mostrar precio = NO | Botón amarillo **[Cotizar]** |
| Alquiler con tarifa | Campo tarifa completado + Mostrar tarifa = SÍ | Tarifa visible |
| Alquiler sin tarifa | Campo tarifa vacío O Mostrar tarifa = NO | Botón **[Solicitar cotización de alquiler]** |

> **Nota:** En la sección EQD-001 (Equipos Destacados del home) el precio **nunca** se muestra, independientemente de esta configuración.

**Criterios de Aceptación:**
- [ ] El administrador cambia visibilidad del precio desde el panel sin tocar código
- [ ] El cambio se refleja de inmediato en tarjeta de listado y ficha técnica
- [ ] El botón Cotizar siempre lleva al formulario modal CAT-003 con el equipo preseleccionado

---

#### CAT-006: Mega Menú de Equipos por Subtipo
**Prioridad:** Alta | **Estimado:** 5 pts

> ⚠️ **v6.0:** El mega menú pasa de 8 a **7 subtipos** al eliminar "Montacargas 3 Ruedas". El grid se ajusta a **4+3** o se reorganiza en 7 celdas uniformes.

**Los 7 subtipos del mega menú:**

| # | Subtipo | URL con filtro aplicado |
|---|---|---|
| 1 | Contrabalanceadas | `/equipos?tipo=contrabalanceadas` |
| 2 | Retráctiles | `/equipos?tipo=retractiles` |
| 3 | Apiladores | `/equipos?tipo=apiladores` |
| 4 | Transpaletas | `/equipos?tipo=transpaletas` |
| 5 | Recogepedidos | `/equipos?tipo=recogepedidos` |
| 6 | Estibadores manuales | `/equipos?tipo=estibadores-manuales` |
| 7 | Manlift / Plataformas | `/equipos?tipo=manlift` |

**Criterios de Aceptación:**
- [ ] El mega menú abre/cierra sin recargar la página
- [ ] Cada tarjeta: imagen representativa + nombre + descripción de 1 línea
- [ ] En móvil el acordeón funciona completamente con gestos táctiles

---

### ÉPICA 1B: BIBLIOTECA DE TIPOS DE EQUIPO

#### BIB-001: Páginas Educativas por Tipo de Equipo
**Prioridad:** Alta | **Estimado:** 8 pts

> ⚠️ **v6.0:** La biblioteca pasa de 8 a **7 tipos** al eliminar "Montacargas 3 Ruedas".

**Los 7 tipos con sus URLs:**

| Tipo de equipo | URL |
|---|---|
| Contrabalanceadas | `/equipos/que-es/contrabalanceadas` |
| Retráctiles | `/equipos/que-es/retractiles` |
| Apiladores | `/equipos/que-es/apiladores` |
| Transpaletas | `/equipos/que-es/transpaletas` |
| Recogepedidos | `/equipos/que-es/recogepedidos` |
| Estibadores manuales | `/equipos/que-es/estibadores-manuales` |
| Manlift / Plataformas | `/equipos/que-es/manlift` |

**Contenido de cada página:**
- Qué es y para qué sirve (lenguaje no técnico)
- Casos de uso típicos
- Comparativa rápida vs tipos similares
- Especificaciones clave a tener en cuenta
- CTA (call to action) principal: "Ver equipos disponibles" → `/equipos?tipo=[tipo]`
- CTA secundario: "Hablar con un asesor" → WhatsApp

---

### ÉPICA 2: REPUESTOS Y TIENDA ONLINE

#### REP-001: Navegación por Categorías Técnicas
**Prioridad:** Alta | **Estimado:** 8 pts

Categorías: Sistema Eléctrico, Sistema de Gas, Partes de Motor, Baterías y Accesorios, Llantas, Sistema de Frenos, Sistema de Enfriamiento, Sistema Hidráulico, Sistema de Dirección, Filtros, Lubricantes, Chasis.

#### REP-002: Búsqueda Avanzada
**Prioridad:** Alta | **Estimado:** 5 pts

#### REP-003: Ficha de Repuesto
**Prioridad:** Alta | **Estimado:** 5 pts

#### REP-004: Carrito y Pasarela de Pagos
**Prioridad:** Alta | **Estimado:** 13 pts

| Paso | Contenido |
|---|---|
| 1. Carrito | Items, cantidades, eliminar, calcular envío |
| 2. Datos | Nombre, email, teléfono, dirección, ciudad |
| 3. Envío | Recoger en tienda / Estándar / Express |
| 4. Pago | MercadoPago (tarjeta, cuotas, billetera) / PSE / Transferencia |
| 5. Confirmación | Resumen, número de pedido, email de confirmación |

#### REP-005: Notificaciones Post-compra
**Prioridad:** Media | **Estimado:** 5 pts

---

### ÉPICA 2B: ENERGÍA  ⭐ ACTUALIZADO v6.6

#### ENE-001: Landing y Catálogo de Energía (Baterías y Cargadores)
**Prioridad:** Alta | **Estimado:** 8 pts

> *Como cliente, quiero explorar el catálogo de soluciones de energía para montacargas y plataformas — baterías de plomo y litio, y cargadores Renma y PowerAnderson — para encontrar la opción compatible con mi equipo y solicitar cotización.*

##### Posición en la Navegación

"Energía" es una sección de **primer nivel** en el menú principal, al mismo nivel que Equipos y Repuestos. Contiene dos subsecciones principales: **Baterías** y **Cargadores**.

```
INICIO   EQUIPOS ▼   ENERGÍA ▼   REPUESTOS   SERVICIOS   NOSOTROS ▼
                       ├── Baterías
                       └── Cargadores
```

##### Submenu desplegable de Energía

Al hacer hover o clic en "Energía" se despliega un submenú con las dos categorías:

| Categoría | Descripción breve | URL |
|---|---|---|
| **Baterías** | Plomo y litio para montacargas eléctricos | `/energia/baterias` |
| **Cargadores** | Renma y PowerAnderson | `/energia/cargadores` |

---

##### SUBSECCIÓN: BATERÍAS — `/energia/baterias`

La sección incluye:

| Subsección | Descripción |
|---|---|
| Catálogo de baterías | Listado con filtros por tipo (plomo / litio), voltaje, capacidad (Ah) y compatibilidad con marca/modelo de equipo |
| Ficha de batería | Especificaciones técnicas, compatibilidad, condición (nueva/reacondicionada), precio o botón Cotizar |
| Formulario de cotización | Modal reutilizable similar a CAT-003, con campo de modelo de equipo para indicar compatibilidad requerida |

**Tipos de Batería:**

| Tipo | Descripción |
|---|---|
| **Plomo** | Plomo-ácido tubular (alta durabilidad industrial) · Gel (sin mantenimiento, espacios cerrados) · AGM (alta descarga, sin derrame) |
| **Litio** | Li-Ion — alta eficiencia, carga rápida, largo ciclo de vida |

**Filtros del Catálogo de Baterías:**

| Filtro | Opciones |
|---|---|
| Tipo de batería | Plomo · Litio |
| Subtipo (plomo) | Plomo-ácido tubular · Gel · AGM |
| Voltaje | 24V · 36V · 48V · 80V |
| Capacidad | < 300 Ah · 300-500 Ah · 500-700 Ah · > 700 Ah |
| Compatibilidad | Por marca de equipo (Toyota, Hyster, Yale, Crown, JLG, Genie) |
| Condición | Nueva · Reacondicionada |

---

##### SUBSECCIÓN: CARGADORES — `/energia/cargadores`

La sección incluye:

| Subsección | Descripción |
|---|---|
| Catálogo de cargadores | Listado con filtros por marca, voltaje de salida, amperaje y compatibilidad con tipo de batería |
| Ficha de cargador | Especificaciones técnicas, compatibilidad con baterías, precio o botón Cotizar |
| Formulario de cotización | Modal reutilizable similar a CAT-003, con campo de tipo de batería y equipo asociado |

**Marcas de Cargadores:**

| Marca | Descripción |
|---|---|
| **Renma** | Cargadores industriales para baterías de plomo y litio |
| **PowerAnderson** | Cargadores de alta eficiencia con conectores Anderson |

**Filtros del Catálogo de Cargadores:**

| Filtro | Opciones |
|---|---|
| Marca | Renma · PowerAnderson |
| Voltaje de salida | 24V · 36V · 48V · 80V |
| Amperaje | < 30A · 30-60A · 60-100A · > 100A |
| Compatible con | Baterías de plomo · Baterías de litio · Ambas |
| Condición | Nuevo · Reacondicionado |

---

##### Lógica de Precio (Baterías y Cargadores)

Aplica la misma regla que CAT-005: el administrador define si se muestra el precio o un botón [Cotizar] por cada producto, desde el panel de WordPress sin código.

##### Gestión desde WordPress

- El administrador crea productos en WordPress bajo la taxonomía "Energía > Baterías" o "Energía > Cargadores"
- Puede asignar tipo, marca, voltaje, capacidad y compatibilidad desde campos personalizados
- No requiere tocar código

##### Criterios de Aceptación — ENE-001

- [ ] "Energía" aparece en el menú principal al mismo nivel que Equipos y Repuestos
- [ ] Al hacer hover/clic en "Energía" se despliega submenú con "Baterías" y "Cargadores"
- [ ] `/energia/baterias` muestra catálogo filtrable por tipo (plomo/litio), voltaje y compatibilidad
- [ ] `/energia/cargadores` muestra catálogo filtrable por marca (Renma/PowerAnderson), voltaje y amperaje
- [ ] Cada ficha muestra especificaciones técnicas y compatibilidad con modelos de equipo
- [ ] El formulario de cotización incluye campo de modelo de equipo / tipo de batería del cliente
- [ ] El administrador gestiona ambos catálogos desde WordPress sin código
- [ ] La sección es completamente responsive

---

### ÉPICA 3: CHATBOT CON IA

| ID | Historia | Prioridad | Pts |
|---|---|---|---|
| IA-001 | Chatbot 24/7 para dudas básicas con base de conocimiento | Media | 13 |
| IA-002 | Transferencia a asesor humano para consultas complejas | Media | 5 |
| IA-003 | Panel de administración con historial y leads | Media | 5 |

**Especificaciones de posición y diseño (IA-001):**

```
Posición:         position: fixed, bottom: 24px, right: 24px
                  z-index: 1000 (sobre todos los demás elementos, incluida la barra lateral SID-001)
Visibilidad:      Todas las páginas del sitio, todos los dispositivos incluyendo móvil
Botón flotante:   Círculo 56px, fondo #128CEB (Azul Empresarial), ícono chat blanco
                  Hover: fondo #0f7ad4, scale(1.05), transición 200ms
                  Badge contador de mensajes no leídos en #FFC33C
Ventana chat:     Aparece sobre el botón al hacer clic, max-width 360px, border-radius 16px
                  Header: fondo #262E4F, logo TM + "Asistente Tecni Montacargas" + botón cerrar
                  Body: fondo blanco, mensajes con burbujas
                  Footer: campo de texto + botón enviar en #128CEB
Horario visible:  Mensaje dentro del widget indicando horario de atención humana
```

**Criterios de Aceptación:**
- [ ] El widget aparece en la **esquina inferior derecha** en todas las páginas y en todos los dispositivos
- [ ] En móvil el widget no queda cubierto por ningún otro elemento
- [ ] El botón flotante muestra badge amarillo cuando hay respuesta pendiente
- [ ] El chat se entrena con catálogo, FAQs y políticas de garantía de TM
- [ ] La transferencia a asesor humano (IA-002) es accesible desde dentro del chat

---

### ÉPICA 3B: POPUP DE BIENVENIDA  ⭐ NUEVO v6.4

#### POP-001: Popup de Descuento para Visitantes Nuevos
**Prioridad:** Media | **Estimado:** 5 pts

> *Como visitante nuevo al sitio, quiero recibir una oferta de bienvenida exclusiva para motivarme a registrarme y realizar mi primera compra o alquiler con Tecni Montacargas.*

##### Comportamiento General

| Aspecto | Especificación |
|---|---|
| ¿Dónde aparece? | Solo en la página de inicio (`/`) |
| ¿A quién aparece? | Únicamente a visitantes nuevos — se detecta mediante cookie |
| ¿Cuántas veces? | **Una sola vez** por usuario/navegador |
| ¿Cuándo aparece? | **2 segundos** después de cargar la página |
| Persistencia | Una vez cerrado o completado, se guarda cookie `tm_popup_seen=true` y no vuelve a mostrarse |
| Cierre | Botón ✕ en esquina superior derecha · también cierra al hacer clic fuera del popup (overlay) |

##### Oferta

> **$100.000 COP de descuento** en tu primera compra de repuestos o primer alquiler de montacargas.

| Elemento | Detalle |
|---|---|
| Valor | $100.000 pesos colombianos |
| Aplica en | Repuestos (tienda online) O alquiler de montacargas |
| Condición | Solo para usuarios registrados en su primera transacción |
| Código generado | Al registrarse el sistema genera y envía un código de descuento por email |

##### Wireframe

```
        ┌──────────────────────────────────────────────┐
        │                                           ✕  │
        │   [LOGO TM]                                  │
        │                                              │
        │   🎁  ¡Bienvenido!                           │
        │                                              │
        │   Obtén $100.000 de descuento                │
        │   en tu primera compra de repuestos          │
        │   o alquiler de montacargas                  │
        │                                              │
        │   ─────────────────────────────────────────  │
        │                                              │
        │   [  Crear cuenta  ]  ← tab activo           │
        │   [  Ya tengo cuenta  ]  ← tab inactivo      │
        │                                              │
        │   ┌──────────────────────────────────────┐   │
        │   │  Nombre completo                     │   │
        │   └──────────────────────────────────────┘   │
        │   ┌──────────────────────────────────────┐   │
        │   │  Correo electrónico                  │   │
        │   └──────────────────────────────────────┘   │
        │   ┌──────────────────────────────────────┐   │
        │   │  Contraseña                          │   │
        │   └──────────────────────────────────────┘   │
        │                                              │
        │   [  Registrarme y obtener descuento  ]      │
        │   ← botón amarillo #FFC33C, ancho completo   │
        │                                              │
        │   ☐ Acepto la política de datos              │
        │                                              │
        └──────────────────────────────────────────────┘
              overlay oscuro rgba(0,0,0,0.60) detrás
```

##### Pestaña "Ya tengo cuenta" (Login)

```
        │   ┌──────────────────────────────────────┐   │
        │   │  Correo electrónico                  │   │
        │   └──────────────────────────────────────┘   │
        │   ┌──────────────────────────────────────┐   │
        │   │  Contraseña                          │   │
        │   └──────────────────────────────────────┘   │
        │                                              │
        │   [  Iniciar sesión y usar descuento  ]      │
        │                                              │
        │   ¿Olvidaste tu contraseña? →                │
```

##### Especificaciones de Diseño

```
Overlay:          rgba(0,0,0,0.60), z-index: 1100 (sobre todo, incluyendo el chat)
Contenedor popup: background #FFFFFF, border-radius 16px, max-width 440px
                  padding 40px, box-shadow 0 24px 64px rgba(0,0,0,0.25)
                  Centrado horizontal y verticalmente en pantalla
Logo TM:          Versión horizontal, max-height 36px, centrado
Título oferta:    Work Sans Bold, 22px, #262E4F, centrado
Subtítulo:        Work Sans Light, 15px, #5E748B, centrado
Tabs:             Fondo #F2F4F8, activo fondo #262E4F texto blanco, border-radius 8px
Campos:           Border 1px #E6E6E6, border-radius 8px, padding 12px 16px, font 14px
Botón principal:  Fondo #FFC33C, color #262E4F, Work Sans Bold 15px, border-radius 8px
                  ancho 100%, padding 14px, hover: brighten
Animación:        Aparece con fadeIn + translateY(-16px → 0), duración 350ms, ease-out
Botón ✕:          Posición absolute top 16px right 16px, color #5E748B, hover #262E4F
```

##### Flujo Post-Registro

1. El usuario completa el formulario y acepta política de datos
2. Se crea la cuenta en WordPress
3. Se genera automáticamente un código de descuento único (ej: `TM-BIENVENIDA-XXXX`)
4. El código se muestra en pantalla dentro del mismo popup (mensaje de éxito)
5. Se envía el código por email de confirmación
6. Se guarda cookie `tm_popup_seen=true` — el popup no vuelve a aparecer

##### Flujo Post-Login (usuario existente)

1. El usuario inicia sesión
2. Si es su primera compra, el sistema aplica el descuento automáticamente en el checkout
3. Si ya usó el descuento, se muestra mensaje: "Ya usaste tu descuento de bienvenida"
4. Se guarda cookie `tm_popup_seen=true`

##### Criterios de Aceptación

- [ ] El popup aparece **exactamente 2 segundos** después de cargar el home
- [ ] Solo aparece en la página `/` — no en otras páginas
- [ ] Solo aparece a usuarios que no tengan la cookie `tm_popup_seen=true`
- [ ] Una vez cerrado o completado **no vuelve a aparecer** en ese navegador
- [ ] Cierra al hacer clic en ✕ o en el overlay oscuro de fondo
- [ ] Las pestañas "Crear cuenta" / "Ya tengo cuenta" cambian el formulario sin recargar
- [ ] El botón de registro crea la cuenta y muestra el código de descuento en pantalla
- [ ] El código de descuento se envía también por email
- [ ] El descuento es válido para repuestos (tienda online) y alquiler de montacargas
- [ ] El campo de aceptación de política de datos es obligatorio para continuar
- [ ] El popup es completamente responsive y usable en móvil
- [ ] El administrador puede activar/desactivar el popup y editar el texto desde WordPress sin código

---

### ÉPICA 4: AGENDA DE CITAS

#### AGE-001: Página de Agendamiento — Enlace de Redirección
**Prioridad:** Alta | **Estimado:** 1 pt

- ❌ NO se desarrollará ninguna integración técnica con Microsoft 365
- ✅ SÍ: Un botón **"Agendar cita"** que abre el enlace de agendamiento en una nueva pestaña

```
[  📅  Agendar una cita  ]  → abre enlace en nueva pestaña
```

**Criterios de Aceptación:**
- [ ] El botón "Agendar cita" aparece en `/servicios` y en `/contacto`
- [ ] Abre el enlace en nueva pestaña (target="_blank")
- [ ] El enlace es configurable desde WordPress sin tocar código

---

### ÉPICA 5: TRABAJA CON NOSOTROS  ← ahora bajo "Nosotros" en la navegación (v6.0)

#### TWN-001: Landing "Trabaja con Nosotros" — Diseño Visual
**Prioridad:** Media | **Estimado:** 8 pts

La página se divide en **4 bloques visuales apilados verticalmente:**

**BLOQUE 1** — Hero editorial (texto + columna decorativa)
**BLOQUE 2** — Foto del equipo (ancho completo)
**BLOQUE 3** — "Nuestro Equipo" (texto centrado con etiqueta)
**BLOQUE 4** — Testimonio de empleado (foto con overlay oscuro)

**Criterios de Aceptación:**
- [ ] Los 4 bloques se despliegan en orden vertical en desktop y móvil
- [ ] Bloque 4: overlay oscuro correcto, comillas en `#FFC33C`, foto circular con borde dorado
- [ ] La página es coherente con el tono editorial de la referencia (limpia, profesional, humana)

#### TWN-002: Sección de Vacantes
**Prioridad:** Media | **Estimado:** 5 pts

#### TWN-003: Formulario de Postulación
**Prioridad:** Media | **Estimado:** 5 pts

---

### ÉPICA 6: NOSOTROS  ← agrupa Historia, Blog, Trabaja con Nosotros y Contacto (v6.0)

> **v6.0:** "Nosotros" pasa a ser una sección paraguas que contiene Historia, Blog / Novedades, Trabaja con Nosotros y Contacto. Estos cuatro ítems desaparecen del menú principal como entradas independientes y se acceden desde el submenú de Nosotros o desde las URLs directas.

##### Estructura del Submenú de Nosotros

```
NOSOTROS ▼
├── Historia
├── Blog / Novedades
├── Trabaja con Nosotros
└── Contacto
```

##### URLs (sin cambios funcionales, solo reorganización en la navegación)

| Sección | URL |
|---|---|
| Historia | `/nosotros` o `/nosotros/historia` |
| Blog — Listado | `/nosotros/blog` |
| Blog — Artículo | `/nosotros/blog/[slug]` |
| Trabaja con Nosotros | `/nosotros/trabaja-con-nosotros` |
| Contacto | `/nosotros/contacto` |

---

#### NOS-001: Narrativa Visual — Enfoque Minimalista en Texto  ⭐ ACTUALIZADO v5.9
**Prioridad:** Media | **Estimado:** 13 pts

> **Directriz del cliente:** La sección "Quiénes Somos" / "Historia" debe tener **el mínimo texto posible**. El protagonismo es visual: imágenes del equipo, línea de tiempo gráfica, cifras clave y momentos de la empresa. El texto se limita a frases cortas, titulares de impacto y datos concretos.

##### Principios de Diseño

| Principio | Descripción |
|---|---|
| Texto mínimo | Máximo 2-3 líneas por bloque. Sin párrafos largos. Solo frases de impacto. |
| Cifras clave como protagonistas | +20 años · +500 equipos · +8 marcas · Cobertura nacional — en tipografía grande |
| Imágenes primero | Las fotos del equipo, taller e instalaciones son el contenido principal |
| Línea de tiempo visual | Hitos de la empresa representados con ícono + año + frase corta (no párrafos) |
| Sin historia redactada | No se redacta la historia completa de la empresa — solo hitos visuales |

##### Estructura Recomendada (scroll animado tipo Industronic)

```
┌──────────────────────────────────────────────────────────────────┐
│  [FOTO FONDO OSCURA — equipo o instalaciones]                    │
│                                                                  │
│   +20 años           +500 equipos          8 marcas             │
│   en Colombia        entregados            aliadas              │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
┌──────────────────────────────────────────────────────────────────┐
│  NUESTRA HISTORIA                                                │
│                                                                  │
│  [2004]──────[2010]──────[2016]──────[2020]──────[2026]         │
│  Fundación   Primera     Expansión   +100        Líderes        │
│              alianza     nacional    clientes    del sector     │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
┌──────────────────────────────────────────────────────────────────┐
│  [GALERÍA FOTOS — equipo de trabajo, taller, operaciones]        │
│                                                                  │
│  Nuestros valores:                                               │
│  Confiabilidad · Excelencia · Compromiso                        │
└──────────────────────────────────────────────────────────────────┘
```

> ❌ **Restricción:** NO usar estibas/pallets como elemento visual principal.

##### Criterios de Aceptación

- [ ] El texto total de la sección no supera 200 palabras
- [ ] Las cifras clave se muestran con tipografía grande y prominente
- [ ] La línea de tiempo es visual: iconos + año + frase corta (sin párrafos)
- [ ] Las fotos del equipo y las instalaciones tienen mayor presencia que el texto
- [ ] La sección es completamente editable desde WordPress sin código

#### NOS-002: Galería fotos auténticas
**Prioridad:** Media | **Estimado:** 5 pts

#### NOS-003: Valores y pilares corporativos
**Prioridad:** Media | **Estimado:** 3 pts

---

### ÉPICA 7: QUIÉNES SOMOS / HISTORIA (bajo Nosotros)

#### SER-001: Landing de Servicios Técnicos
**Prioridad:** Alta | **Estimado:** 8 pts

Servicios: Mantenimiento Preventivo, Correctivo, Predictivo, Reparación de Emergencia 24/7, Overhaul / Reacondicionamiento, Diagnóstico Especializado.

#### SER-002: Quiz "Encuentra tu equipo ideal"
**Prioridad:** Alta | **Estimado:** 13 pts

| Paso | Pregunta | Opciones |
|---|---|---|
| 0 / 6 | ¿Qué estás buscando? | Comprar un equipo / Alquilar un equipo |
| 1 / 6 | ¿Cuánto necesitas levantar? | Hasta 2 ton / 2-5 ton / 5-10 ton / Más de 10 ton |
| 2 / 6 | ¿A qué altura necesitas llegar? | Hasta 3m / 3-6m / 6-12m / Más de 12m |
| 3 / 6 | ¿Dónde va a operar el equipo? | Solo interiores / Solo exteriores / Ambos |
| 4 / 6 | ¿Alguna condición especial? | Cámara de frío / Pasillo angosto / Contenedores / Terreno irregular / Ninguna |
| 5 / 6 | ¿Quieres recibir la recomendación en tu correo? (opcional) | [Campo email] |

#### SER-003: Trazabilidad y Analytics del Quiz  ⭐ AMPLIADO v6.5
**Prioridad:** Alta | **Estimado:** 8 pts

> *Como administrador, quiero conocer en detalle cómo los visitantes usan el quiz — incluso si no dejan su email — para entender qué tipos de equipos demanda el mercado y optimizar el catálogo y las campañas comerciales.*

##### Datos Capturados por Sesión de Quiz

Cada vez que un visitante inicia el quiz se crea un registro anónimo que guarda:

| Dato | Descripción | Se guarda aunque no deje email |
|---|---|---|
| Timestamp | Fecha y hora de inicio del quiz | ✅ |
| Intención | Comprar / Alquilar (Paso 0) | ✅ |
| Capacidad de carga | Rango seleccionado (Paso 1) | ✅ |
| Altura de elevación | Rango seleccionado (Paso 2) | ✅ |
| Entorno de operación | Interior / Exterior / Mixto (Paso 3) | ✅ |
| Condición especial | Cámara de frío / Pasillo angosto / etc. (Paso 4) | ✅ |
| Equipo recomendado | Tipo de equipo que arrojó el resultado | ✅ |
| Email | Solo si el usuario lo dejó voluntariamente (Paso 5) | Opcional |
| Dispositivo | Móvil / Tablet / Desktop | ✅ |
| Origen UTM | Campaña, canal y fuente de tráfico | ✅ |
| Completó el quiz | Si llegó al resultado o abandonó en algún paso | ✅ |
| Paso de abandono | En qué pregunta se fue sin terminar | ✅ |
| Conversión post-quiz | Si hizo clic en "Ver equipos" o "Hablar con asesor" | ✅ |

##### Dashboard de Métricas del Quiz

Panel visible desde WordPress para el equipo comercial y de marketing:

**Métricas de uso:**
- Total de quizzes iniciados vs completados (tasa de completitud)
- Embudo paso a paso: Inicio → Paso 1 → Paso 2 → ... → Resultado → Conversión
- Paso con mayor tasa de abandono (dónde se van los usuarios)

**Métricas de demanda de mercado:**
- Split compra vs alquiler (% de cada intención)
- Capacidades más solicitadas (ranking de rangos de tonelaje)
- Alturas más solicitadas
- Entorno más frecuente (interior / exterior / mixto)
- Condiciones especiales más comunes (cámara de frío, pasillo angosto, etc.)
- Top 5 equipos más recomendados por el quiz

**Métricas de conversión:**
- Tasa de conversión a lead (quizzes que dejaron email)
- Tasa de conversión a catálogo (quizzes que hicieron clic en "Ver equipos")
- Tasa de conversión a asesor (quizzes que hicieron clic en "Hablar con asesor")

##### Exportación y Uso Comercial

| Funcionalidad | Detalle |
|---|---|
| Exportar a Excel/CSV | Todos los registros del quiz con sus respuestas para análisis externo |
| Filtros por fecha | Ver métricas por semana, mes o rango personalizado |
| Leads con email | Lista descargable de usuarios que dejaron email, lista para importar a CRM |
| Alertas automáticas | Email semanal al equipo comercial con el resumen de métricas del quiz |

##### Criterios de Aceptación — SER-003

- [ ] Cada sesión del quiz genera un registro en la base de datos, con o sin email
- [ ] El dashboard es accesible desde el panel de WordPress para usuarios con rol administrador
- [ ] Los datos se pueden exportar en Excel/CSV
- [ ] El embudo por pasos muestra claramente dónde se van los usuarios
- [ ] El ranking de equipos más recomendados se actualiza en tiempo real
- [ ] Los registros con email están separados y listos para exportar como lista de leads

---

### ÉPICA 8: ALIANZAS Y POLÍTICAS

#### ALI-001: Sección de Alianzas Comerciales
**Prioridad:** Media | **Estimado:** 3 pts

**Alianza destacada: Barbillón** — Logo, descripción de la alianza, beneficios para clientes.

#### POL-001: Sección de Políticas Corporativas
**Prioridad:** Media | **Estimado:** 5 pts

| Política | Contenido |
|---|---|
| SG-SST | Sistema de Gestión de Seguridad y Salud en el Trabajo |
| Política de Calidad | Compromiso con estándares internacionales |
| Política Ambiental | Compromiso sostenible |
| Política de Garantía | Términos de garantía equipos nuevos y usados |
| Política de Devoluciones | Proceso para repuestos |

---

### ÉPICA 9: BLOG / NOVEDADES  ← ahora bajo "Nosotros" en la navegación (v6.0)

#### BLG-001: Página de Blog — Estilo Editorial Magazine  ⭐ REDISEÑADO v6.2
**Prioridad:** Media | **Estimado:** 8 pts

> *Como visitante, quiero explorar el blog de Tecni Montacargas en una página visualmente atractiva y editorial, para descubrir contenido relevante de forma intuitiva y profesional.*

##### Referencia Visual

La página sigue el estilo editorial magazine de la referencia provista por el cliente: hero dividido en dos bloques (foto + panel de color), tarjetas medianas con foto de fondo y texto overlay, sección de artículos recientes minimalista en 3 columnas, y sección de video opcional al pie.

---

##### BLOQUE 1 — Artículo Destacado (Hero Split 50/50)

El artículo marcado como "destacado" por el administrador ocupa toda la parte superior.

```
┌──────────────────────────────┬──────────────────────────────────────┐
│                              │                                      │
│   [FOTO DEL ARTÍCULO]        │   background: #128CEB                │
│   object-fit: cover          │   (Azul Empresarial)                 │
│   altura: 400px              │                                      │
│   50% del ancho              │   Categoría  ·  Fecha                │
│                              │                                      │
│                              │   Título del artículo                │
│                              │   destacado en dos o tres            │
│                              │   líneas, tipografía grande          │
│                              │   y en blanco                        │
│                              │                                      │
│                              │   [ Leer blog ]                      │
│                              │   ← botón pill outline blanco        │
└──────────────────────────────┴──────────────────────────────────────┘
         ○  ●  ○  ○   ← indicadores si hay múltiples destacados
```

| Elemento | Especificación |
|---|---|
| Layout | 50% foto izquierda / 50% panel derecho |
| Foto | `object-fit: cover` · altura 400px · sin texto encima |
| Panel derecho | Fondo `#128CEB` · padding 48px · display flex · justify-content center |
| Categoría + fecha | Work Sans Light · 12px · `rgba(255,255,255,0.70)` · uppercase · margin-bottom 12px |
| Título | Work Sans Bold · 28-32px · `#FFFFFF` · max 3 líneas · line-height 1.3 |
| Botón | Outline blanco · border 2px `rgba(255,255,255,0.70)` · border-radius 20px · padding 10px 24px · hover: fondo blanco, texto `#128CEB` |
| Indicadores | Puntos debajo del hero si hay >1 destacado · activo `#FFC33C` |
| Móvil | Apilado: foto arriba 220px · panel azul abajo |

---

##### BLOQUE 2 — Dos Tarjetas Medianas con Texto Sobre Imagen

Dos artículos presentados con foto de fondo de ancho completo y texto + botón sobre un overlay oscuro.

```
┌──────────────────────────────┬──────────────────────────────────────┐
│  [FOTO DE FONDO]             │  [FOTO DE FONDO]                     │
│  overlay rgba(0,0,0,0.50)    │  overlay rgba(0,0,0,0.50)            │
│                              │                                      │
│                              │                                      │
│  Categoría                   │  Categoría                           │
│  ═══════════                 │  ═══════════                         │
│  Título del artículo         │  Título del artículo                 │
│  en dos líneas               │  en dos líneas                       │
│                              │                                      │
│  Descripción breve           │  Descripción breve                   │
│  en 2 líneas máximo          │  en 2 líneas máximo                  │
│                              │                                      │
│  [ Ver más ]                 │  [ Ver más ]                         │
└──────────────────────────────┴──────────────────────────────────────┘
```

| Elemento | Especificación |
|---|---|
| Layout | 2 columnas iguales · gap 4px (casi sin separación) |
| Foto de fondo | `object-fit: cover` · altura 280px · width 100% |
| Overlay | `rgba(0,0,0,0.50)` sobre la foto · hover: `rgba(0,0,0,0.65)` · transición 250ms |
| Categoría | Work Sans Bold · 13px · `#FFFFFF` · uppercase · letter-spacing 0.8px |
| Línea decorativa | 2px · `#FFC33C` · width 32px · debajo del nombre de categoría |
| Descripción | Work Sans Light · 13px · `rgba(255,255,255,0.80)` · max 2 líneas |
| Botón | Outline blanco · border-radius 20px (pill) · 13px · padding 8px 20px |
| Todo el texto | Posición inferior izquierda · padding 24px |
| Móvil | Apilado verticalmente · cada tarjeta altura 240px |

---

##### BLOQUE 3 — "Últimos Artículos" (3 columnas minimalistas)

```
                      Últimos artículos

┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐
│  [IMAGEN 16:9]  │  │  [IMAGEN 16:9]  │  │  [IMAGEN 16:9]  │
│                 │  │                 │  │                 │
│ REGULADOR DE    │  │ REGULADOR DE    │  │ ENERGÍA         │
│ VOLTAJE         │  │ VOLTAJE         │  │                 │
│ ← categoría     │  │ ← categoría     │  │ ← categoría     │
│                 │  │                 │  │                 │
│ ¿Qué es un      │  │ Variación de    │  │ Interrupción    │
│ circuito        │  │ voltaje y       │  │ eléctrica:      │
│ regulador de    │  │ regulación:     │  │ causas, efectos │
│ voltaje?        │  │ guía completa   │  │ y soluciones    │
└─────────────────┘  └─────────────────┘  └─────────────────┘

                      [ Ver todos ]
```

| Elemento | Especificación |
|---|---|
| Título sección | "Últimos artículos" · H2 centrado · Work Sans SemiBold · `#262E4F` · margin-bottom 32px |
| Grid | 3 cols desktop · 2 cols tablet · 1 col móvil · gap 24px |
| Imagen | aspect-ratio 16/9 · object-fit cover · border-radius 6px · width 100% |
| Categoría | Work Sans Regular · 11px · `#128CEB` · uppercase · letter-spacing 1px · margin-top 12px |
| Título | Work Sans SemiBold · 15px · `#262E4F` · line-height 1.4 · max 4 líneas |
| Minimalismo intencional | Sin extracto · sin fecha · sin botón explícito — toda la tarjeta es clicable |
| Hover tarjeta | Título cambia a `#128CEB` · imagen con zoom sutil scale(1.03) · transición 250ms |
| Botón "Ver todos" | Pill centrado debajo · borde `#262E4F` · color `#262E4F` · fondo blanco · hover: fondo `#262E4F` texto blanco · border-radius 20px · padding 10px 32px |

---

##### BLOQUE 4 — Sección de Video (Opcional)

Sección al pie con fondo oscuro, titular a la izquierda y botón de reproducción sobre imagen a la derecha. **Solo se muestra si el administrador ha activado y cargado un video en WordPress.**

```
┌──────────────────────────────────────────────────────────────────────┐
│  [fondo oscuro #1A1A1A o foto con overlay rgba(0,0,0,0.75)]          │
│                                                                      │
│   Gestión de montacargas,         [FOTO PERSONA / OPERACIÓN]         │
│   la nueva ventaja estratégica         ┌────────────┐               │
│                                        │     ▶      │               │
│   Descubre cómo Tecni Montacargas      └────────────┘               │
│   puede ser un aliado estratégico                                    │
│   para tu operación.               [ Ver video ]                     │
└──────────────────────────────────────────────────────────────────────┘
```

| Elemento | Especificación |
|---|---|
| Fondo | `#1A1A1A` o foto con overlay `rgba(0,0,0,0.75)` |
| Layout | 2 cols: texto izquierda 55% / imagen derecha 45% |
| Título | Work Sans Bold · 24-28px · `#FFFFFF` |
| Descripción | Work Sans Light · 14px · `rgba(255,255,255,0.75)` · max 3 líneas |
| Imagen | Foto de persona del equipo u operación · height 280px · object-fit cover · border-radius 8px |
| Botón ▶ | Círculo 56px · fondo `rgba(255,255,255,0.15)` · borde 2px blanco · ícono ▶ blanco 20px · centrado sobre imagen · hover: fondo `#128CEB` |
| CTA texto | `[ Ver video ]` debajo de imagen · outline blanco · pill |
| Visibilidad | Si no hay video activo la sección **no aparece** (oculta automáticamente) |
| Móvil | Apilado: texto arriba · imagen + botón abajo |

---

##### Filtros de Categoría

Fila de pills entre el Bloque 1 y el Bloque 2.

```
[ Todos ]  [ Noticias ]  [ Eventos ]  [ Consejos técnicos ]  [ Casos de éxito ]  [ Sector ]  [ Lanzamientos ]
```

| Aspecto | Especificación |
|---|---|
| Estilo | Pills · border-radius 20px · padding 8px 20px |
| Activo | Fondo `#262E4F` · texto blanco |
| Inactivo | Fondo `#F2F4F8` · texto `#5E748B` · hover fondo `#E6EAF2` |
| Comportamiento | Filtra los 3 bloques simultáneamente sin recargar |

##### Colores de Badge por Categoría (Bloque 3)

| Categoría | Color | Texto |
|---|---|---|
| Noticias | `#262E4F` | Blanco |
| Eventos | `#FFC33C` | `#262E4F` |
| Consejos técnicos | `#128CEB` | Blanco |
| Casos de éxito | `#1E7E34` | Blanco |
| Novedades del sector | `#5E748B` | Blanco |
| Lanzamientos | `#E65C00` | Blanco |

##### Orden de secciones `/nosotros/blog`

```
[FILTROS — pills horizontales]
[BLOQUE 1 — Hero Split 50/50]
[BLOQUE 2 — Dos tarjetas medianas con overlay]
[BLOQUE 3 — "Últimos artículos" 3 cols + botón "Ver todos"]
[BLOQUE 4 — Video (solo si hay video activo)]
```

##### Gestión desde WordPress

| Elemento | Cómo se administra |
|---|---|
| Artículo destacado (Bloque 1) | Checkbox "Destacar en blog" en la ficha del artículo |
| Artículos Bloque 2 | Los 2 artículos más recientes de categorías distintas, o selección manual |
| Bloque 3 | Los 3 artículos siguientes más recientes automáticamente |
| Video (Bloque 4) | Campo URL de video + imagen de portada + checkbox "Mostrar sección de video" |

##### Criterios de Aceptación — BLG-001

- [ ] Bloque 1: hero 50/50 con foto izquierda y panel `#128CEB` derecha, botón pill outline blanco
- [ ] Bloque 2: dos tarjetas iguales con overlay oscuro y texto + botón encima
- [ ] Bloque 3: grilla 3 columnas minimalista — solo imagen + categoría + título, toda la tarjeta clicable
- [ ] Botón "Ver todos" tipo pill centrado debajo del Bloque 3
- [ ] Bloque 4: aparece solo si hay un video activo configurado en WordPress
- [ ] Filtros de categoría en pills funcionales entre el hero y las tarjetas medianas
- [ ] Responsive: Bloque 1 apilado · Bloque 2 apilado · Bloque 3 en 1 col en móvil
- [ ] Indexable por Google con URL `/nosotros/blog`

---

#### BLG-002: Artículo Individual del Blog
**Prioridad:** Media | **Estimado:** 3 pts

- Breadcrumb visible: Nosotros > Blog > Título
- Imagen destacada ancho completo superior
- Badge de categoría, fecha y tiempo de lectura
- Botones de compartir: LinkedIn, WhatsApp, Facebook, Copiar enlace
- Artículos relacionados (mínimo 3, mismo estilo minimalista del Bloque 3)
- CTA final: "Contáctanos" + "Ver equipos"
- Meta tags SEO por artículo (título, descripción, Open Graph)
- URL amigable: `/nosotros/blog/nombre-del-articulo`

#### BLG-003: Gestión del Blog desde WordPress
**Prioridad:** Alta | **Estimado:** 2 pts

El cliente puede publicar artículos sin ayuda técnica. Plugin SEO (Yoast o Rank Math) instalado y configurado. Campos adicionales por artículo: checkbox "Destacar en blog" (Bloque 1) y selección manual para Bloque 2.

#### BLG-004: Preview de Blog en el Home
**Prioridad:** Media | **Estimado:** 2 pts

El preview en el home replica el estilo minimalista del Bloque 3 de BLG-001: 3 artículos recientes en columnas con imagen 16:9, categoría en texto pequeño azul y título. Sin extracto ni botón explícito por tarjeta. Botón "Ver todos los artículos →" centrado al pie, estilo pill.

- Si no hay artículos publicados la sección no aparece
- Se actualiza automáticamente con cada nueva publicación

---

### ÉPICA 10: DIRECTORIO DE CONTACTOS — CONTACT CENTER

#### CON-001: Contact Center — Carrusel de Contactos Estilo Dark
**Prioridad:** Media | **Estimado:** 5 pts

**Dos versiones:** Full (página `/contacto` + home) y Mini (footer del sitio).

**Especificaciones clave:**

```
Fondo:            #1A1A1A con textura sutil
Borde card:       2px solid #FFC33C, border-radius 16px
Número teléfono:  Work Sans Bold, 36-40px (full), color #FFC33C
Foto circular:    80px, border 3px #FFC33C
CTAs:             [📞 Llamar] fondo #262E4F  |  [💬 WhatsApp] fondo #25D366
```

**Criterios de Aceptación:**
- [ ] Carrusel con flechas `‹ ›` funcional en desktop y móvil
- [ ] Botón "Llamar" abre el marcador nativo en móvil
- [ ] Botón "WhatsApp" abre WhatsApp con número y mensaje predefinido
- [ ] El administrador gestiona contactos desde WordPress sin código

---

### ÉPICA 11: ANALÍTICA Y TRAZABILIDAD DE USUARIOS  ⭐ NUEVO v6.5

> *Como administrador y equipo comercial, queremos conocer en detalle cómo se comportan los visitantes en el sitio, de dónde vienen y qué hacen antes de cotizar, para tomar decisiones de diseño y marketing basadas en datos reales.*

---

#### ANL-001: Comportamiento en Página — Eventos y Clics
**Prioridad:** Alta | **Estimado:** 5 pts

> *Saber exactamente qué hace cada visitante dentro del sitio: qué botones presiona, cuánto scrollea, cuánto tiempo pasa en cada sección y qué elementos ignora.*

##### Implementación

Se configura **Google Tag Manager (GTM)** con una capa de datos (dataLayer) que dispara eventos personalizados hacia **Google Analytics 4 (GA4)**.

##### Eventos a Rastrear

| Evento | Dónde ocurre | Qué nos dice |
|---|---|---|
| `clic_cta_cotizar` | Cualquier botón "Cotizar" del sitio | Cuántos visitantes tienen intención real de compra |
| `clic_ver_ficha` | Tarjetas de equipos destacados y catálogo | Qué equipos generan más interés |
| `clic_whatsapp` | Barra lateral SID-001, Contact Center, fichas | Canal de contacto más usado |
| `clic_llamar` | Contact Center CON-001 | Preferencia por llamada vs WhatsApp |
| `clic_slide_carrusel` | Equipos destacados, testimonios, contactos | Qué slides se consumen más |
| `scroll_depth` | Todas las páginas (25%, 50%, 75%, 100%) | Hasta dónde llegan los usuarios en cada página |
| `tiempo_en_seccion` | Secciones del home (hero, grilla, equipos, blog) | Qué secciones enganchan más |
| `clic_mega_menu` | Cada subtipo del mega menú de Equipos | Qué tipo de equipo busca más la gente |
| `clic_filtro_catalogo` | Filtros del catálogo (tipo, energía, capacidad, etc.) | Qué características son más buscadas |
| `clic_comparador` | Botón "Agregar al comparador" | Uso real del comparador de equipos |
| `popup_visto` | Popup POP-001 | Cuántos visitantes nuevos ven el popup |
| `popup_registro` | Formulario de registro dentro del popup | Tasa de conversión del popup |
| `popup_cerrado` | Botón ✕ del popup | Cuántos lo descartan sin registrarse |
| `quiz_iniciado` | Paso 0 del quiz | Interés en la herramienta de recomendación |
| `video_reproducido` | Sección de video del blog | Engagement con contenido multimedia |

##### Dashboard en GA4

Reportes personalizados que muestran:
- Top 10 botones más clicados del sitio
- Páginas con mayor y menor scroll depth promedio
- Secciones del home con mayor tiempo de permanencia
- Embudo: visita → ficha → cotización → lead

##### Criterios de Aceptación — ANL-001

- [ ] GTM instalado y configurado con todos los eventos listados
- [ ] Los eventos aparecen en GA4 en tiempo real (modo debug)
- [ ] El scroll depth se mide en incrementos de 25%
- [ ] Los clics en WhatsApp y Llamar se registran separados para comparar
- [ ] El administrador puede ver los reportes desde GA4 sin necesidad del desarrollador

---

#### ANL-002: Embudos de Conversión
**Prioridad:** Alta | **Estimado:** 5 pts

> *Visualizar el camino completo que recorre un visitante desde que llega al sitio hasta que cotiza, e identificar exactamente en qué punto se pierde la mayoría.*

##### Embudos Definidos

**Embudo principal — Cotización de equipo:**
```
1. Entra al sitio (cualquier página)
     ↓
2. Ve la ficha de un equipo
     ↓
3. Abre el formulario de cotización
     ↓
4. Envía la cotización  ← conversión final
```

**Embudo de equipos destacados:**
```
1. Ve la sección EQD-001 en el home
     ↓
2. Hace clic en "Ver ficha →"
     ↓
3. Solicita cotización desde la ficha
```

**Embudo del quiz:**
```
Inicio → Paso 1 → Paso 2 → Paso 3 → Paso 4 → Resultado → Clic en catálogo/asesor
```

**Embudo del popup:**
```
Popup visible → Interacción con formulario → Registro completado
```

##### Lo Que Permite Hacer

- Ver qué porcentaje de visitantes llega a cada paso
- Identificar el paso con mayor caída (punto débil del sitio)
- Comparar embudos por canal de origen (Google Ads vs orgánico vs redes)
- Medir el impacto de cambios de diseño en la tasa de conversión

##### Criterios de Aceptación — ANL-002

- [ ] Los 4 embudos están configurados en GA4 como "Exploration Funnels"
- [ ] Cada paso del embudo dispara un evento desde GTM
- [ ] Los embudos se pueden segmentar por dispositivo y canal de origen
- [ ] El administrador recibe un reporte semanal automático por email con el resumen

---

#### ANL-003: Grabaciones de Sesión y Mapas de Calor
**Prioridad:** Media | **Estimado:** 3 pts

> *Ver grabaciones reales de cómo los visitantes navegan el sitio y mapas de calor que muestran qué zonas reciben más atención, para identificar problemas de usabilidad y oportunidades de mejora.*

##### Herramienta

**Microsoft Clarity** (gratuita) como opción principal. Alternativa: **Hotjar** (versión free suficiente para empezar).

##### Qué Captura

| Funcionalidad | Descripción |
|---|---|
| Grabaciones de sesión | Video del movimiento del mouse, clics y scroll de cada visita · Sin capturar datos personales ni contenido de formularios |
| Mapa de calor de clics | Imagen del sitio con zonas coloreadas según frecuencia de clic · Rojo = mucho clic · Azul = poco o ningún clic |
| Mapa de calor de scroll | Qué porcentaje de usuarios llega a cada parte de la página antes de irse |
| Rage clicks | Detección automática de usuarios que hacen clic repetidamente en el mismo lugar (señal de frustración o elemento roto) |
| Dead clicks | Clics en zonas que no son interactivas (señal de que el usuario cree que algo es un botón y no lo es) |

##### Privacidad

- Los campos de formulario se enmascaran automáticamente — ningún dato personal es visible en las grabaciones
- Compatible con política de privacidad y protección de datos colombiana
- El usuario puede ser informado en la política de cookies del sitio

##### Casos de Uso Concretos para TM

- ¿Los visitantes ven el botón "Cotizar" o lo ignoran? → mapa de calor lo confirma en minutos
- ¿El mega menú de Equipos se usa en móvil? → grabaciones de sesión en móvil
- ¿La sección de Equipos Destacados funciona? → scroll depth + clics en las tarjetas
- ¿El popup de bienvenida molesta o convierte? → rage clicks en el ✕ vs registros completados

##### Criterios de Aceptación — ANL-003

- [ ] Microsoft Clarity o Hotjar instalado vía GTM (sin tocar el código del tema)
- [ ] Las grabaciones están activas en todas las páginas
- [ ] Los campos de formulario están correctamente enmascarados
- [ ] El equipo de TM tiene acceso al panel de Clarity/Hotjar para ver grabaciones y mapas
- [ ] Se configura alerta automática de "rage clicks" para detectar problemas rápido

---

#### ANL-004: Origen del Tráfico por Lead
**Prioridad:** Alta | **Estimado:** 3 pts

> *Saber qué canal de marketing — Google, Instagram, WhatsApp, email, referidos — genera los leads y cotizaciones reales, no solo visitas, para invertir el presupuesto donde realmente funciona.*

##### Implementación

Combinación de **parámetros UTM** en los enlaces de campañas + **GA4** para atribución + registro del origen en cada formulario de cotización.

##### Parámetros UTM Estándar

Cada enlace de campaña debe incluir:
```
?utm_source=google&utm_medium=cpc&utm_campaign=equipos-nuevos
?utm_source=instagram&utm_medium=social&utm_campaign=alquiler-montacargas
?utm_source=whatsapp&utm_medium=referral&utm_campaign=asesor-carlos
```

##### Campo Oculto en Formularios

Cada formulario de cotización, quiz y popup incluye un campo oculto que captura automáticamente el origen UTM del visitante y lo guarda junto con el lead en la base de datos de WordPress.

##### Reporte de Origen de Leads

| Canal | Visitas | Leads generados | Tasa de conversión |
|---|---|---|---|
| Google orgánico | — | — | — |
| Google Ads | — | — | — |
| Instagram | — | — | — |
| WhatsApp directo | — | — | — |
| Email / newsletter | — | — | — |
| Referido (otro sitio) | — | — | — |

*La tabla se llena automáticamente en el dashboard.*

##### Criterios de Aceptación — ANL-004

- [ ] Todos los formularios (cotización, quiz, popup, postulación) capturan el UTM de origen
- [ ] El origen queda guardado en el registro del lead en WordPress
- [ ] GA4 muestra el reporte de conversiones por canal
- [ ] El equipo comercial puede ver de qué canal vino cada cotización recibida
- [ ] Se documenta la convención de nombres UTM para que el equipo de marketing la use de forma consistente

---

#### ANL-005: Perfil Progresivo del Usuario Registrado
**Prioridad:** Media | **Estimado:** 8 pts

> *Enriquecer automáticamente el perfil de cada usuario registrado con su comportamiento en el sitio, para que el equipo comercial pueda hacer seguimiento inteligente y personalizado.*

##### Qué se Guarda en el Perfil

Cada usuario que crea cuenta (vía popup POP-001 o registro normal) tiene un perfil en WordPress que se enriquece automáticamente con:

| Dato | Cómo se captura |
|---|---|
| Equipos vistos | Cada vez que abre la ficha de un equipo se registra en su perfil |
| Tipos de equipo de interés | Inferido de los equipos vistos y filtros usados en el catálogo |
| Repuestos buscados | Búsquedas y fichas de repuestos visitadas |
| Resultado del quiz | Si completó el quiz, qué equipo le recomendó y su intención (compra/alquiler) |
| Cotizaciones solicitadas | Historial de formularios de cotización enviados |
| Compras realizadas | Órdenes completadas en la tienda de repuestos |
| Fecha de último acceso | Cuándo fue la última vez que entró al sitio |
| Canal de origen | Cómo llegó al sitio por primera vez (UTM) |

##### Vista para el Equipo Comercial

Desde el panel de WordPress, el equipo comercial puede ver una ficha de cada usuario registrado con:

```
┌──────────────────────────────────────────────────────┐
│  👤 Juan Pérez — Logística Andina S.A.S.             │
│  📧 juan@logisticaandina.com  📱 57 310 000 0000     │
│  Registrado: 15 feb 2026  |  Último acceso: hoy      │
├──────────────────────────────────────────────────────┤
│  🔍 INTERESES DETECTADOS                             │
│  Equipos vistos: Contrabalanceada Toyota 8FGU25,     │
│                  Retráctil Crown RR 5725              │
│  Quiz: Alquiler · 2-5 ton · Interior · Pasillo ang.  │
│  Equipo recomendado: Retráctiles                     │
├──────────────────────────────────────────────────────┤
│  📋 ACTIVIDAD                                        │
│  2 cotizaciones solicitadas (sin respuesta aún)      │
│  0 compras en tienda de repuestos                    │
│  Canal de origen: Google orgánico                    │
└──────────────────────────────────────────────────────┘
```

##### Exportación

- Lista completa de usuarios registrados exportable en Excel/CSV
- Filtros: por tipo de equipo de interés, por intención (compra/alquiler), por fecha de registro, por canal de origen
- Compatible con importación a CRM (HubSpot, Zoho, etc.)

##### Criterios de Aceptación — ANL-005

- [ ] Cada usuario registrado tiene un perfil enriquecido visible desde WordPress
- [ ] Los equipos vistos se registran automáticamente sin acción del usuario
- [ ] El resultado del quiz queda vinculado al perfil si el usuario está logueado o se registra al final
- [ ] El equipo comercial puede filtrar y exportar usuarios por intereses
- [ ] Los datos respetan la política de privacidad y el usuario puede solicitar su eliminación

---

## 5. INTEGRACIONES TÉCNICAS

| Sistema | Tipo | Uso | Prioridad |
|---|---|---|---|
| Enlace de agendamiento externo | Redirección simple | Botón que abre el sistema de citas del cliente | Baja |
| MercadoPago | Pasarela de pagos | Pagos con tarjeta y billetera | Alta |
| PSE | Pasarela de pagos | Pagos bancarios Colombia | Alta |
| WhatsApp API/Business | Comunicación | Notificaciones y contacto directo | Media |
| Google Maps | Ubicación | Ícono de ubicación en barra lateral sticky (SID-001) | Media |
| OpenAI / Claude / Landbot | IA | Chatbot inteligente | Media |
| SendGrid / Mailgun | Email | Transaccionales y marketing | Media |
| Google Analytics 4 | Analytics | Trazabilidad web, embudos de conversión, origen de leads (ANL-001 a ANL-004) | Alta |
| Google Tag Manager | Analytics | Eventos custom (quiz, clics, scroll, popup, conversiones) — ANL-001 | Alta |
| Microsoft Clarity | UX Analytics | Grabaciones de sesión y mapas de calor (ANL-003) | Media |
| CRM (HubSpot / Zoho / API) | Gestión | Leads y clientes | Media |

---

## 6. REQUISITOS NO FUNCIONALES

### 6.1 Diseño Responsive — Mobile First ✅

| Dispositivo | Breakpoint | Comportamiento esperado |
|---|---|---|
| Móvil | < 768px | Menú hamburguesa · SID-001 **oculto** · Chat IA visible esquina inferior derecha · 1 col en listados |
| Tablet | 768px – 1024px | Menú colapsable · 2 cols en listados · SID-001 visible **lado izquierdo** |
| Desktop | > 1024px | Mega menú desplegable · 3-4 cols en listados · SID-001 visible **lado izquierdo** |

**Criterios clave adicionales v6.4:**
- Barra lateral sticky (SID-001): lado **izquierdo** en desktop/tablet · **invisible en móvil**
- Chat IA (IA-001): esquina inferior **derecha** en **todos** los dispositivos · z-index superior a SID-001
- Popup bienvenida (POP-001): centrado en pantalla · z-index máximo · solo home · solo visitantes nuevos
- Equipos destacados (EQD-001): 3 tarjetas layout horizontal en desktop / 2 en tablet / 1 en móvil
- Hero (HER-001): dos CTAs en fila desktop, apilados en móvil

---

### 6.2 Seguridad ✅

| Área | Requerimiento |
|---|---|
| Certificado SSL | HTTPS obligatorio en todo el sitio |
| Formularios | reCAPTCHA v3 o Honeypot |
| Pagos | No almacenar datos de tarjeta |
| Acceso admin | Usuario + contraseña fuerte + 2FA |
| URL admin | Cambiar ruta `/wp-admin` por defecto |
| Backups | Backup diario, retención 30 días |

---

### 6.3 Control Total de WordPress — Entrega al Cliente ✅

El cliente recibe acceso completo de administrador sin restricciones. El desarrollador no puede retener el acceso a plugins premium sin transferir la licencia.

**Lo que el cliente podrá hacer autónomamente:**
- Crear páginas, entradas de blog, equipos
- Gestionar menús, imágenes, usuarios
- Instalar plugins y actualizar WordPress
- Marcar/desmarcar equipos como "Destacados" (EQD-001)
- Configurar el enlace y los datos de la barra lateral sticky (SID-001)
- Configurar los datos del Contact Center (CON-001)

---

### 6.4 Performance y SEO

| Aspecto | Requerimiento |
|---|---|
| Performance | Lighthouse score >85 en móvil, >90 en desktop |
| SEO | Meta tags dinámicos por página, sitemap XML automático, URLs amigables |
| Accesibilidad | WCAG 2.1 AA mínimo |
| Escalabilidad | Soporte para catálogo de hasta 1.000 equipos sin degradación visible |

---

## 7. PLAN DE IMPLEMENTACIÓN

| Fase | Duración | Entregables |
|---|---|---|
| Fase 0 — Setup | Semana 1 | Repositorio, ambientes, CI/CD, inserción del enlace de agendamiento |
| Fase 1 — Core | Semanas 2-3 | Hero (HER-001), Barra lateral sticky (SID-001), Home completo (HOME-001, EQD-001), Equipos unificado con mega menú (CAT-001, CAT-002, CAT-006) |
| Fase 2 — Biblioteca + Precio | Semana 4 | 8 páginas `/equipos/que-es/[tipo]` + lógica precio/cotizar (BIB-001, CAT-005) |
| Fase 3 — E-commerce | Semanas 5-6 | Repuestos, carrito, checkout MercadoPago + PSE (REP-001 a REP-005) |
| Fase 4 — Servicios + Quiz | Semana 7 | Landing servicios, quiz 6 pasos, botón agendamiento (SER-001 a SER-003, AGE-001) |
| Fase 5 — Contenido | Semana 8 | Nosotros minimalista (NOS-001), alianzas, políticas, directorio WhatsApp |
| Fase 6 — Interacción | Semana 9 | Chatbot IA, formularios, notificaciones (IA, TWN) |
| Fase 7 — QA & Deploy | Semana 10 | Testing, optimización, contenido final, capacitación, lanzamiento |

**Total estimado: 10 semanas (2.5 meses)**

### MVP vs Fase 2

| Funcionalidad | MVP (Lanzamiento) | Fase 2 (Mejoras) |
|---|---|---|
| Hero | HER-001 funcional con textos y logos definitivos | Imagen de fondo animada o video loop |
| Equipos Destacados | EQD-001 carrusel 3 tarjetas sin precios | Mostrar precio si el cliente lo activa |
| Sidebar sticky | SID-001 funcional con 3 íconos | Tooltip expandido, analytics de clics |
| Nosotros | Texto mínimo, cifras y galería | Video institucional |
| Catálogo | Filtros básicos, precio/cotizar | Comparador, favoritos, alertas de stock |
| Quiz | 6 pasos con intención compra/alquiler | IA predictiva |
| Blog | 3 artículos al lanzamiento | Newsletter integrado |

---

## 8. CHECKLIST DE CONTENIDO PREVIO AL LANZAMIENTO

### Hero Principal (HER-001)
- [ ] Texto definitivo del badge (ej: "Más de 20 años de experiencia en Colombia") — confirmar con el cliente
- [ ] Texto definitivo del H1 y subtítulo
- [ ] Textos definitivos de los dos CTAs
- [ ] Logos de los 6 fabricantes en formato PNG fondo transparente o blanco (Toyota, Hyster, Yale, Crown, JLG, Genie)
- [ ] Decisión sobre imagen/foto de fondo opcional (si se usa: foto de equipo real, landscape, alta resolución)

### Barra Lateral Sticky (SID-001)
- [ ] Dirección completa de Tecni Montacargas para enlace de Google Maps
- [ ] Número de WhatsApp con código de país (ej: 573160184042)
- [ ] Mensaje predefinido de WhatsApp acordado con el cliente
- [ ] Correo electrónico de contacto principal (o decisión: mailto vs /nosotros/contacto)
- [ ] **URL de la página de LinkedIn** de Tecni Montacargas (página de empresa, no perfil personal)
- [ ] Decisión: ícono de correo abre mailto o redirige a la página de contacto

### Equipos Destacados (EQD-001)
- [ ] Selección de 4 a 8 equipos del inventario para marcar como "Destacados" al lanzamiento
- [ ] Mínimo 1 foto de buena calidad por equipo destacado (aspect-ratio 4/3)
- [ ] Características técnicas confirmadas: tipo de energía, capacidad en toneladas, altura máxima
- [ ] Condición correctamente asignada: Usado / Nuevo / Reacondicionado

### Equipos (Catálogo)
- [ ] Inventario completo en Excel/CSV con todos los campos
- [ ] Campo "Mostrar precio": SÍ / NO por cada equipo
- [ ] Mínimo 4 fotos profesionales por equipo
- [ ] Especificaciones técnicas detalladas por modelo

### Energía — Baterías y Cargadores (ENE-001)
- [ ] Inventario de baterías en Excel/CSV: tipo (plomo/litio), subtipo, voltaje, capacidad (Ah), compatibilidad, condición, precio o "cotizar"
- [ ] Inventario de cargadores en Excel/CSV: marca (Renma/PowerAnderson), voltaje de salida, amperaje, compatibilidad con batería, condición, precio o "cotizar"
- [ ] Mínimo 1 foto por referencia de batería y cargador
- [ ] Especificaciones técnicas de cada referencia confirmadas con el proveedor
- [ ] Decisión: ¿Se muestran precios de baterías/cargadores o siempre botón Cotizar?

### Nosotros — Minimalista (NOS-001)
- [ ] Cifras clave confirmadas: años de experiencia, equipos entregados, marcas aliadas, ciudades con presencia
- [ ] Hitos de la empresa para la línea de tiempo: fecha + frase corta (sin párrafos)
- [ ] 15-20 fotos del equipo de trabajo, taller e instalaciones (sin estibas como protagonistas)
- [ ] Frase de misión o valores: máximo 1 oración por valor

### Blog / Novedades
- [ ] Mínimo 3 artículos publicados antes del lanzamiento
- [ ] Imagen destacada por artículo (mínimo 1200×630px)

### Contact Center — Directorio de Contactos (CON-001)
- [ ] Fotos profesionales de cada persona (fondo neutro, mínimo 160×160px)
- [ ] Nombre completo, cargo y área de cada contacto
- [ ] Número de WhatsApp con código de país
- [ ] Email directo o del área

**Contactos de ejemplo — reemplazar con datos reales antes del lanzamiento:**

| # | Nombre | Cargo / Área | Teléfono | Email |
|---|---|---|---|---|
| 1 | [Nombre Asesor 1] | Ventas & Cotizaciones | 57 316 000 0001 | asesor1@tecnimontacargas.com |
| 2 | [Nombre Asesor 2] | Servicio Técnico & Mantenimiento | 57 316 000 0002 | asesor2@tecnimontacargas.com |
| 3 | [Nombre Asesor 3] | Repuestos & Alquiler de Flotas | 57 316 000 0003 | asesor3@tecnimontacargas.com |

> Pendiente del cliente: confirmar nombres reales, números definitivos, fotos y orden de aparición en el carrusel.

### Técnico / Legal
- [ ] Cuenta MercadoPago productiva configurada
- [ ] Enlace público de agendamiento activo (Microsoft Bookings, Calendly u otro)
- [ ] Credenciales de administrador de WordPress generadas y entregadas al cliente
- [ ] Sesión de capacitación realizada con persona técnica del cliente

---

## 9. ANEXOS

### Anexo A: Estructura del Home (Orden de Secciones)

```
[HEADER — logo, menú, CTA cotizar]
│
[HERO — HER-001]
│
[GRILLA DE SUBTIPOS — HOME-001]
│
[EQUIPOS DESTACADOS — EQD-001]
│
[HISTORIAS DE ÉXITO — HOME-002]
│
[SERVICIOS TÉCNICOS PREVIEW]
│
[ÚLTIMAS NOTICIAS — BLG-004]
│
[CTA CONTACTO / QUIZ]
│
[FOOTER con Contact Center Mini — CON-001]

[BARRA LATERAL IZQUIERDA STICKY — SID-001]
  Flotante lado IZQUIERDO · solo desktop y tablet · invisible en móvil
  📍 Ubicación   💬 WhatsApp   ✉️ Correo   in LinkedIn

[CHAT IA — IA-001]
  Esquina inferior DERECHA · todos los dispositivos incluyendo móvil
  z-index superior a SID-001

[POPUP BIENVENIDA — POP-001]
  Aparece 2 seg después de cargar · solo home · solo visitantes nuevos · 1 vez
  $100.000 COP de descuento · formulario registro / login integrado
```

---

### Anexo B: Flujo de Filtros CAT-001

```
[TIPO DE EQUIPO] → Montacargas → [SUBTIPO] → [ENERGÍA] → [CAPACIDAD]
                                                              ↓
                                                          [ALTURA]
                                                              ↓
                                                           [USO]
                                                              ↓
                                                       [FABRICANTE]
                                                              ↓
                                                            [AÑO]
                                                              ↓
                                                        [RESULTADOS]
```

---

### Anexo C: Estructura de URLs SEO-Friendly

| Página | URL |
|---|---|
| Home | `/` |
| Equipos · Listado general | `/equipos` |
| Equipos · Filtrado por subtipo | `/equipos?tipo=contrabalanceadas` |
| Equipos · Filtrado comprar | `/equipos?tipo=contrabalanceadas&modo=comprar` |
| Equipos · Filtrado alquilar | `/equipos?tipo=contrabalanceadas&modo=alquilar` |
| Equipos · Ficha de equipo | `/equipos/marca-modelo-año-id` |
| Biblioteca · Contrabalanceadas | `/equipos/que-es/contrabalanceadas` |
| Biblioteca · Retráctiles | `/equipos/que-es/retractiles` |
| Biblioteca · Apiladores | `/equipos/que-es/apiladores` |
| Biblioteca · Transpaletas | `/equipos/que-es/transpaletas` |
| Biblioteca · Recogepedidos | `/equipos/que-es/recogepedidos` |
| Biblioteca · Estibadores manuales | `/equipos/que-es/estibadores-manuales` |
| Biblioteca · Manlift / Plataformas | `/equipos/que-es/manlift` |
| Energía · Listado general | `/energia` |
| Energía · Baterías | `/energia/baterias` |
| Energía · Baterías · Ficha | `/energia/baterias/[sku-nombre]` |
| Energía · Cargadores | `/energia/cargadores` |
| Energía · Cargadores · Ficha | `/energia/cargadores/[sku-nombre]` |
| Quiz | `/encuentra-tu-equipo` |
| Repuestos · Listado | `/repuestos` |
| Repuestos · Categoría | `/repuestos/sistema-electrico` |
| Repuestos · Producto | `/repuestos/sku-nombre-del-repuesto` |
| Servicios | `/servicios` |
| Agendar cita | `/agendar-cita` (redirige a enlace externo) |
| Nosotros · Historia | `/nosotros` |
| Nosotros · Blog · Listado | `/nosotros/blog` |
| Nosotros · Blog · Artículo | `/nosotros/blog/[slug]` |
| Nosotros · Trabaja con Nosotros | `/nosotros/trabaja-con-nosotros` |
| Nosotros · Contacto | `/nosotros/contacto` |
| Nosotros · Alianzas | `/nosotros/alianzas` |
| Legal · Términos | `/legal/terminos-y-condiciones` |
| Legal · Privacidad | `/legal/politica-de-privacidad` |

---
# ÉPICA 12 — SEO: META TAGS Y PALABRAS CLAVE
## Tecni Montacargas — Documento de Requerimientos v6.6 (Complemento SEO)

**Fecha:** 25 de febrero de 2026  
**Aplica a:** Sitio Web Corporativo + E-commerce + Servicios  
**Marca:** Tecni Montacargas TM-Dual

---

## Índice

1. [Configuración Global de SEO](#1-configuración-global-de-seo)
2. [Meta Tags por Página](#2-meta-tags-por-página)
3. [Palabras Clave por Sección](#3-palabras-clave-por-sección)
4. [Datos de Negocio para SEO Local](#4-datos-de-negocio-para-seo-local)
5. [Criterios de Aceptación SEO](#5-criterios-de-aceptación-seo)

---

## 1. CONFIGURACIÓN GLOBAL DE SEO

Estos valores aplican a todo el sitio como configuración base. Cada página los sobreescribe con sus propios meta tags específicos (ver sección 2).

| Campo | Valor |
|---|---|
| `lang` del HTML | `es` |
| Locale Open Graph | `es_CO` |
| Tipo OG por defecto | `website` |
| `og:site_name` | `Tecni Montacargas` |
| Twitter card | `summary_large_image` |
| `meta name="robots"` | `index, follow` |
| `meta name="author"` | `Tecnimontacargas Dual` |
| Imagen OG por defecto | `/images/og-default.jpg` (1200×630px, imagen de equipo representativo) |

### Elementos técnicos obligatorios en `<head>` de todas las páginas

```html
<html lang="es">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="index, follow">
<meta name="author" content="Tecnimontacargas Dual">
<link rel="canonical" href="https://tecnimontacargas.com[URL-de-la-página]">
<link rel="icon" href="/favicon.ico" type="image/x-icon">
```

> **Nota de implementación:** En WordPress, usar el plugin **Rank Math** (opción preferida) o **Yoast SEO** para gestionar meta tags dinámicos por página sin tocar código. Ver BLG-003 del documento de requerimientos principal.

---

## 2. META TAGS POR PÁGINA

Cada fila define el `<title>`, `meta description` y `keywords` que deben configurarse en WordPress para esa URL.

---

### 2.1 Inicio — `/`

| Campo | Valor |
|---|---|
| **Title** | `Montacargas Bogotá \| Venta, Alquiler y Mantenimiento \| Tecnimontacargas` |
| **Description** | `Expertos en venta, alquiler y servicio técnico de montacargas en Colombia. Toyota, Hyster, Yale, Crown, JLG y Genie. Más de 20 años de experiencia. Cotice ahora.` |
| **Keywords** | `montacargas bogotá, alquiler montacargas, venta montacargas, mantenimiento montacargas, montacargas eléctricos, montacargas colombia, tecnimontacargas` |
| **og:title** | `Tecni Montacargas — Venta, Alquiler y Servicio Técnico en Colombia` |
| **og:description** | `Más de 20 años especializados en montacargas y plataformas elevadoras. Toyota, Hyster, Yale, Crown, JLG y Genie. Cotiza hoy.` |

---

### 2.2 Equipos — Listado general — `/equipos`

| Campo | Valor |
|---|---|
| **Title** | `Catálogo de Montacargas en Venta y Alquiler \| Tecnimontacargas` |
| **Description** | `Explora nuestro catálogo de montacargas nuevos, usados y reacondicionados. Contrabalanceados, retráctiles, apiladores, transpaletas, manlift y más. Solicita cotización.` |
| **Keywords** | `catálogo montacargas, montacargas en venta, montacargas en alquiler, montacargas nuevos, montacargas usados, montacargas reacondicionados` |

---

### 2.3 Equipos — Subtipos (una configuración por tipo)

#### Contrabalanceadas — `/equipos?tipo=contrabalanceadas`

| Campo | Valor |
|---|---|
| **Title** | `Montacargas Contrabalanceadas en Venta y Alquiler \| Tecnimontacargas` |
| **Description** | `Montacargas contrabalanceadas de 3 y 4 ruedas, alta capacidad y para contenedores. Marcas Toyota, Hyster, Yale. Nuevos, usados y reacondicionados en Colombia.` |
| **Keywords** | `montacargas contrabalanceada, montacargas 3 ruedas, montacargas 4 ruedas, montacargas alta capacidad, montacargas contenedores` |

#### Retráctiles — `/equipos?tipo=retractiles`

| Campo | Valor |
|---|---|
| **Title** | `Montacargas Retráctiles — Reach Truck \| Tecnimontacargas` |
| **Description** | `Montacargas retráctiles reach truck para pasillos angostos y almacenes de alta densidad. Simple reach, doble reach y VNA disponibles en Colombia.` |
| **Keywords** | `reach truck, montacargas retráctil, montacargas pasillo angosto, reach simple, doble reach, VNA montacargas` |

#### Apiladores — `/equipos?tipo=apiladores`

| Campo | Valor |
|---|---|
| **Title** | `Apiladores Eléctricos y Semiléctricos \| Tecnimontacargas` |
| **Description** | `Apiladores manuales, semieléctricos y eléctricos montados para bodegas y almacenes. Compactos y eficientes. Solicita cotización en Colombia.` |
| **Keywords** | `apilador eléctrico, apilador manual, apilador semieléctrico, apilador compacto, apilador montacargas colombia` |

#### Transpaletas — `/equipos?tipo=transpaletas`

| Campo | Valor |
|---|---|
| **Title** | `Transpaletas Manuales y Eléctricas \| Tecnimontacargas` |
| **Description** | `Transpaletas manuales, eléctricas, bajo perfil y con báscula para logística y bodegas. Venta y alquiler en Colombia.` |
| **Keywords** | `transpaleta eléctrica, transpaleta manual, transpaleta con báscula, transpaleta bajo perfil, zorra eléctrica colombia` |

#### Recogepedidos — `/equipos?tipo=recogepedidos`

| Campo | Valor |
|---|---|
| **Title** | `Recogepedidos — Order Picker \| Tecnimontacargas` |
| **Description** | `Equipos recogepedidos de bajo, medio y alto nivel para picking eficiente en centros de distribución. Disponibles en Colombia.` |
| **Keywords** | `recogepedidos, order picker, picking montacargas, recogepedidos alto nivel, recogepedidos bajo nivel` |

#### Estibadores Manuales — `/equipos?tipo=estibadores-manuales`

| Campo | Valor |
|---|---|
| **Title** | `Estibadores Manuales e Hidráulicos \| Tecnimontacargas` |
| **Description** | `Estibadores manuales hidráulicos y de tijera para manejo de carga en bodegas. Sin motor, económicos y resistentes. Venta en Colombia.` |
| **Keywords** | `estibador manual, estibador hidráulico, estibador tijera, apilador manual sin motor, estibador bajo costo` |

#### Manlift / Plataformas — `/equipos?tipo=manlift`

| Campo | Valor |
|---|---|
| **Title** | `Plataformas Elevadoras y Manlift \| Alquiler y Venta \| Tecnimontacargas` |
| **Description** | `Tijeras eléctricas, brazos articulados, telescópicos y todo terreno de marcas JLG y Genie. Alquiler y venta de manlift en Colombia.` |
| **Keywords** | `manlift, plataforma elevadora, tijera eléctrica, brazo articulado, JLG colombia, Genie colombia, plataforma todo terreno` |

---

### 2.4 Biblioteca de tipos de equipo — `/equipos/que-es/[tipo]`

Estas páginas tienen como objetivo SEO educativo/informacional. El title sigue el patrón `¿Qué es un [tipo]? | Guía Completa | Tecnimontacargas`.

| Página | Title | Description |
|---|---|---|
| `/equipos/que-es/contrabalanceadas` | `¿Qué es un Montacargas Contrabalanceado? \| Guía Completa` | `Aprende qué es un montacargas contrabalanceado, para qué sirve, sus tipos y cuándo usarlo. Guía completa de Tecnimontacargas.` |
| `/equipos/que-es/retractiles` | `¿Qué es un Reach Truck o Montacargas Retráctil? \| Guía` | `Descubre qué es un reach truck, cómo funciona en pasillos angostos y en qué se diferencia de un montacargas convencional.` |
| `/equipos/que-es/apiladores` | `¿Qué es un Apilador Eléctrico? \| Guía Completa` | `Todo sobre los apiladores eléctricos y manuales: tipos, capacidades, diferencias y cuándo elegir uno para tu bodega.` |
| `/equipos/que-es/transpaletas` | `¿Qué es una Transpaleta Eléctrica? \| Guía Completa` | `Diferencias entre transpaleta manual y eléctrica, tipos disponibles y casos de uso en logística y almacenamiento.` |
| `/equipos/que-es/recogepedidos` | `¿Qué es un Recogepedidos u Order Picker? \| Guía` | `Conoce los recogepedidos de bajo, medio y alto nivel: cómo funcionan y cuál necesitas para tu centro de distribución.` |
| `/equipos/que-es/estibadores-manuales` | `¿Qué es un Estibador Manual? \| Guía y Tipos` | `Guía sobre estibadores manuales hidráulicos: tipos, capacidades y diferencias con apiladores eléctricos.` |
| `/equipos/que-es/manlift` | `¿Qué es un Manlift o Plataforma Elevadora? \| Guía` | `Todo sobre plataformas elevadoras y manlift: tijeras, brazos articulados, telescópicos y cuándo alquilar cada uno.` |

---

### 2.5 Energía — `/energia`

| Campo | Valor |
|---|---|
| **Title** | `Baterías y Cargadores para Montacargas \| Tecnimontacargas` |
| **Description** | `Soluciones de energía para montacargas eléctricos: baterías de plomo y litio, cargadores Renma y PowerAnderson. Cotiza en Colombia.` |
| **Keywords** | `baterías montacargas, cargadores montacargas, batería litio montacargas, batería plomo montacargas, Renma, PowerAnderson colombia` |

#### Baterías — `/energia/baterias`

| Campo | Valor |
|---|---|
| **Title** | `Baterías para Montacargas Plomo y Litio \| Tecnimontacargas` |
| **Description** | `Baterías industriales de plomo-ácido tubular, gel, AGM y litio para montacargas eléctricos. Nuevas y reacondicionadas. Cotiza en Colombia.` |
| **Keywords** | `batería montacargas, batería plomo montacargas, batería litio montacargas, batería AGM, batería gel montacargas, batería tracción colombia` |

#### Cargadores — `/energia/cargadores`

| Campo | Valor |
|---|---|
| **Title** | `Cargadores para Baterías de Montacargas \| Renma y PowerAnderson` |
| **Description** | `Cargadores industriales Renma y PowerAnderson para baterías de plomo y litio de montacargas. Alta eficiencia y confiabilidad. Colombia.` |
| **Keywords** | `cargador batería montacargas, cargador Renma, PowerAnderson, cargador industrial colombia, cargador plomo litio montacargas` |

---

### 2.6 Repuestos — `/repuestos`

| Campo | Valor |
|---|---|
| **Title** | `Repuestos para Montacargas \| Tienda Online \| Tecnimontacargas` |
| **Description** | `Compra online repuestos originales y compatibles para montacargas: sistema eléctrico, hidráulico, motor, llantas, frenos y más. Envío a toda Colombia.` |
| **Keywords** | `repuestos montacargas, partes montacargas, accesorios montacargas, repuestos originales montacargas, llantas montacargas, filtros montacargas colombia` |

#### Páginas de categoría de repuestos (patrón)

El title sigue el patrón: `[Categoría] para Montacargas | Tecnimontacargas`

| Categoría | Title | Keywords clave |
|---|---|---|
| Sistema Eléctrico | `Sistema Eléctrico para Montacargas \| Repuestos` | `sistema eléctrico montacargas, arnés montacargas, fusibles montacargas` |
| Sistema Hidráulico | `Sistema Hidráulico para Montacargas \| Repuestos` | `cilindro hidráulico montacargas, bomba hidráulica montacargas, sellos hidráulicos` |
| Llantas | `Llantas para Montacargas \| Caucho Sólido e Industrial` | `llantas montacargas, llantas caucho sólido, llantas industriales colombia` |
| Baterías y Accesorios | `Baterías y Accesorios para Montacargas \| Repuestos` | `batería repuesto montacargas, accesorios batería, conectores batería` |
| Filtros | `Filtros para Montacargas \| Aceite, Aire y Combustible` | `filtros montacargas, filtro aceite montacargas, filtro aire montacargas` |

---

### 2.7 Servicios Técnicos — `/servicios`

| Campo | Valor |
|---|---|
| **Title** | `Servicio Técnico de Montacargas en Colombia \| Tecnimontacargas` |
| **Description** | `Mantenimiento preventivo, correctivo y predictivo para montacargas. Reparación de emergencia 24/7. Overhaul y diagnóstico especializado en Colombia.` |
| **Keywords** | `servicio técnico montacargas, mantenimiento preventivo montacargas, reparación montacargas, mantenimiento correctivo montacargas, overhaul montacargas colombia` |

---

### 2.8 Quiz — `/encuentra-tu-equipo`

| Campo | Valor |
|---|---|
| **Title** | `Encuentra tu Montacargas Ideal \| Quiz Interactivo \| Tecnimontacargas` |
| **Description** | `Responde 6 preguntas y descubre qué montacargas o plataforma elevadora es ideal para tu operación. Recomendación gratuita e inmediata.` |
| **Keywords** | `qué montacargas necesito, cómo elegir montacargas, recomendación montacargas, montacargas para bodega, montacargas para exterior` |

---

### 2.9 Nosotros — Historia — `/nosotros`

| Campo | Valor |
|---|---|
| **Title** | `Quiénes Somos \| Más de 20 Años en Montacargas \| Tecnimontacargas` |
| **Description** | `Conoce la historia de Tecnimontacargas: más de 20 años de experiencia en venta, alquiler y servicio técnico de montacargas en Colombia.` |
| **Keywords** | `tecnimontacargas, quiénes somos, empresa montacargas colombia, historia tecnimontacargas, especialistas montacargas bogotá` |

---

### 2.10 Blog — Listado — `/nosotros/blog`

| Campo | Valor |
|---|---|
| **Title** | `Blog de Montacargas \| Consejos, Noticias y Novedades \| Tecnimontacargas` |
| **Description** | `Aprende sobre montacargas, plataformas elevadoras, mantenimiento y logística industrial. Artículos técnicos y noticias del sector en Colombia.` |
| **Keywords** | `blog montacargas, noticias montacargas, consejos mantenimiento montacargas, guías montacargas, sector logístico colombia` |

> **Nota:** Cada artículo individual (`/nosotros/blog/[slug]`) debe tener su propio title y description únicos, gestionados desde el plugin SEO de WordPress por el equipo de contenido de TM.

---

### 2.11 Trabaja con Nosotros — `/nosotros/trabaja-con-nosotros`

| Campo | Valor |
|---|---|
| **Title** | `Trabaja con Nosotros \| Vacantes en Tecnimontacargas Colombia` |
| **Description** | `Únete al equipo de Tecnimontacargas. Revisa nuestras vacantes en el sector de montacargas, logística y mantenimiento industrial en Colombia.` |
| **Keywords** | `trabajar en tecnimontacargas, vacantes montacargas colombia, empleo sector logístico, técnico montacargas empleo` |

---

### 2.12 Contacto — `/nosotros/contacto`

| Campo | Valor |
|---|---|
| **Title** | `Contáctenos \| Tecnimontacargas Bogotá Colombia` |
| **Description** | `Contáctenos para cotizar montacargas, solicitar mantenimiento o comprar repuestos. Atención en Bogotá y toda Colombia. WhatsApp y teléfono disponibles.` |
| **Keywords** | `contacto tecnimontacargas, teléfono montacargas bogotá, cotizar montacargas, whatsapp montacargas colombia` |

---

## 3. PALABRAS CLAVE POR SECCIÓN

Mapa consolidado de intenciones de búsqueda alineadas con cada sección del sitio. Usar como referencia para redacción de textos, títulos internos (H2/H3) y alt text de imágenes.

---

### 3.1 Equipos — Intenciones de búsqueda por fase del ciclo de compra

| Fase | Intención | Palabras clave representativas | Página destino |
|---|---|---|---|
| **Informacional** | El usuario quiere entender qué equipo necesita | `qué es un reach truck`, `para qué sirve un apilador`, `diferencia entre montacargas y transpaleta` | `/equipos/que-es/[tipo]` |
| **Comparación** | El usuario evalúa opciones | `montacargas eléctrico vs diésel`, `reach truck vs contrabalanceada`, `apilador vs transpaleta eléctrica` | `/equipos/que-es/[tipo]` + Quiz |
| **Transaccional** | El usuario está listo para comprar o alquilar | `comprar montacargas contrabalanceada bogotá`, `alquiler reach truck colombia`, `precio montacargas Toyota` | `/equipos?tipo=[tipo]` |
| **Local** | El usuario busca proveedor específico | `montacargas bogotá`, `servicio técnico montacargas bogotá`, `alquiler montacargas medellín` | Home + Contacto |

---

### 3.2 Palabras clave por subtipo de equipo

| Subtipo | Palabras clave primarias | Palabras clave secundarias (long tail) |
|---|---|---|
| **Contrabalanceadas** | `montacargas contrabalanceada`, `montacargas Toyota bogotá`, `montacargas GLP colombia` | `montacargas alta capacidad contenedores`, `montacargas 4 ruedas diésel`, `Toyota 8FGU25 colombia` |
| **Retráctiles** | `reach truck colombia`, `montacargas retráctil bogotá`, `order picker VNA` | `reach truck pasillo angosto`, `doble reach almacén`, `reach truck eléctrico Crown` |
| **Apiladores** | `apilador eléctrico colombia`, `apilador semieléctrico bogotá` | `apilador compacto bodega pequeña`, `apilador sin batería manual`, `apilador montado eléctrico` |
| **Transpaletas** | `transpaleta eléctrica colombia`, `zorra eléctrica bogotá` | `transpaleta con báscula`, `transpaleta bajo perfil cámara frío`, `transpaleta manual con ruedas tándem` |
| **Recogepedidos** | `recogepedidos colombia`, `order picker bogotá`, `picking logístico` | `recogepedidos alto nivel Yale`, `order picker centro de distribución`, `picking combinado` |
| **Estibadores** | `estibador manual colombia`, `estibador hidráulico bogotá` | `estibador tijera bajo costo`, `estibador manual sin motor`, `estibador económico bodega` |
| **Manlift** | `manlift colombia`, `plataforma elevadora bogotá`, `alquiler tijera eléctrica` | `brazo articulado JLG colombia`, `tijera eléctrica construcción`, `Genie telescópico alquiler colombia` |

---

### 3.3 Repuestos — Palabras clave por categoría técnica

| Categoría | Palabras clave principales |
|---|---|
| Sistema Eléctrico | `repuestos eléctricos montacargas`, `arnés montacargas`, `tablero eléctrico montacargas` |
| Sistema Hidráulico | `cilindros hidráulicos montacargas`, `sellos hidráulicos montacargas`, `bomba hidráulica montacargas` |
| Partes de Motor | `repuestos motor montacargas`, `partes motor GLP montacargas`, `bujías montacargas` |
| Llantas | `llantas montacargas bogotá`, `caucho sólido montacargas`, `neumáticos industriales colombia` |
| Filtros | `filtros montacargas colombia`, `filtro aceite montacargas`, `filtro hidráulico montacargas` |
| Sistema de Frenos | `frenos montacargas`, `pastillas freno montacargas`, `freno de mano montacargas` |
| Lubricantes | `aceite montacargas`, `lubricante transmisión montacargas`, `grasa industrial montacargas` |
| Baterías y Accesorios | `batería montacargas repuesto`, `cargador batería montacargas`, `electrolito batería industrial` |

---

### 3.4 Servicios técnicos — Palabras clave

| Servicio | Palabras clave principales | Long tail |
|---|---|---|
| Mantenimiento preventivo | `mantenimiento preventivo montacargas`, `contrato mantenimiento montacargas colombia` | `plan mantenimiento montacargas mensual`, `checklist mantenimiento montacargas` |
| Mantenimiento correctivo | `reparación montacargas bogotá`, `taller montacargas colombia` | `reparación montacargas urgente`, `técnico montacargas a domicilio` |
| Mantenimiento predictivo | `diagnóstico montacargas`, `análisis vibraciones montacargas` | `mantenimiento predictivo flota montacargas` |
| Emergencias 24/7 | `reparación montacargas emergencia`, `servicio técnico 24 horas montacargas` | `montacargas dañado urgente colombia` |
| Overhaul | `overhaul montacargas colombia`, `reacondicionamiento montacargas bogotá` | `reconstrucción motor montacargas`, `overhaul transmisión montacargas` |

---

### 3.5 Energía — Palabras clave

| Subsección | Palabras clave principales | Long tail |
|---|---|---|
| **Baterías (general)** | `baterías para montacargas`, `batería tracción industrial colombia` | `batería montacargas eléctrico 48v`, `batería industrial bogotá` |
| **Baterías plomo** | `batería plomo ácido montacargas`, `batería tubular montacargas` | `batería gel montacargas cámara frío`, `batería AGM montacargas sin mantenimiento` |
| **Baterías litio** | `batería litio montacargas`, `Li-Ion montacargas colombia` | `batería litio carga rápida montacargas`, `conversión litio montacargas eléctrico` |
| **Cargadores Renma** | `cargador Renma colombia`, `cargador industrial Renma` | `cargador Renma 48v montacargas`, `cargador Renma plomo litio` |
| **Cargadores PowerAnderson** | `cargador PowerAnderson colombia`, `conector Anderson montacargas` | `cargador PowerAnderson eficiencia alta`, `cargador Anderson 80v montacargas` |

---

### 3.6 Palabras clave de marca y SEO local

Estas deben aparecer de forma natural en los textos del sitio, especialmente en el home, la página Nosotros y el footer.

| Tipo | Palabras clave |
|---|---|
| **Marca propia** | `tecnimontacargas`, `tecni montacargas`, `TMD dual`, `tecnimontacargas dual` |
| **SEO local Bogotá** | `montacargas bogotá`, `servicio técnico montacargas bogotá`, `venta montacargas bogotá DC` |
| **SEO local Colombia** | `montacargas colombia`, `alquiler montacargas colombia`, `repuestos montacargas colombia` |
| **Marcas aliadas** | `montacargas Toyota colombia`, `Hyster colombia`, `Yale colombia`, `Crown colombia`, `JLG colombia`, `Genie colombia` |

---

## 4. DATOS DE NEGOCIO PARA SEO LOCAL

Estos datos deben estar presentes de forma consistente en el sitio (footer, página de contacto y Schema.org) para fortalecer el posicionamiento local en Google.

| Campo | Valor |
|---|---|
| **Razón social** | TECNIMONTACARGAS DUAL LTDA |
| **Nombre comercial** | Tecnimontacargas / Tecni Montacargas |
| **Teléfono 1** | 601 256 2256 |
| **Teléfono 2** | 324 429 8326 |
| **Email** | consultor1@tmdual.com |
| **Dirección** | Carrera 108 # 22F-21, Bogotá D.C., Colombia |
| **Horario de atención** | Lunes a Viernes: 8:00 AM – 6:00 PM |
| **Área de cobertura** | Bogotá y cobertura nacional en Colombia |

### Schema.org — LocalBusiness (implementar en el `<head>` del home y contacto)

```json
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "Tecnimontacargas Dual",
  "url": "https://tecnimontacargas.com",
  "logo": "https://tecnimontacargas.com/images/logos/logo.svg",
  "image": "https://tecnimontacargas.com/images/og-default.jpg",
  "description": "Especialistas en venta, alquiler y servicio técnico de montacargas y plataformas elevadoras en Colombia. Más de 20 años de experiencia.",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Carrera 108 # 22F-21",
    "addressLocality": "Bogotá",
    "addressRegion": "Bogotá D.C.",
    "addressCountry": "CO"
  },
  "telephone": "+576012562256",
  "email": "consultor1@tmdual.com",
  "openingHoursSpecification": {
    "@type": "OpeningHoursSpecification",
    "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday"],
    "opens": "08:00",
    "closes": "18:00"
  },
  "sameAs": [
    "https://www.linkedin.com/company/tecnimontacargas"
  ]
}
```

> **Nota:** Reemplazar la URL de LinkedIn con la URL real de la página de empresa de TM (pendiente de confirmar — ver checklist SID-001 del documento de requerimientos principal).

---

## 5. CRITERIOS DE ACEPTACIÓN SEO

Estos criterios se verifican durante la fase de QA (Semana 10 del plan de implementación) antes del lanzamiento.

### Meta tags

- [ ] Cada URL del sitio tiene un `<title>` único de entre 50 y 60 caracteres
- [ ] Cada URL tiene una `meta description` única de entre 140 y 160 caracteres
- [ ] Ningún title ni description está duplicado entre páginas
- [ ] Las páginas de ficha de equipo (`/equipos/[slug]`) tienen title y description generados dinámicamente desde los campos del producto en WordPress
- [ ] Los artículos del blog tienen title y description editables desde Rank Math / Yoast por el equipo de contenido
- [ ] Las imágenes tienen atributo `alt` descriptivo en todas las páginas

### Open Graph y redes sociales

- [ ] Todas las páginas tienen `og:title`, `og:description` y `og:image` correctamente configurados
- [ ] La imagen OG por defecto tiene dimensiones mínimas de 1200×630px
- [ ] Las páginas de equipos y artículos de blog generan su propia imagen OG (o usan la foto principal del producto/artículo)

### SEO técnico

- [ ] `<html lang="es">` en todas las páginas
- [ ] Tag `canonical` presente en todas las páginas y apuntando a la URL canónica correcta
- [ ] Las URLs con parámetros de filtro (`?tipo=`, `?modo=`) tienen canonical apuntando a la URL base cuando corresponde
- [ ] `meta name="robots" content="index, follow"` en páginas públicas
- [ ] Las páginas de checkout, carrito y administración de WordPress tienen `noindex` configurado
- [ ] Sitemap XML generado automáticamente e incluye todas las URLs indexables
- [ ] El Schema.org de LocalBusiness está validado en [schema.org/validator](https://validator.schema.org)
- [ ] Favicon y App icon configurados (ver especificaciones de marca, sección 2 del documento principal)
- [ ] Lighthouse SEO score ≥ 90 en home, páginas de tipo de equipo y servicios

### Palabras clave en contenido (checklist de redacción)

- [ ] El H1 del home incluye las palabras `montacargas` y `Colombia`
- [ ] Cada página de la biblioteca (`/equipos/que-es/[tipo]`) incluye la palabra clave principal del tipo en el H1, primer párrafo y al menos un H2
- [ ] Las páginas de categoría de repuestos incluyen la palabra clave de la categoría en el H1 y la description
- [ ] Los alt text de imágenes de equipos siguen el patrón: `[Marca] [Modelo] [Subtipo] — Tecnimontacargas`

---

*Documento complementario al Documento de Requerimientos Tecni Montacargas v6.6*  
*Versión SEO: 1.0 — 25 de febrero de 2026*