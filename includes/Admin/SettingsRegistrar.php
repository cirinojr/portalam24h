<?php

class Am24h_SettingsRegistrar
{
    private const SETTINGS = array(
        'am24h_theme_settings' => array(
            'am24h_home_categories' => 'sanitize_home_categories',
            'am24h_cleanup_emojis' => 'sanitize_checkbox',
            'am24h_cleanup_rsd' => 'sanitize_checkbox',
            'am24h_cleanup_generator' => 'sanitize_checkbox',
            'am24h_cleanup_feed_links' => 'sanitize_checkbox',
            'am24h_cleanup_wlwmanifest' => 'sanitize_checkbox',
            'am24h_cleanup_prev_next_links' => 'sanitize_checkbox',
            'am24h_cleanup_shortlink' => 'sanitize_checkbox',
            'am24h_cleanup_rest_links' => 'sanitize_checkbox',
            'am24h_cleanup_oembed_links' => 'sanitize_checkbox',
            'am24h_cleanup_admin_bar' => 'sanitize_checkbox',
            'am24h_cleanup_block_styles_on_demand' => 'sanitize_checkbox',
            'am24h_cleanup_block_styles' => 'sanitize_checkbox',
            'am24h_cleanup_multilingualpress_hreflang' => 'sanitize_checkbox',
            'am24h_cookie_consent_enabled' => 'sanitize_checkbox',
            'am24h_cookie_consent_message' => 'sanitize_cookie_consent_text',
            'am24h_cookie_consent_accept_label' => 'sanitize_cookie_consent_label',
            'am24h_cookie_consent_reject_label' => 'sanitize_cookie_consent_label',
            'am24h_cookie_consent_policy_url' => 'sanitize_cookie_consent_url',
            'am24h_cookie_consent_policy_label' => 'sanitize_cookie_consent_label',
            'am24h_cookie_consent_position' => 'sanitize_cookie_consent_position',
            'am24h_cookie_consent_variant' => 'sanitize_cookie_consent_variant',
            'am24h_cookie_consent_mode' => 'sanitize_cookie_consent_mode',
            'am24h_accessibility_popup_enabled' => 'sanitize_checkbox',
            'am24h_accessibility_popup_title' => 'sanitize_accessibility_popup_title',
            'am24h_accessibility_popup_description' => 'sanitize_accessibility_popup_description',
            'am24h_accessibility_popup_trigger_label' => 'sanitize_accessibility_popup_label',
            'am24h_accessibility_popup_close_label' => 'sanitize_accessibility_popup_label',
            'am24h_accessibility_popup_trigger_position' => 'sanitize_accessibility_popup_position',
            'am24h_accessibility_popup_features' => 'sanitize_accessibility_popup_features',
        ),
        'am24h_color_settings' => array(
            'am24h_primary_color' => 'sanitize_color',
            'am24h_secondary_color' => 'sanitize_color',
            'am24h_text_color' => 'sanitize_color',
            'am24h_background_color' => 'sanitize_color',
            'am24h_success_color' => 'sanitize_color',
            'am24h_danger_color' => 'sanitize_color',
            'am24h_defer_visual_overrides' => 'sanitize_checkbox',
        ),
        'am24h_language_settings' => array(
            'am24h_site_language' => 'sanitize_language',
        ),
    );

    private Am24h_SettingsSanitizer $sanitizer;

    public function __construct(Am24h_SettingsSanitizer $sanitizer)
    {
        $this->sanitizer = $sanitizer;
    }

    public function register_hooks(): void
    {
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings(): void
    {
        foreach (self::SETTINGS as $group => $settings) {
            foreach ($settings as $key => $sanitizer_method) {
                register_setting($group, $key, array($this->sanitizer, $sanitizer_method));
            }
        }
    }
}
