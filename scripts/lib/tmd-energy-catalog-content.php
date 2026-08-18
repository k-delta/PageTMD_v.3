<?php
/**
 * Transformación pura que conserva únicamente el bloque del catálogo de Energía.
 */

if (! function_exists('tmd_energy_catalog_extract_block')) {
    function tmd_energy_catalog_extract_block($content, &$error) {
        $content = (string) $content;
        $pattern = '/<!--\s*(\/?)wp:([a-z0-9_-]+(?:\/[a-z0-9_-]+)?)(?:\s+\{[^\r\n]*\})?\s*(\/?)-->/i';
        $count   = preg_match_all($pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        if (false === $count) {
            $error = 'No se pudo analizar el contenido Gutenberg de Energía.';
            return null;
        }

        $start_index = null;
        foreach ($matches as $index => $match) {
            if ('' === $match[1][0] && false !== strpos($match[0][0], '"className":"tmde-section"')) {
                if (null !== $start_index) {
                    $error = 'Se encontró más de un bloque tmde-section.';
                    return null;
                }
                $start_index = $index;
            }
        }

        if (null === $start_index) {
            $error = 'No se encontró el bloque tmde-section del catálogo.';
            return null;
        }

        $stack = [];
        $start = $matches[$start_index][0][1];
        foreach (array_slice($matches, $start_index) as $match) {
            $closing      = '/' === $match[1][0];
            $self_closing = '/' === $match[3][0];
            $block_name   = strtolower($match[2][0]);

            if ($closing) {
                if (empty($stack) || end($stack) !== $block_name) {
                    $error = "El cierre {$block_name} no corresponde con la estructura abierta.";
                    return null;
                }
                array_pop($stack);
            } elseif (! $self_closing) {
                $stack[] = $block_name;
            }

            if (empty($stack)) {
                $end = $match[0][1] + strlen($match[0][0]);
                return substr($content, $start, $end - $start);
            }
        }

        $error = 'El bloque tmde-section no tiene un cierre válido.';
        return null;
    }
}

if (! function_exists('tmd_transform_energy_catalog_content')) {
    function tmd_transform_energy_catalog_content($content) {
        $original = (string) $content;
        $error    = '';
        $catalog  = tmd_energy_catalog_extract_block($original, $error);

        if (null === $catalog) {
            return ['content' => $original, 'changes' => [], 'errors' => [$error]];
        }

        foreach (['[tmd_energy_filters', '[tmd_energy_grid'] as $required) {
            if (false === strpos($catalog, $required)) {
                return [
                    'content' => $original,
                    'changes' => [],
                    'errors'  => ["El catálogo no contiene {$required}."],
                ];
            }
        }

        if (trim($original) === trim($catalog)) {
            return ['content' => $original, 'changes' => [], 'errors' => []];
        }

        return [
            'content' => $catalog . "\n",
            'changes' => ['catalog-only'],
            'errors'  => [],
        ];
    }
}
