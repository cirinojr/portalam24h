<?php

class Am24h_HeadStyles
{
    private Am24h_AssetLocator $assets;

    public function __construct(Am24h_AssetLocator $assets)
    {
        $this->assets = $assets;
    }

    public function register_hooks(): void
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_main_stylesheet'), 20);
        add_action('wp_enqueue_scripts', array($this, 'dequeue_default_theme_style'), 100);
    }

    public function enqueue_main_stylesheet(): void
    {
        $relative = 'assets/styles/style.css';

        if (! $this->assets->is_readable($relative)) {
            return;
        }

        wp_enqueue_style(
            'am24h-main-style',
            $this->assets->url($relative),
            array(),
            $this->assets->version($relative),
            'all'
        );
    }

    public function dequeue_default_theme_style(): void
    {
        wp_dequeue_style('theme-style');
        wp_deregister_style('theme-style');
    }
}
