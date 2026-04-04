<?php

class Am24h_ThemeSetup
{
    public function register_hooks(): void
    {
        add_action('after_setup_theme', array($this, 'register_theme_supports'));
        add_action('init', array($this, 'register_menus'));
        add_action('after_switch_theme', array($this, 'seed_cleanup_defaults'));
    }

    public function register_theme_supports(): void
    {
        add_theme_support('title-tag');
        add_theme_support('align-wide');
        add_theme_support('post-thumbnails');

        add_image_size('thumb-desktop', 248, 387, true);
        add_image_size('thumb-mobile', 149, 232, true);
        add_image_size('news-card-thumb', 360, 240, true);

        add_theme_support(
            'custom-logo',
            array(
                'height'      => 100,
                'width'       => 100,
                'flex-height' => true,
                'flex-width'  => true,
                'header-text' => array('site-title', 'site-description'),
            )
        );
    }

    public function register_menus(): void
    {
        register_nav_menus(
            array(
                'header-menu' => __('Header Menu (Main Navigation)', 'am24h'),
                'bottom-menu' => __('Footer Menu (Footer Navigation)', 'am24h'),
            )
        );
    }

    public function seed_cleanup_defaults(): void
    {
        $cleanup_defaults = array(
            'am24h_cleanup_emojis' => 1,
            'am24h_cleanup_rsd' => 1,
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
        );

        foreach ($cleanup_defaults as $option_key => $value) {
            if (get_option($option_key, null) === null) {
                add_option($option_key, $value);
            }
        }
    }
}
