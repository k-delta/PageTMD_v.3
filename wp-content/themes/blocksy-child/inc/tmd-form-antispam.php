<?php
/**
 * Protección focalizada para formularios públicos.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('tmd_form_antispam_is_quoted_user_agent')) {
    function tmd_form_antispam_is_quoted_user_agent($user_agent) {
        $user_agent = trim((string) $user_agent);

        return strlen($user_agent) >= 2
            && '"' === $user_agent[0]
            && '"' === $user_agent[strlen($user_agent) - 1];
    }
}

if (! function_exists('tmd_form_antispam_should_block')) {
    function tmd_form_antispam_should_block($server = null) {
        $server = is_array($server) ? $server : $_SERVER;
        $user_agent = isset($server['HTTP_USER_AGENT'])
            ? wp_unslash((string) $server['HTTP_USER_AGENT'])
            : '';

        return tmd_form_antispam_is_quoted_user_agent($user_agent);
    }
}

if (! function_exists('tmd_form_antispam_cf7_before_send_mail')) {
    function tmd_form_antispam_cf7_before_send_mail($contact_form, &$abort, $submission) {
        if (
            ! is_object($contact_form)
            || ! method_exists($contact_form, 'id')
            || 14 !== (int) $contact_form->id()
            || ! tmd_form_antispam_should_block()
        ) {
            return;
        }

        $abort = true;

        if (is_object($submission) && method_exists($submission, 'add_spam_log')) {
            $submission->add_spam_log([
                'agent'  => 'tmd-form-antispam',
                'reason' => 'Malformed quoted user agent.',
            ]);
        }
    }
}

add_action('wpcf7_before_send_mail', 'tmd_form_antispam_cf7_before_send_mail', 10, 3);
