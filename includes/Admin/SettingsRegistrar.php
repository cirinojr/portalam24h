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
            'am24h_css_asset_version_enabled' => 'sanitize_checkbox',
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
            'am24h_accessibility_tool_font_size' => 'sanitize_checkbox',
            'am24h_accessibility_tool_line_height' => 'sanitize_checkbox',
            'am24h_accessibility_tool_letter_spacing' => 'sanitize_checkbox',
            'am24h_accessibility_tool_readable_font' => 'sanitize_checkbox',
            'am24h_accessibility_tool_reading_mode' => 'sanitize_checkbox',
            'am24h_accessibility_tool_reading_guide' => 'sanitize_checkbox',
            'am24h_accessibility_tool_reading_mask' => 'sanitize_checkbox',
            'am24h_accessibility_tool_highlight_links' => 'sanitize_checkbox',
            'am24h_accessibility_tool_highlight_headings' => 'sanitize_checkbox',
            'am24h_accessibility_tool_hide_images' => 'sanitize_checkbox',
            'am24h_accessibility_tool_pause_animations' => 'sanitize_checkbox',
            'am24h_accessibility_tool_high_contrast' => 'sanitize_checkbox',
            'am24h_accessibility_tool_reduced_saturation' => 'sanitize_checkbox',
            'am24h_accessibility_tool_grayscale' => 'sanitize_checkbox',
            'am24h_accessibility_popup_features' => 'sanitize_accessibility_popup_features',
            'am24h_share_bar_enabled' => 'sanitize_checkbox',
            'am24h_share_bar_alignment' => 'sanitize_share_bar_alignment',
            'am24h_share_bar_icon_source' => 'sanitize_share_bar_icon_source',
            'am24h_share_bar_size' => 'sanitize_share_bar_size',
            'am24h_share_bar_order' => 'sanitize_share_bar_order',
            'am24h_share_icon_library' => 'sanitize_share_icon_library',
            'am24h_share_network_whatsapp' => 'sanitize_checkbox',
            'am24h_share_network_facebook' => 'sanitize_checkbox',
            'am24h_share_network_x' => 'sanitize_checkbox',
            'am24h_share_network_linkedin' => 'sanitize_checkbox',
            'am24h_share_network_telegram' => 'sanitize_checkbox',
            'am24h_share_network_copy' => 'sanitize_checkbox',
            'am24h_share_network_reddit' => 'sanitize_checkbox',
            'am24h_share_network_pinterest' => 'sanitize_checkbox',
            'am24h_share_network_mastodon' => 'sanitize_checkbox',
            'am24h_share_network_threads' => 'sanitize_checkbox',
            'am24h_share_network_email' => 'sanitize_checkbox',
            'am24h_share_network_instagram' => 'sanitize_checkbox',
            'am24h_share_network_youtube' => 'sanitize_checkbox',
            'am24h_share_network_tiktok' => 'sanitize_checkbox',
            'am24h_share_network_custom' => 'sanitize_checkbox',
            'am24h_share_network_custom_label' => 'sanitize_share_custom_label',
            'am24h_share_network_custom_url' => 'sanitize_share_custom_url_template',
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
                register_setting(
                    $group,
                    $key,
                    array(
                        'sanitize_callback' => function ($value) use ($key, $sanitizer_method) {
                            if (! array_key_exists($key, $_POST)) {
                                return get_option($key, null);
                            }

                            return call_user_func(array($this->sanitizer, $sanitizer_method), $value);
                        },
                    )
                );
            }
        }
    }
}
