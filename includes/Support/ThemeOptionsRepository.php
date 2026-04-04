<?php

class Am24h_ThemeOptionsRepository
{
    private const ACCESSIBILITY_LEGACY_DEFAULTS = array(
        'title' => 'Accessibility Help',
        'description' => 'Use keyboard navigation and skip links to move through the page quickly.',
        'trigger_label' => 'Accessibility',
        'close_label' => 'Close',
        'features' => "Use Tab and Shift+Tab to navigate focusable elements.\nPress Enter or Space to activate controls.\nPress Escape to close dialogs and overlays.",
    );

    private const DEFAULTS = array(
        'am24h_home_categories' => array(),
        'am24h_cleanup_emojis'  => 1,
        'am24h_cleanup_rsd'     => 1,
        'am24h_cleanup_generator' => 1,
        'am24h_cleanup_feed_links' => 1,
        'am24h_cleanup_wlwmanifest' => 1,
        'am24h_cleanup_prev_next_links' => 1,
        'am24h_cleanup_shortlink' => 1,
        'am24h_cleanup_rest_links' => 1,
        'am24h_cleanup_oembed_links' => 1,
        'am24h_cleanup_admin_bar' => 1,
        'am24h_cleanup_block_styles_on_demand' => 1,
        'am24h_cleanup_block_styles' => 0,
        'am24h_cleanup_multilingualpress_hreflang' => 1,
        'am24h_css_asset_version_enabled' => 0,
        'am24h_cookie_consent_enabled' => 0,
        'am24h_cookie_consent_message' => 'We use cookies to improve site functionality and measure audience usage.',
        'am24h_cookie_consent_accept_label' => 'Accept',
        'am24h_cookie_consent_reject_label' => 'Reject',
        'am24h_cookie_consent_policy_url' => '',
        'am24h_cookie_consent_policy_label' => 'Privacy Policy',
        'am24h_cookie_consent_position' => 'bottom-full',
        'am24h_cookie_consent_variant' => 'light',
        'am24h_cookie_consent_mode' => 'choice',
        'am24h_accessibility_popup_enabled' => 0,
        'am24h_accessibility_popup_title' => '',
        'am24h_accessibility_popup_description' => '',
        'am24h_accessibility_popup_trigger_label' => '',
        'am24h_accessibility_popup_close_label' => '',
        'am24h_accessibility_popup_trigger_position' => 'bottom-right',
        'am24h_accessibility_tool_font_size' => 1,
        'am24h_accessibility_tool_line_height' => 1,
        'am24h_accessibility_tool_letter_spacing' => 1,
        'am24h_accessibility_tool_readable_font' => 1,
        'am24h_accessibility_tool_reading_mode' => 1,
        'am24h_accessibility_tool_reading_guide' => 1,
        'am24h_accessibility_tool_reading_mask' => 1,
        'am24h_accessibility_tool_highlight_links' => 1,
        'am24h_accessibility_tool_highlight_headings' => 1,
        'am24h_accessibility_tool_hide_images' => 1,
        'am24h_accessibility_tool_pause_animations' => 1,
        'am24h_accessibility_tool_high_contrast' => 1,
        'am24h_accessibility_tool_reduced_saturation' => 1,
        'am24h_accessibility_tool_grayscale' => 1,
        'am24h_accessibility_popup_features' => '',
        'am24h_share_bar_enabled' => 1,
        'am24h_share_bar_alignment' => 'center',
        'am24h_share_bar_icon_source' => 'inline',
        'am24h_share_bar_size' => 'medium',
        'am24h_share_bar_order' => 'whatsapp,facebook,x,linkedin,telegram,copy,reddit,pinterest,mastodon,threads,email,instagram,youtube,tiktok,custom',
        'am24h_share_icon_library' => 'simple-icons',
        'am24h_share_network_whatsapp' => 1,
        'am24h_share_network_facebook' => 1,
        'am24h_share_network_x' => 1,
        'am24h_share_network_linkedin' => 1,
        'am24h_share_network_telegram' => 1,
        'am24h_share_network_copy' => 1,
        'am24h_share_network_reddit' => 0,
        'am24h_share_network_pinterest' => 0,
        'am24h_share_network_mastodon' => 0,
        'am24h_share_network_threads' => 0,
        'am24h_share_network_email' => 0,
        'am24h_share_network_instagram' => 0,
        'am24h_share_network_youtube' => 0,
        'am24h_share_network_tiktok' => 0,
        'am24h_share_network_custom' => 0,
        'am24h_share_network_custom_label' => 'Custom',
        'am24h_share_network_custom_url' => '',
        'am24h_third_party_worker_scripts' => array(),
        'am24h_third_party_main_thread_scripts' => array(),
        'am24h_primary_color'   => '#cc0000',
        'am24h_secondary_color' => '#f3f3f3',
        'am24h_text_color'      => '#111111',
        'am24h_background_color' => '#f5f5f5',
        'am24h_success_color'   => '#0b7a4b',
        'am24h_danger_color'    => '#cc0000',
        'am24h_defer_visual_overrides' => 1,
        'am24h_site_language'   => 'pt_BR',
    );

