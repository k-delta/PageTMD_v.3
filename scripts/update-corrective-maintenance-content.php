<?php
/** Idempotent content migration for Mantenimiento correctivo (page 290). */
function tmd_corr_replace(string $content, string $label, string $old, string $new, array &$changes, array &$errors): string {
    $oc = substr_count($content, $old);
    $nc = substr_count($content, $new);
    if (1 === $oc) {
        $content = str_replace($old, $new, $content, $count);
        if (1 !== $count) {
            $errors[] = sprintf('%s: reemplazos=%d.', $label, $count);
            return $content;
        }
        $changes[] = $label;
        return $content;
    }
    if (0 === $oc && $nc >= 1) {
        return $content;
    }
    $errors[] = sprintf('%s: precondicion invalida (anterior=%d, nuevo=%d).', $label, $oc, $nc);
    return $content;
}

function tmd_transform_corrective_maintenance_content(string $content): array {
    $original = $content;
    $changes = [];
    $errors = [];
    $pairs = [
        ['hero-titulo', 'Diagnostique la causa de la falla antes de cambiar componentes', 'Diagnostique la causa antes de cambiar componentes'],
        ['hero-descripcion', 'Cuando un montacargas se detiene, pierde rendimiento o presenta una alarma, el primer paso es establecer qué sistema origina el problema. El mantenimiento correctivo busca reparar con un alcance definido y comprobar el funcionamiento antes de devolver el equipo a la operación.', 'Cuando un montacargas presenta una falla, pérdida de rendimiento o una alarma, es importante identificar primero el origen del problema.</p>\n    <p class="tmd-maint-hero__lead">Un diagnóstico técnico permite determinar la intervención necesaria, evitar cambios innecesarios de componentes y realizar pruebas de funcionamiento antes de devolver el equipo a la operación.'],
        ['nav-repetida', "  <nav class=\"tmd-maint-service-nav\" aria-label=\"Páginas de mantenimiento\">\n    <a href=\"/mantenimiento/\">Mantenimiento</a>\n    <a href=\"/mantenimiento/mantenimiento-preventivo/\">Preventivo</a>\n    <a href=\"/mantenimiento/mantenimiento-correctivo/\" aria-current=\"page\">Correctivo</a>\n  </nav>", '  <!-- tmd-corrective-service-nav-removed -->'],
        ['senales-marcador', "  <section class=\"tmd-maint-section\">\n    <header class=\"tmd-maint-section__header\">\n      <p class=\"tmd-maint-eyebrow\">Cuándo solicitarlo</p>\n      <h2>Señales que requieren diagnóstico técnico</h2>", "  <section class=\"tmd-maint-section tmd-corrective-signals\">\n    <header class=\"tmd-maint-section__header\">\n      <p class=\"tmd-maint-eyebrow\">Cuando solicitarlo</p>\n      <h2>Señales que requieren diagnóstico técnico</h2>"],
        ['senales-descripcion', 'Si una condición afecta la seguridad, la estabilidad o el control, el equipo debe inmovilizarse y revisarse antes de continuar operando.', 'Cuando una falla compromete la seguridad, estabilidad o control del montacargas, el equipo debe retirarse de operación y someterse a una revisión técnica antes de continuar trabajando.'],
        ['senal-funcional', 'Interrupciones completas, fallas intermitentes, pérdida de tracción o imposibilidad de ejecutar una función.', 'Fallas de encendido, paradas inesperadas, funcionamiento intermitente, pérdida de tracción o imposibilidad de ejecutar alguna función.'],
        ['senal-desempeno', 'Cambios notorios en elevación, desplazamiento, capacidad de trabajo o duración de la batería.', 'Disminución evidente en la elevación, desplazamiento, rendimiento operativo o autonomía de la batería.'],
        ['senal-alerta', 'Códigos recurrentes, calentamiento, sonidos anormales, olor, vibración o presencia de fluido.', 'Códigos de falla recurrentes, calentamiento anormal, ruidos, vibraciones, olores inusuales o presencia de fugas.'],
        ['nota-seguridad', '<strong>Prioridad de seguridad:</strong> no continúe utilizando un montacargas que pierda control, estabilidad, frenado o capacidad de sostener la carga. Registre la condición y solicite revisión.', '<strong>Prioridad de seguridad:</strong> No continúe operando un montacargas que presente pérdida de control, estabilidad, capacidad de frenado o dificultad para sostener la carga. Retire el equipo de operación y solicite una revisión técnica.'],
        ['diagnostico-marcador', "  <section class=\"tmd-maint-section tmd-maint-split\">\n    <div>\n      <p class=\"tmd-maint-eyebrow\">Diagnóstico por sistemas</p>", "  <section class=\"tmd-maint-section tmd-maint-split tmd-corrective-systems\">\n    <div>\n      <p class=\"tmd-maint-eyebrow\">Diagnóstico por sistemas</p>"],
        ['diagnostico-titulo', 'La misma señal puede tener causas diferentes', 'Una misma señal puede tener diferentes causas'],
        ['diagnostico-texto', 'Cambiar una pieza sin comprobar la causa puede aumentar costos y dejar la falla activa. La revisión debe relacionar síntomas, códigos, mediciones y pruebas funcionales.', 'Reemplazar un componente sin identificar el origen de la falla puede generar costos innecesarios y no solucionar el problema. El diagnóstico debe relacionar síntomas, códigos de falla, mediciones y pruebas de funcionamiento.'],
        ['sistema-electrico', 'Alimentación, cableado, mandos, contactores, sensores, controladores y alarmas.', 'Alimentación eléctrica, cableado, mandos, contactores, sensores, controladores y sistemas de alarma.'],
        ['sistema-hidraulico', '<strong>Hidráulico</strong><span>Fugas, presión, bombas, válvulas, cilindros, conexiones y pérdida de capacidad.</span>', '<strong>Sistema hidráulico</strong><span>Fugas, presión, bombas, válvulas, cilindros, conexiones y desempeño de las funciones hidráulicas.</span>'],
        ['sistema-mecanico', '<strong>Mecánico</strong><span>Frenos, dirección, ruedas, rodamientos, transmisión, mástil, cadenas y elementos móviles.</span>', '<strong>Sistema mecánico</strong><span>Frenos, dirección, ruedas, rodamientos, transmisión, mástil, cadenas y componentes móviles.</span>'],
        ['sistema-energia', '<strong>Energía y carga</strong><span>Batería, conectores, cargador, autonomía y comportamiento durante carga y descarga.</span>', '<strong>Batería y sistema de carga</strong><span>Batería, conectores, cargador, autonomía y comportamiento durante los ciclos de carga y operación.</span>'],
        ['proceso-etiqueta', '<p class="tmd-maint-eyebrow">Proceso correctivo</p>', '<p class="tmd-maint-eyebrow">Proceso de mantenimiento correctivo</p>'],
        ['proceso-titulo', '<h2>Reparar con autorización y pruebas</h2>', '<h2>Diagnóstico, autorización y reparación</h2>'],
        ['proceso-1', '<h3>Reporte de la falla</h3><p>Se recopilan síntomas, momento de aparición, códigos, condiciones de uso y antecedentes.</p>', '<h3>Reporte de la falla</h3><p>Se recopila información sobre los síntomas, códigos de falla, condiciones de operación y antecedentes del equipo.</p>'],
        ['proceso-2', '<h3>Diagnóstico</h3><p>El técnico inspecciona, mide y prueba para identificar el sistema y la causa probable.</p>', '<h3>Diagnóstico técnico</h3><p>Se realizan inspecciones, mediciones y pruebas para identificar el origen de la falla y determinar la intervención requerida.</p>'],
        ['proceso-3', '<h3>Propuesta de reparación</h3><p>Se informa el alcance, los repuestos requeridos y las actividades sujetas a autorización.</p>', '<h3>Propuesta y autorización</h3><p>Se informa el alcance de la reparación, los repuestos requeridos y las actividades a realizar para su aprobación.</p>'],
        ['proceso-4', '<h3>Reparación y validación</h3><p>Se ejecuta lo autorizado, se realizan pruebas funcionales y se comunican recomendaciones.</p>', '<h3>Reparación y validación</h3><p>Se ejecutan las actividades autorizadas y se realizan pruebas de funcionamiento para verificar la correcta operación del equipo. Al finalizar, se informan las recomendaciones técnicas aplicables.</p>'],
        ['info-etiqueta', '<p class="tmd-maint-eyebrow">Información útil</p>', '<p class="tmd-maint-eyebrow">Información para solicitar el servicio</p>'],
        ['info-texto', 'Una descripción precisa no reemplaza la inspección, pero facilita preparar la visita y evita perder datos importantes sobre la falla.', 'Contar con información básica del equipo y de la falla nos permite comprender mejor la situación y preparar la atención técnica. Estos datos complementan la inspección que realizará nuestro personal.'],
        ['info-equipo-titulo', '<h3>Datos del equipo</h3>', '<h3>Información del equipo</h3>'],
        ['info-equipo-lista', "          <li>Marca, modelo y número interno o serial.</li>\n          <li>Tipo de montacargas y capacidad.</li>\n          <li>Ubicación y condiciones de acceso.</li>\n          <li>Horas aproximadas y turnos de trabajo.</li>", "          <li>Marca, modelo y número de serie o identificación interna.</li>\n          <li>Tipo de montacargas y capacidad de carga.</li>\n          <li>Ubicación del equipo y condiciones de acceso.</li>\n          <li>Horómetro y turnos aproximados de operación.</li>"],
        ['info-falla-titulo', '<h3>Datos de la falla</h3>', '<h3>Información de la falla</h3>'],
        ['info-falla-lista', "          <li>Qué función dejó de operar y cuándo ocurrió.</li>\n          <li>Códigos, alarmas o indicadores visibles.</li>\n          <li>Ruidos, fugas, calentamiento o pérdida de fuerza.</li>\n          <li>Reparaciones recientes o repetición del problema.</li>", "          <li>Descripción de la falla y momento en que se presentó.</li>\n          <li>Funciones que dejaron de operar o presentan comportamiento irregular.</li>\n          <li>Códigos de falla, alarmas o indicadores visibles.</li>\n          <li>Ruidos, fugas, calentamiento, vibraciones o pérdida de rendimiento.</li>\n          <li>Reparaciones recientes o antecedentes de la misma falla.</li>"],
        ['criterios-marcador', "  <section class=\"tmd-maint-section\">\n    <header class=\"tmd-maint-section__header\">\n      <p class=\"tmd-maint-eyebrow\">Criterios de servicio</p>\n      <h2>Qué puede esperar de una atención correctiva</h2>", "  <section class=\"tmd-maint-section tmd-corrective-criteria\">\n    <header class=\"tmd-maint-section__header\">\n      <p class=\"tmd-maint-eyebrow\">Criterios de servicio</p>\n      <h2>¿Qué puede esperar de nuestro servicio correctivo?</h2>"],
        ['criterio-1', '<h3>Diagnóstico primero</h3><p>La intervención se orienta a la causa probable y a las pruebas realizadas.</p>', '<h3>Diagnóstico técnico</h3><p>La intervención se define a partir de la inspección, las mediciones y las pruebas realizadas para identificar el origen de la falla.</p>'],
        ['criterio-2', '<h3>Alcance informado</h3><p>Las actividades y repuestos adicionales se comunican antes de ejecutarse.</p>', '<h3>Alcance informado</h3><p>Antes de realizar trabajos adicionales, se informa el alcance de la reparación y los repuestos requeridos para su autorización.</p>'],
        ['criterio-3', '<h3>Pruebas funcionales</h3><p>El equipo se verifica dentro de condiciones seguras antes de concluir el servicio.</p>', '<h3>Pruebas de funcionamiento</h3><p>Una vez realizada la intervención, se efectúan pruebas para verificar el funcionamiento del equipo en condiciones seguras de operación.</p>'],
        ['criterio-4', '<h3>Recomendaciones</h3><p>Se indican hallazgos que requieren seguimiento preventivo o una intervención posterior.</p>', '<h3>Recomendaciones técnicas</h3><p>Al finalizar, se informan los hallazgos relevantes y las recomendaciones de mantenimiento o intervenciones que requieran seguimiento.</p>'],
        ['despues-titulo', 'Evite que la falla se repita por la misma condición de uso', 'Prevenga la repetición de la falla'],
        ['despues-texto', 'Cuando el origen está relacionado con operación, carga, ambiente o mantenimiento pendiente, la reparación debe acompañarse de acciones de seguimiento.', 'Cuando la causa de una falla está relacionada con las condiciones de operación, los ciclos de carga, el entorno de trabajo o necesidades de mantenimiento, es importante establecer acciones de seguimiento que ayuden a prevenir su recurrencia.'],
        ['despues-lista', "        <li>Registrar la falla, la causa identificada y la solución aplicada.</li>\n        <li>Revisar hábitos de operación y carga relacionados con el evento.</li>\n        <li>Programar la inspección de componentes con desgaste asociado.</li>\n        <li>Confirmar que el operador reconoce alarmas y señales críticas.</li>\n        <li>Incorporar el equipo a un plan preventivo cuando corresponda.</li>", "        <li>Registrar la falla, la causa identificada y la reparación realizada.</li>\n        <li>Revisar las condiciones de operación y los ciclos de carga relacionados con la falla.</li>\n        <li>Programar la inspección de componentes que presenten desgaste o requieran seguimiento.</li>\n        <li>Verificar que los operadores reconozcan alarmas y señales de funcionamiento anormal.</li>\n        <li>Incluir el equipo en un plan de mantenimiento preventivo cuando sea necesario.</li>"],
        ['faq-titulo', '<h2>Sobre el mantenimiento correctivo</h2>', '<h2>Preguntas frecuentes sobre mantenimiento correctivo</h2>'],
        ['faq-1', '<details><summary>¿Pueden cotizar una reparación solo con una fotografía?</summary><p>Una imagen o video ayuda a entender el síntoma, pero muchas fallas necesitan medición e inspección. La cotización definitiva depende del diagnóstico y del alcance confirmado.</p></details>', '<details><summary>¿Pueden cotizar una reparación solo con una fotografía o video?</summary><p>Puede orientar la revisión, pero la cotización definitiva depende del diagnóstico técnico.</p></details>'],
        ['faq-2', '<details><summary>¿Todo código de alarma significa que debe cambiarse una pieza?</summary><p>No necesariamente. Un código orienta la revisión, pero puede estar relacionado con conexiones, alimentación, sensores, configuración u otras condiciones que deben comprobarse.</p></details>', '<details><summary>¿Todo código de alarma significa que debe cambiarse un componente?</summary><p>No. El código orienta el diagnóstico, pero es necesario verificar la causa antes de reemplazar componentes.</p></details>'],
        ['faq-3', '<details><summary>¿Qué pasa si durante la reparación aparece otra falla?</summary><p>El nuevo hallazgo debe informarse con su impacto y alcance. No deberían ejecutarse actividades adicionales sin la autorización correspondiente.</p></details>', '<details><summary>¿Qué sucede si durante la reparación se identifica otra falla?</summary><p>Se informa el hallazgo y cualquier trabajo adicional se realiza con autorización del cliente.</p></details>'],
        ['faq-4', '<details><summary>¿Cuándo puede regresar el equipo a operación?</summary><p>Después de completar la reparación autorizada, realizar las pruebas aplicables y confirmar que las funciones intervenidas responden de manera adecuada.</p></details>', '<details><summary>¿Cuándo puede regresar el equipo a operación?</summary><p>Después de finalizar la reparación y verificar su funcionamiento mediante las pruebas correspondientes.</p></details>'],
    ];
    foreach ($pairs as [$label, $old, $new]) {
        $content = tmd_corr_replace($content, $label, $old, $new, $changes, $errors);
    }
    return ['content' => $content, 'changes' => $changes, 'errors' => $errors, 'changed' => $content !== $original];
}

if (! defined('WP_CLI') || ! WP_CLI) { return; }
$page_id = 290;
$page = get_post($page_id);
if (! $page || 'page' !== $page->post_type) { WP_CLI::error("No existe la página de Mantenimiento correctivo esperada con ID {$page_id}."); }
$result = tmd_transform_corrective_maintenance_content((string) $page->post_content);
if (! empty($result['errors'])) { WP_CLI::error("La actualización de Mantenimiento correctivo se detuvo sin escribir:\n- " . implode("\n- ", $result['errors'])); }
if (! $result['changed']) { WP_CLI::success('Mantenimiento correctivo ya contiene los cambios solicitados; no hay cambios.'); return; }
$updated_id = wp_update_post(['ID' => $page_id, 'post_content' => $result['content']], true);
if (is_wp_error($updated_id) || $page_id !== (int) $updated_id) { $message = is_wp_error($updated_id) ? $updated_id->get_error_message() : 'ID inesperado.'; WP_CLI::error('No se pudo actualizar Mantenimiento correctivo: ' . $message); }
clean_post_cache($page_id);
WP_CLI::success('Mantenimiento correctivo actualizado: ' . implode(', ', $result['changes']));
