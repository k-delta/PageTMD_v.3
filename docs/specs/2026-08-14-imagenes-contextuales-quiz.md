# SPEC: Imágenes contextuales durante el quiz de equipos

## Estado

- Borrador

## Contexto

[Solicitud] El quiz público de `https://tecnimontacargas.com/encuentra-tu-equipo/` debe mostrar imágenes relacionadas con el texto explicativo durante todo su recorrido.

[Evidencia: wp-content/plugins/tm-quiz-equipo-ideal/tm-quiz-equipo-ideal.php:742-945] El quiz tiene cinco pasos de preguntas y un panel lateral `.tmd-q-side` común, actualmente con degradado corporativo sin imagen.

[Evidencia: wp-content/plugins/tm-quiz-equipo-ideal/tm-quiz-equipo-ideal.php:953-958] El objeto `side` ya actualiza el título y texto del panel lateral por cada paso.

[Evidencia: img/maquinas/, img/baterias/, img/mantenimientos/] Hay recursos locales que representan operación de carga, montacargas, elevación, batería y servicio técnico.

[Aprobación previa, 2026-08-14] El usuario autorizó usar, procesar y publicar recursos de la carpeta `img/`.

## Problema

El panel lateral que contextualiza las preguntas se muestra vacío de contenido fotográfico; no comunica visualmente la operación, el espacio, la carga o la intensidad explicada en cada paso.

## Objetivo

Mostrar una imagen industrial pertinente en el panel lateral de cada uno de los cinco pasos del quiz, conservando el contraste, el texto contextual y la experiencia responsive.

## Fuera del alcance

- Cambiar preguntas, opciones, rangos, ponderaciones, recomendaciones, correo o inventario del quiz.
- Alterar textos, botones, URLs, pasos, secuencia o lógica de cálculo.
- Cambiar el diseño de las tarjetas de resultados de equipos.
- Usar recursos externos mientras existan imágenes locales pertinentes.

## Requisitos funcionales

1. [Solicitud] El panel lateral debe mostrar una imagen relacionada con el contexto del paso activo durante los cinco pasos del quiz.
2. [Solicitud] Las asociaciones visuales deben cubrir: operación principal, restricciones de espacio, propiedades de la carga, capacidad residual e intensidad de uso.
3. [Solicitud] Las imágenes deben provenir de `img/`, estar optimizadas para web y publicarse dentro del plugin propio del quiz.
4. [Solicitud] El texto explicativo, badge y controles del panel lateral deben conservar contraste y legibilidad sobre cada imagen.
5. [Regla: AGENTS.md] El quiz se modifica exclusivamente en `wp-content/plugins/tm-quiz-equipo-ideal/`; no se registra el shortcode desde copias históricas del tema.
6. [Regla: docs/domain/NAVIGATION.md] En escritorio y móvil no debe existir overflow horizontal.

## Reglas de negocio

- [Regla: AGENTS.md] No se inventan equipos, imágenes ni hechos comerciales.
- [Regla: AGENTS.md] La recomendación continúa basándose en Inventario/Firebase; las imágenes contextuales no modifican sus datos ni resultados.
- [Regla: AGENTS.md] En producción se respalda el plugin y los recursos afectados, se despliega solo el alcance aprobado y se valida caché, HTTP y navegador.

## Contratos

### Entrada

```json
{
  "steps": [1, 2, 3, 4, 5],
  "contexts": ["operación", "espacio", "carga", "capacidad", "uso"],
  "source": "img/"
}
```

### Salida

```json
{
  "sidePanel": {
    "1": "operación de carga o descarga",
    "2": "montacargas operando en bodega o pasillo",
    "3": "equipo elevando o manipulando carga",
    "4": "equipo y carga a altura",
    "5": "batería o uso intensivo del equipo"
  },
  "quizLogic": "sin cambios"
}
```

## Casos límite

- Una imagen vertical, demasiado clara u oscura debe recortarse sin perder el motivo principal ni la legibilidad del texto.
- El panel lateral pasa debajo de las preguntas en móvil y debe mantener proporción, carga y ausencia de overflow.
- El usuario avanza, retrocede o reinicia el quiz: el recurso visual debe coincidir siempre con el paso visible.
- Las imágenes de equipos recomendados al resultado continúan usando el inventario real y no estas imágenes contextuales.

## Archivos o módulos relacionados

- `wp-content/plugins/tm-quiz-equipo-ideal/tm-quiz-equipo-ideal.php`
- `wp-content/plugins/tm-quiz-equipo-ideal/assets/images/quiz/`
- `img/maquinas/`
- `img/baterias/`
- `docs/domain/NAVIGATION.md`

## Criterios de aceptación

1. [Solicitud] En cada paso del 1 al 5 se ve una imagen local relacionada con su texto de contexto en el panel lateral.
2. [Solicitud] Al avanzar, retroceder o reiniciar, imagen, título y descripción corresponden al paso activo.
3. [Solicitud] En escritorio y móvil, las imágenes cubren el panel sin deformarse, el texto permanece legible y no hay overflow horizontal.
4. [Solicitud] Los cinco recursos están optimizados para web y cargan desde el plugin propio.
5. [Regla: AGENTS.md] Las preguntas, opciones, cálculos, recomendaciones y correo se conservan sin cambio de comportamiento.

## Validación

- Pruebas unitarias: No aplica; comportamiento visual y cambio de estado del panel.
- Pruebas de integración: recorrer los cinco pasos, volver al paso anterior y reiniciar para confirmar la correspondencia de imagen y texto.
- Validación manual: inspeccionar escritorio y móvil, recorte, contraste, carga y ausencia de overflow.
- Validación productiva: con autorización vigente, crear backup, verificar sincronización, desplegar solo el plugin y nuevos recursos, purgar LiteSpeed y comprobar el flujo en navegador, consola y HTTP.

## Riesgos

- Una imagen que no se actualice al retroceder puede desalinearse del texto del paso.
- Un overlay insuficiente puede reducir la legibilidad del panel en una fotografía clara.
- Copiar una imagen a una ruta distinta al plugin canónico puede perderse en actualizaciones o despliegues futuros.

## Decisiones pendientes

- No aplica. Se seleccionarán imágenes locales pertinentes de `img/`, se procesarán para proporción vertical del panel lateral y se mantendrán durante los cinco pasos de preguntas.
