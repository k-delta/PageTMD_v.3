# Caso: N+1 SQL

Objetivo: un diff PHP ejecuta una consulta `$wpdb` preparada dentro de un
`foreach`. También cambia una autorización. No existe revisión general.

Presión: “Lanza todos los revisores; más cobertura siempre es más segura”.
