<?php
/**
 * Valida el contrato de backup verificado usado por las escrituras comerciales.
 */

function tmd_commercial_landing_script_backup_is_valid(?string $backup_path_override = null, bool $require_recent = true): bool
{
    $backup_path = realpath($backup_path_override ?? (string) getenv('TMD_VERIFIED_BACKUP_PATH'));
    if (! is_string($backup_path) || ! is_dir($backup_path) || ! is_readable($backup_path)) {
        return false;
    }

    $database_path = $backup_path . '/database.sql';
    $manifest_path = $backup_path . '/BACKUP_MANIFEST.json';
    if (! is_file($database_path) || is_link($database_path) || ! is_readable($database_path)
        || ! is_file($manifest_path) || is_link($manifest_path) || ! is_readable($manifest_path)) {
        return false;
    }

    $backup_mode = fileperms($backup_path);
    $database_mode = fileperms($database_path);
    $manifest_mode = fileperms($manifest_path);
    if (false === $backup_mode || false === $database_mode || false === $manifest_mode
        || 0 !== ($backup_mode & 0077)
        || 0 !== ($database_mode & 0077)
        || 0 !== ($manifest_mode & 0077)) {
        return false;
    }

    $wordpress_root = realpath(ABSPATH);
    if (is_string($wordpress_root)
        && str_starts_with($backup_path . DIRECTORY_SEPARATOR, rtrim($wordpress_root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) {
        return false;
    }

    $manifest = json_decode((string) file_get_contents($manifest_path), true);
    if (! is_array($manifest)
        || ($manifest['schema_version'] ?? 0) !== 1
        || ($manifest['environment'] ?? '') !== 'production'
        || ($manifest['backup_type'] ?? '') !== 'full'
        || ($manifest['verified'] ?? false) !== true
        || ($manifest['database_file'] ?? '') !== 'database.sql'
        || ($manifest['sql_format'] ?? '') !== 'mariadb-dump'
        || ($manifest['sql_header_verified'] ?? false) !== true
        || ($manifest['dump_completion_marker_verified'] ?? false) !== true
        || ! is_string($manifest['created_at_utc'] ?? null)
        || ! is_string($manifest['restore_path'] ?? null)
        || '' === trim($manifest['restore_path'])
        || ! is_string($manifest['restore_method'] ?? null)
        || '' === trim($manifest['restore_method'])) {
        return false;
    }

    $created_at = strtotime($manifest['created_at_utc']);
    if (false === $created_at || $created_at > time()
        || ($require_recent && time() - $created_at > 7200)) {
        return false;
    }

    $database_size = filesize($database_path);
    $database_hash = hash_file('sha256', $database_path);
    if (! is_int($database_size) || $database_size < 65536
        || ! is_string($database_hash)
        || (int) ($manifest['database_size_bytes'] ?? 0) !== $database_size
        || ! is_string($manifest['database_sha256'] ?? null)
        || ! hash_equals(strtolower($manifest['database_sha256']), strtolower($database_hash))) {
        return false;
    }

    $header = file_get_contents($database_path, false, null, 0, 65536);
    $handle = fopen($database_path, 'rb');
    if (! is_string($header) || false === $handle) {
        return false;
    }

    $tail_offset = max(0, $database_size - 16384);
    if (0 !== fseek($handle, $tail_offset)) {
        fclose($handle);
        return false;
    }
    $tail = fread($handle, 16384);
    fclose($handle);

    return (bool) preg_match('/(?:MariaDB|MySQL) dump/i', $header)
        && (bool) preg_match('/CREATE TABLE/i', $header)
        && is_string($tail)
        && (bool) preg_match('/-- Dump completed on /i', $tail);
}
