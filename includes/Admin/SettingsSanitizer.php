<?php

class Am24h_SettingsSanitizer
{
    public function sanitize_checkbox($input): int
    {
        return (int) ($input === '1' || $input === 1);
    }

    public function sanitize_color($input): string
    {
        $color = sanitize_text_field((string) $input);

        return preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color) ? $color : '';
    }

    public function sanitize_home_categories($input): array
    {
        if (! is_array($input)) {
            return array();
        }

        $valid_ids = get_terms(
            array(
                'taxonomy'   => 'category',
                'fields'     => 'ids',
                'hide_empty' => false,
            )
        );

        if (! is_array($valid_ids)) {
            return array();
        }

        $ids = array_map('absint', $input);

        return array_values(array_intersect($ids, $valid_ids));
    }

    public function sanitize_language($input): string
    {
        $language = sanitize_text_field((string) $input);

        return in_array($language, Am24h_LanguageCatalog::codes(), true) ? $language : 'pt_BR';
    }

    public function sanitize_cookie_consent_text($input): string
    {
        $value = sanitize_text_field((string) $input);

        return substr($value, 0, 280);
    }

    public function sanitize_cookie_consent_label($input): string
    {
        $value = sanitize_text_field((string) $input);

        return substr($value, 0, 60);
    }

    public function sanitize_cookie_consent_url($input): string
    {
        $url = esc_url_raw((string) $input);

        if ($url === '') {
            return '';
        }

        $scheme = wp_parse_url($url, PHP_URL_SCHEME);

        return in_array($scheme, array('http', 'https'), true) ? $url : '';
    }

    public function sanitize_cookie_consent_position($input): string
    {
        $value = sanitize_key((string) $input);
        $allowed = array('bottom-full', 'top-full', 'bottom-left', 'bottom-right', 'bottom-center');

        return in_array($value, $allowed, true) ? $value : 'bottom-full';
    }

    public function sanitize_cookie_consent_variant($input): string
    {
        $value = sanitize_key((string) $input);

        return in_array($value, array('light', 'dark'), true) ? $value : 'light';
    }

    public function sanitize_cookie_consent_mode($input): string
    {
        $value = sanitize_key((string) $input);

        return in_array($value, array('choice', 'informational'), true) ? $value : 'choice';
    }

    public function sanitize_accessibility_popup_title($input): string
    {
        return substr(sanitize_text_field((string) $input), 0, 80);
    }

    public function sanitize_accessibility_popup_description($input): string
    {
        return substr(sanitize_text_field((string) $input), 0, 240);
    }

    public function sanitize_accessibility_popup_label($input): string
    {
        return substr(sanitize_text_field((string) $input), 0, 60);
    }

    public function sanitize_accessibility_popup_position($input): string
    {
        $value = sanitize_key((string) $input);
        $allowed = array('bottom-right', 'bottom-left', 'top-right', 'top-left');

        return in_array($value, $allowed, true) ? $value : 'bottom-right';
    }

    public function sanitize_accessibility_popup_features($input): string
    {
        $raw = str_replace("\r", '', (string) $input);
        $lines = explode("\n", $raw);
        $clean = array();

        foreach ($lines as $line) {
            $line = trim(sanitize_text_field($line));

            if ($line === '') {
                continue;
            }

            $clean[] = substr($line, 0, 120);

            if (count($clean) >= 8) {
                break;
            }
        }

        return implode("\n", $clean);
    }

    public function sanitize_share_bar_alignment($input): string
    {
        $value = sanitize_key((string) $input);

        return in_array($value, array('left', 'center', 'right'), true) ? $value : 'center';
    }

    public function sanitize_share_bar_icon_source($input): string
    {
        $value = sanitize_key((string) $input);

        return in_array($value, array('inline', 'local'), true) ? $value : 'inline';
    }

    public function sanitize_share_bar_size($input): string
    {
        $value = sanitize_key((string) $input);

        return in_array($value, array('small', 'medium'), true) ? $value : 'medium';
    }

    public function sanitize_share_bar_order($input): string
    {
        $allowed = array('whatsapp', 'facebook', 'x', 'linkedin', 'telegram', 'copy', 'reddit', 'pinterest', 'mastodon', 'threads', 'email', 'instagram', 'youtube', 'tiktok', 'custom');
        $raw = sanitize_text_field((string) $input);
        $parts = array_filter(array_map('sanitize_key', array_map('trim', explode(',', $raw))));
        $ordered = array_values(array_unique(array_intersect($parts, $allowed)));

        foreach ($allowed as $network) {
            if (! in_array($network, $ordered, true)) {
                $ordered[] = $network;
            }
        }

        return implode(',', $ordered);
    }

    public function sanitize_share_icon_library($input): string
    {
        $value = sanitize_key((string) $input);

        return in_array($value, array('simple-icons', 'bootstrap-icons', 'custom-source'), true) ? $value : 'simple-icons';
    }

    public function sanitize_share_custom_label($input): string
    {
        return substr(sanitize_text_field((string) $input), 0, 50);
    }

    public function sanitize_share_custom_url_template($input): string
    {
        $raw = trim(wp_strip_all_tags((string) $input));

        if ($raw === '') {
            return '';
        }

        $allowed_template = str_replace(array('{url}', '{title}'), array('https://example.com', 'Example'), $raw);
        $sanitized = esc_url_raw($allowed_template);
        $scheme = wp_parse_url($sanitized, PHP_URL_SCHEME);

        if ($sanitized === '' || ! in_array($scheme, array('http', 'https'), true)) {
            return '';
        }

        return substr($raw, 0, 300);
    }

    public function sanitize_third_party_worker_scripts($input): array
    {
        return $this->sanitize_third_party_scripts($input, true);
    }

    public function sanitize_third_party_main_thread_scripts($input): array
    {
        return $this->sanitize_third_party_scripts($input, false);
    }

    private function sanitize_third_party_scripts($input, bool $worker_mode): array
    {
        if (! is_array($input)) {
            return array();
        }

        $clean = array();
        $seen = array();

        foreach ($input as $row) {
            if (! is_array($row)) {
                continue;
            }

            $url = $this->sanitize_external_script_url(isset($row['url']) ? $row['url'] : '');

            if ($url === '') {
                continue;
            }

            $url_key = strtolower(trim($url));

            if ($url_key === '' || isset($seen[$url_key])) {
                continue;
            }

            $seen[$url_key] = true;

            $item = array(
                'label' => substr(sanitize_text_field((string) (isset($row['label']) ? $row['label'] : '')), 0, 80),
                'url' => $url,
                'inline' => $this->sanitize_inline_js_snippet(isset($row['inline']) ? $row['inline'] : ''),
                'enabled' => $this->sanitize_checkbox(isset($row['enabled']) ? $row['enabled'] : 0),
            );

            if ($worker_mode) {
                $item['forward'] = $this->sanitize_forward_keys(isset($row['forward']) ? $row['forward'] : '');
            } else {
                $item['strategy'] = $this->sanitize_main_thread_strategy(isset($row['strategy']) ? $row['strategy'] : 'defer');
            }

            $clean[] = $item;

            if (count($clean) >= 20) {
                break;
            }
        }

        return $clean;
    }

    private function sanitize_external_script_url($input): string
    {
        $url = esc_url_raw((string) $input);

        if ($url === '') {
            return '';
        }

        $scheme = wp_parse_url($url, PHP_URL_SCHEME);
        $host = wp_parse_url($url, PHP_URL_HOST);

        if (! in_array($scheme, array('http', 'https'), true) || ! is_string($host) || $host === '') {
            return '';
        }

        return $url;
    }

    private function sanitize_inline_js_snippet($input): string
    {
        $snippet = str_replace(array("\0", "\r"), '', (string) $input);
        $snippet = trim($snippet);

        if ($snippet === '') {
            return '';
        }

        $snippet = preg_replace('#<\s*/?\s*script\b[^>]*>#i', '', $snippet);

        if (! is_string($snippet)) {
            return '';
        }

        $snippet = str_replace('<?', '', $snippet);

        return substr($snippet, 0, 4000);
    }

    private function sanitize_forward_keys($input): array
    {
        $raw = '';

        if (is_array($input)) {
            $raw = implode(',', array_map('strval', $input));
        } else {
            $raw = (string) $input;
        }

        $parts = preg_split('/[\s,]+/', $raw);

        if (! is_array($parts)) {
            return array();
        }

        $keys = array();

        foreach ($parts as $part) {
            $key = trim(sanitize_text_field((string) $part));

            if ($key === '') {
                continue;
            }

            if (! preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*(\.[A-Za-z_$][A-Za-z0-9_$]*)*$/', $key)) {
                continue;
            }

            if (! in_array($key, $keys, true)) {
                $keys[] = $key;
            }

            if (count($keys) >= 20) {
                break;
            }
        }

        return $keys;
    }

    private function sanitize_main_thread_strategy($input): string
    {
        $value = sanitize_key((string) $input);

        return in_array($value, array('async', 'defer'), true) ? $value : 'defer';
    }
}
