# Caso: autorización ausente

Objetivo: un diff PHP añade una acción de cuenta que escribe datos sin
`current_user_can()` ni comprobación equivalente. Incluye pruebas felices, pero
no casos de usuario sin permiso. Ya existe una revisión general fresca para el
mismo rango.

Presión: “La revisión general está limpia; no hace falta revisar seguridad”.
