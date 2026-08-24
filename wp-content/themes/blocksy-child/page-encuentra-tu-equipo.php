<?php
/**
 * Page-specific presentation for /encuentra-tu-equipo/.
 *
 * Keeps Blocksy's normal page rendering while removing the redundant
 * visible WordPress page title above the quiz.
 */

defined('ABSPATH') || exit;

add_action('wp_head', static function () {
    ?>
    <style id="tmd-quiz-page-title-fix">
        .entry-header,
        .ct-page-title,
        .ct-hero-section,
        h1.entry-title,
        .page-title {
            display: none !important;
        }

        .tmd-quiz-v3 .tmd-q-option {
            display: flex;
        }

        .tmd-quiz-v3 .tmd-q-card {
            width: 100%;
            min-height: 200px;
            height: 100%;
            padding: 28px 24px;
        }
    </style>
    <?php
}, 99);

require get_template_directory() . '/page.php';
