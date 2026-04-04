<?php

class Am24h_ThemeOptionsRepository
{
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
        'am24h_accessibility_popup_title' => 'Accessibility Help',
        'am24h_accessibility_popup_description' => 'Use keyboard navigation and skip links to move through the page quickly.',
        'am24h_accessibility_popup_trigger_label' => 'Accessibility',
        'am24h_accessibility_popup_close_label' => 'Close',
        'am24h_accessibility_popup_trigger_position' => 'bottom-right',
        'am24h_accessibility_popup_features' => "Use Tab and Shift+Tab to navigate focusable elements.\nPress Enter or Space to activate controls.\nPress Escape to close dialogs and overlays.",
        'am24h_primary_color'   => '#fd5e04',
        'am24h_secondary_color' => '#fef2e6',
        'am24h_text_color'      => '#1d2433',
        'am24h_background_color' => '#f8f9fc',
        'am24h_success_color'   => '#08875d',
        'am24h_danger_color'    => '#e02d3c',
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
     * @return array{enabled: bool, title: string, description: string, trigger_label: string, close_label: string, trigger_position: string, features: string}
     */
    public function get_accessibility_popup_settings(): array
    {
        return array(
            'enabled' => $this->get_bool('am24h_accessibility_popup_enabled'),
            'title' => (string) $this->get('am24h_accessibility_popup_title'),
            'description' => (string) $this->get('am24h_accessibility_popup_description'),
            'trigger_label' => (string) $this->get('am24h_accessibility_popup_trigger_label'),
            'close_label' => (string) $this->get('am24h_accessibility_popup_close_label'),
            'trigger_position' => (string) $this->get('am24h_accessibility_popup_trigger_position'),
            'features' => (string) $this->get('am24h_accessibility_popup_features'),
        );
    }
}
