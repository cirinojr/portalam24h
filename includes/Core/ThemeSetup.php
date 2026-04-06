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
        add_theme_support('responsive-embeds');
        add_theme_support('customize-selective-refresh-widgets');
        add_theme_support('editor-styles');
        add_theme_support(
            'html5',
            array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script')
        );

        add_editor_style('assets/styles/style.css');

        add_image_size('thumb-desktop', 248, 387, true);
        add_image_size('thumb-mobile', 149, 232, true);
        add_image_size('news-card-thumb', 360, 240, true);
        add_image_size('single-featured', 1200, 640, true);

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
        foreach (Am24h_ThemeOptionsRepository::defaults() as $option_key => $value) {
            if (get_option($option_key, null) === null) {
                add_option($option_key, $value);
            }
        }
    }
}
