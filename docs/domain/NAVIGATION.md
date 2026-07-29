# Navegación y componentes

## Menú principal

Secciones principales:

- INICIO
- EQUIPOS
- ENERGÍA
- SERVICIOS
- NOSOTROS

Verificar el menú activo antes de modificar su distribución interna.

## Mega menú

Debe:

- Abrir con hover, foco o clic.
- Cerrar con Escape o clic exterior.
- Mantenerse abierto durante interacción interna.
- Ser navegable con teclado.

## Responsive

Validar los cambios en escritorio y móvil. No debe existir overflow horizontal.

## Header y footer

- Aplicar parches focalizados.
- No reemplazar el componente completo sin una tarea específica.
- Conservar markup y clases cuando otros estilos o scripts dependan de ellos.

## Hero

El comportamiento de video o multimedia debe verificarse en Chrome, Safari y móvil cuando aplique.

No modificar atributos persistidos en `post_content` sin revisar el contenido almacenado.

## Redirecciones conocidas

| Ruta | Comportamiento | Motivo |
|---|---|---|
| `/bms/` | 301 | Ruta canónica en energía |
| `/nosotros/` | 301 | Ruta canónica de quiénes somos |
| `/tienda/` | 302 | Comercio pausado |
| `/carrito/` | 302 | Comercio pausado |
| `/finalizar-compra/` | 302 | Comercio pausado |
| `/repuestos/` | 404 | Sección no publicada |

Verificar el comportamiento real antes de cambiar esta tabla.
