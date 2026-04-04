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
}