    public function get(string $key)
    {
        $default = isset(self::DEFAULTS[$key]) ? self::DEFAULTS[$key] : '';

        return get_option($key, $default);
    }

    public function get_bool(string $key): bool
    {
        return (int) $this->get($key) === 1;
    }

    public function get_home_categories(): array
    {
        return array_values(array_filter(array_map('absint', (array) $this->get('am24h_home_categories'))));
    }

    /**
     * @return array<string, bool>
     */
    public function get_cleanup_flags(): array
    {
        return array(
            'am24h_cleanup_emojis' => $this->get_bool('am24h_cleanup_emojis'),
            'am24h_cleanup_rsd' => $this->get_bool('am24h_cleanup_rsd'),
            'am24h_cleanup_generator' => $this->get_bool('am24h_cleanup_generator'),
            'am24h_cleanup_feed_links' => $this->get_bool('am24h_cleanup_feed_links'),
            'am24h_cleanup_wlwmanifest' => $this->get_bool('am24h_cleanup_wlwmanifest'),
            'am24h_cleanup_prev_next_links' => $this->get_bool('am24h_cleanup_prev_next_links'),
            'am24h_cleanup_shortlink' => $this->get_bool('am24h_cleanup_shortlink'),
            'am24h_cleanup_rest_links' => $this->get_bool('am24h_cleanup_rest_links'),
            'am24h_cleanup_oembed_links' => $this->get_bool('am24h_cleanup_oembed_links'),
            'am24h_cleanup_admin_bar' => $this->get_bool('am24h_cleanup_admin_bar'),
            'am24h_cleanup_block_styles_on_demand' => $this->get_bool('am24h_cleanup_block_styles_on_demand'),
            'am24h_cleanup_block_styles' => $this->get_bool('am24h_cleanup_block_styles'),
            'am24h_cleanup_multilingualpress_hreflang' => $this->get_bool('am24h_cleanup_multilingualpress_hreflang'),
        );
    }

    /**
     * @return array<string, string>
     */
    public function get_color_set(): array
    {
        return array(
            'primary'    => (string) $this->get('am24h_primary_color'),
            'secondary'  => (string) $this->get('am24h_secondary_color'),
            'text'       => (string) $this->get('am24h_text_color'),
            'background' => (string) $this->get('am24h_background_color'),
            'success'    => (string) $this->get('am24h_success_color'),
            'danger'     => (string) $this->get('am24h_danger_color'),
        );
    }

    public function get_site_language(): string
    {
        $language = (string) $this->get('am24h_site_language');

        return in_array($language, Am24h_LanguageCatalog::codes(), true) ? $language : 'pt_BR';
    }

    public function should_defer_visual_overrides(): bool
    {
        return $this->get_bool('am24h_defer_visual_overrides');
    }

    /**
     * @return array{enabled: bool, message: string, accept_label: string, reject_label: string, policy_url: string, policy_label: string, position: string, variant: string, mode: string}
     */
    public function get_cookie_consent_settings(): array
    {
        return array(
            'enabled' => $this->get_bool('am24h_cookie_consent_enabled'),
            'message' => (string) $this->get('am24h_cookie_consent_message'),
            'accept_label' => (string) $this->get('am24h_cookie_consent_accept_label'),
            'reject_label' => (string) $this->get('am24h_cookie_consent_reject_label'),
            'policy_url' => (string) $this->get('am24h_cookie_consent_policy_url'),
            'policy_label' => (string) $this->get('am24h_cookie_consent_policy_label'),
            'position' => (string) $this->get('am24h_cookie_consent_position'),
            'variant' => (string) $this->get('am24h_cookie_consent_variant'),
            'mode' => (string) $this->get('am24h_cookie_consent_mode'),
        );
    }

