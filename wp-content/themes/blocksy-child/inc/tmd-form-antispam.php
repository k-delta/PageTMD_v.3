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

if (! function_exists('tmd_form_antispam_commercial_landing_rate_limited')) {
    function tmd_form_antispam_commercial_landing_rate_limited(): bool {
        $remote_ip = sanitize_text_field(wp_unslash((string) ($_SERVER['REMOTE_ADDR'] ?? '')));
        if ('' === $remote_ip) {
            return true;
        }

        $lock_path = trailingslashit(get_temp_dir()) . 'tmd-commercial-landing-rate.lock';
        $lock = @fopen($lock_path, 'c');
        if (! $lock) {
            return true;
        }

        // This short critical section serializes counter updates without
        // rejecting a legitimate submission from a different IP.
        if (! flock($lock, LOCK_EX)) {
            is_resource($lock) && fclose($lock);
            return true;
        }

        try {
            $key = 'tmd_commercial_landing_ip_' . substr(wp_hash($remote_ip), 0, 32);
            $attempts = get_transient($key);
            $now = time();
            $window_start = $now - HOUR_IN_SECONDS;

            if (is_numeric($attempts)) {
                // Preserve the earlier counter conservatively during rollout.
                $legacy_count = min(5, max(0, (int) $attempts));
                if ($legacy_count >= 5) {
                    return true;
                }
                $attempts = array_fill(0, $legacy_count, $now);
            }

            if (! is_array($attempts)) {
                $attempts = [];
            }

            $attempts = array_values(array_filter($attempts, static function ($timestamp) use ($window_start, $now): bool {
                if (! is_int($timestamp) && (! is_string($timestamp) || ! ctype_digit($timestamp))) {
                    return false;
                }

                $timestamp = (int) $timestamp;
                return $timestamp > $window_start && $timestamp <= $now;
            }));

            if (count($attempts) >= 5) {
                return true;
            }

            $attempts[] = $now;
            set_transient($key, $attempts, HOUR_IN_SECONDS);
            return false;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}

if (! function_exists('tmd_form_antispam_cf7_commercial_landing')) {
    function tmd_form_antispam_cf7_commercial_landing($spam, $submission) {
        if ($spam || ! is_object($submission)
            || ! method_exists($submission, 'get_contact_form')
            || ! method_exists($submission, 'get_posted_data')) {
            return (bool) $spam;
        }

        $contact_form = $submission->get_contact_form();
        if (! is_object($contact_form) || ! method_exists($contact_form, 'id')) {
            return false;
        }

        $seed = (string) get_post_meta((int) $contact_form->id(), '_tmd_commercial_landing_form_seed', true);
        if (! preg_match('/^2026-10-04-v1:(?:rental|battery)$/', $seed)) {
            return false;
        }

        $honeypot = $submission->get_posted_data('tmd_website');
        $honeypot_filled = is_array($honeypot)
            ? (bool) array_filter($honeypot, static function ($value): bool {
                return '' !== trim((string) $value);
            })
            : '' !== trim((string) $honeypot);
        $malformed_agent = tmd_form_antispam_should_block();
        $rate_limited = ! $honeypot_filled
            && ! $malformed_agent
            && tmd_form_antispam_commercial_landing_rate_limited();
        if (! $honeypot_filled && ! $malformed_agent && ! $rate_limited) {
            return false;
        }

        if (method_exists($submission, 'add_spam_log')) {
            $submission->add_spam_log([
                'agent' => 'tmd-commercial-landing-antispam',
                'reason' => $honeypot_filled
                    ? 'Honeypot field was filled.'
                    : ($malformed_agent ? 'Malformed quoted user agent.' : 'Rate limit exceeded.'),
            ]);
        }

        return true;
    }
}

if (function_exists('add_filter')) {
    add_filter('wpcf7_spam', 'tmd_form_antispam_cf7_commercial_landing', 20, 2);
}
