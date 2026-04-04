<?php

class Am24h_Assets
{
    private Am24h_AssetLocator $assets;

    public function __construct(Am24h_AssetLocator $assets)
    {
        $this->assets = $assets;
    }

    public function register_hooks(): void
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('style_loader_src', array($this, 'remove_asset_version'), 9999);
        add_filter('script_loader_src', array($this, 'remove_asset_version'), 9999);
    }

    public function enqueue_scripts(): void
    {
        $relative = 'assets/js/search-bar.js';

        wp_enqueue_script(
            'am24h-theme-search-bar',
            $this->assets->url($relative),
            array(),
            $this->assets->version($relative),
            true
        );

        wp_script_add_data('am24h-theme-search-bar', 'strategy', 'defer');
    }

    public function remove_asset_version(string $src): string
    {
        return remove_query_arg('ver', $src);
    }
}