    /**
     * @return array{enabled: bool, title: string, description: string, trigger_label: string, close_label: string, trigger_position: string, tools: array<string, bool>, features: string}
     */
    public function get_accessibility_popup_settings(): array
    {
        $title = $this->normalize_accessibility_text('title', (string) $this->get('am24h_accessibility_popup_title'));
        $description = $this->normalize_accessibility_text('description', (string) $this->get('am24h_accessibility_popup_description'));
        $trigger_label = $this->normalize_accessibility_text('trigger_label', (string) $this->get('am24h_accessibility_popup_trigger_label'));
        $close_label = $this->normalize_accessibility_text('close_label', (string) $this->get('am24h_accessibility_popup_close_label'));
        $features = $this->normalize_accessibility_text('features', (string) $this->get('am24h_accessibility_popup_features'));

        return array(
            'enabled' => $this->get_bool('am24h_accessibility_popup_enabled'),
            'title' => $title,
            'description' => $description,
            'trigger_label' => $trigger_label,
            'close_label' => $close_label,
            'trigger_position' => (string) $this->get('am24h_accessibility_popup_trigger_position'),
            'tools' => array(
                'font_size' => $this->get_bool('am24h_accessibility_tool_font_size'),
                'line_height' => $this->get_bool('am24h_accessibility_tool_line_height'),
                'letter_spacing' => $this->get_bool('am24h_accessibility_tool_letter_spacing'),
                'readable_font' => $this->get_bool('am24h_accessibility_tool_readable_font'),
                'reading_mode' => $this->get_bool('am24h_accessibility_tool_reading_mode'),
                'reading_guide' => $this->get_bool('am24h_accessibility_tool_reading_guide'),
                'reading_mask' => $this->get_bool('am24h_accessibility_tool_reading_mask'),
                'highlight_links' => $this->get_bool('am24h_accessibility_tool_highlight_links'),
                'highlight_headings' => $this->get_bool('am24h_accessibility_tool_highlight_headings'),
                'hide_images' => $this->get_bool('am24h_accessibility_tool_hide_images'),
                'pause_animations' => $this->get_bool('am24h_accessibility_tool_pause_animations'),
                'high_contrast' => $this->get_bool('am24h_accessibility_tool_high_contrast'),
                'reduced_saturation' => $this->get_bool('am24h_accessibility_tool_reduced_saturation'),
                'grayscale' => $this->get_bool('am24h_accessibility_tool_grayscale'),
            ),
            'features' => $features,
        );
    }

    /**
     * Normalize legacy hardcoded defaults so UI follows the active locale.
     */
    private function normalize_accessibility_text(string $key, string $value): string
    {
        $clean = trim($value);
        $translated_defaults = $this->accessibility_translated_defaults();
        $legacy = isset(self::ACCESSIBILITY_LEGACY_DEFAULTS[$key]) ? self::ACCESSIBILITY_LEGACY_DEFAULTS[$key] : '';

        if ($clean === '' || $clean === $legacy) {
            return isset($translated_defaults[$key]) ? $translated_defaults[$key] : $clean;
        }

        return $clean;
    }

    /**
     * @return array{title: string, description: string, trigger_label: string, close_label: string, features: string}
     */
    private function accessibility_translated_defaults(): array
    {
        return array(
            'title' => __('Accessibility Help', 'am24h'),
            'description' => __('Use keyboard navigation and skip links to move through the page quickly.', 'am24h'),
            'trigger_label' => __('Accessibility', 'am24h'),
            'close_label' => __('Close', 'am24h'),
            'features' => __('Use Tab and Shift+Tab to navigate focusable elements.\nPress Enter or Space to activate controls.\nPress Escape to close dialogs and overlays.', 'am24h'),
        );
    }

    /**
     * @return array<string, string>
     */
    public static function share_network_labels(): array
    {
        return array(
            'whatsapp' => __('WhatsApp', 'am24h'),
            'facebook' => __('Facebook', 'am24h'),
            'x' => __('X', 'am24h'),
            'linkedin' => __('LinkedIn', 'am24h'),
            'telegram' => __('Telegram', 'am24h'),
            'copy' => __('Copy link', 'am24h'),
            'reddit' => __('Reddit', 'am24h'),
            'pinterest' => __('Pinterest', 'am24h'),
            'mastodon' => __('Mastodon', 'am24h'),
            'threads' => __('Threads', 'am24h'),
            'email' => __('Email', 'am24h'),
            'instagram' => __('Instagram', 'am24h'),
            'youtube' => __('YouTube', 'am24h'),
            'tiktok' => __('TikTok', 'am24h'),
            'custom' => __('Custom', 'am24h'),
        );
    }

    /**
     * @return array{enabled: bool, alignment: string, icon_source: string, icon_library: string, size: string, order: string[], networks: array<string, bool>, custom_label: string, custom_url_template: string}
     */
    public function get_share_bar_settings(): array
    {
        $alignment = sanitize_key((string) $this->get('am24h_share_bar_alignment'));
        $icon_source = sanitize_key((string) $this->get('am24h_share_bar_icon_source'));
        $icon_library = sanitize_key((string) $this->get('am24h_share_icon_library'));
        $size = sanitize_key((string) $this->get('am24h_share_bar_size'));
        $allowed_networks = array_keys(self::share_network_labels());

        if (! in_array($alignment, array('left', 'center', 'right'), true)) {
            $alignment = 'center';
        }

        if (! in_array($icon_source, array('inline', 'local'), true)) {
            $icon_source = 'inline';
        }

        if (! in_array($icon_library, array('simple-icons', 'custom-source'), true)) {
            $icon_library = 'simple-icons';
        }

        if (! in_array($size, array('small', 'medium'), true)) {
            $size = 'medium';
        }

        $raw_order = sanitize_text_field((string) $this->get('am24h_share_bar_order'));
        $order = array_values(
            array_unique(
                array_intersect(
                    array_filter(array_map('sanitize_key', array_map('trim', explode(',', $raw_order)))),
                    $allowed_networks
                )
            )
        );

        foreach ($allowed_networks as $network) {
            if (! in_array($network, $order, true)) {
                $order[] = $network;
            }
        }

        $networks = array();

        foreach ($allowed_networks as $network) {
            $networks[$network] = $this->get_bool('am24h_share_network_' . $network);
        }

        $custom_label = sanitize_text_field((string) $this->get('am24h_share_network_custom_label'));
        $custom_url_template = trim(wp_strip_all_tags((string) $this->get('am24h_share_network_custom_url')));

        if ($custom_label === '') {
            $custom_label = __('Custom', 'am24h');
        }

        return array(
            'enabled' => $this->get_bool('am24h_share_bar_enabled'),
            'alignment' => $alignment,
            'icon_source' => $icon_source,
            'icon_library' => $icon_library,
            'size' => $size,
            'order' => $order,
            'networks' => $networks,
            'custom_label' => $custom_label,
            'custom_url_template' => $custom_url_template,
        );
    }

    /**
     * @return array<int, array{label: string, url: string, inline: string, forward: array<int, string>, enabled: bool}>
     */
    public function get_third_party_worker_scripts(): array
    {
        $raw = $this->get('am24h_third_party_worker_scripts');

        if (! is_array($raw)) {
            return array();
        }

        $scripts = array();

        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }

            $url = $this->normalize_external_script_url(isset($row['url']) ? $row['url'] : '');

            if ($url === '') {
                continue;
            }

            $forward = array();

            if (isset($row['forward']) && is_array($row['forward'])) {
                foreach ($row['forward'] as $key) {
                    $token = trim(sanitize_text_field((string) $key));

                    if ($token === '') {
                        continue;
                    }

                    if (! preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*(\.[A-Za-z_$][A-Za-z0-9_$]*)*$/', $token)) {
                        continue;
                    }

                    if (! in_array($token, $forward, true)) {
                        $forward[] = $token;
                    }

                    if (count($forward) >= 20) {
                        break;
                    }
                }
            }

            $scripts[] = array(
                'label' => substr(sanitize_text_field((string) (isset($row['label']) ? $row['label'] : '')), 0, 80),
                'url' => $url,
                'inline' => $this->normalize_inline_script(isset($row['inline']) ? $row['inline'] : ''),
                'forward' => $forward,
                'enabled' => ! empty($row['enabled']) && (int) $row['enabled'] === 1,
            );

            if (count($scripts) >= 20) {
                break;
            }
        }

        return $scripts;
    }

    /**
     * @return array<int, array{label: string, url: string, inline: string, strategy: string, enabled: bool}>
     */
    public function get_third_party_main_thread_scripts(): array
    {
        $raw = $this->get('am24h_third_party_main_thread_scripts');

        if (! is_array($raw)) {
            return array();
        }

        $scripts = array();

        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }

            $url = $this->normalize_external_script_url(isset($row['url']) ? $row['url'] : '');

            if ($url === '') {
                continue;
            }

            $strategy = sanitize_key((string) (isset($row['strategy']) ? $row['strategy'] : 'defer'));

            if (! in_array($strategy, array('async', 'defer'), true)) {
                $strategy = 'defer';
            }

            $scripts[] = array(
                'label' => substr(sanitize_text_field((string) (isset($row['label']) ? $row['label'] : '')), 0, 80),
                'url' => $url,
                'inline' => $this->normalize_inline_script(isset($row['inline']) ? $row['inline'] : ''),
                'strategy' => $strategy,
                'enabled' => ! empty($row['enabled']) && (int) $row['enabled'] === 1,
            );

            if (count($scripts) >= 20) {
                break;
            }
        }

        return $scripts;
    }

    private function normalize_external_script_url($value): string
    {
        $url = esc_url_raw((string) $value);

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

    private function normalize_inline_script($value): string
    {
        $inline = str_replace(array("\0", "\r"), '', (string) $value);
        $inline = trim($inline);

        if ($inline === '') {
            return '';
        }

        $inline = preg_replace('#<\s*/?\s*script\b[^>]*>#i', '', $inline);

        if (! is_string($inline)) {
            return '';
        }

        $inline = str_replace('<?', '', $inline);

        return substr($inline, 0, 4000);
    }
}
