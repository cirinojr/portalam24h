<?php

class Am24h_Assets
{
    private const HANDLE_PREFIX = 'am24h-';

    private Am24h_AssetLocator $assets;
    private Am24h_ThemeOptionsRepository $options;

    public function __construct(Am24h_AssetLocator $assets, Am24h_ThemeOptionsRepository $options)
    {
        $this->assets = $assets;
        $this->options = $options;
    }

    public function register_hooks(): void
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('style_loader_src', array($this, 'filter_style_loader_src'), 9999, 2);
        add_filter('script_loader_src', array($this, 'filter_script_loader_src'), 9999, 2);
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

        if (! is_singular('post') || ! $this->options->get_bool('am24h_share_bar_enabled')) {
            return;
        }

        $share_relative = 'assets/js/share-bar.js';

        if (! $this->assets->is_readable($share_relative)) {
            return;
        }

        wp_enqueue_script(
            'am24h-share-bar',
            $this->assets->url($share_relative),
            array(),
            $this->assets->version($share_relative),
            true
        );

        wp_script_add_data('am24h-share-bar', 'strategy', 'defer');
    }

    public function remove_asset_version(string $src): string
    {
        return remove_query_arg('ver', $src);
    }

    public function filter_script_loader_src(string $src, string $handle): string
    {
        if (! $this->should_filter_handle($handle, $src)) {
            return $src;
        }

        return $this->remove_asset_version($src);
    }

    public function filter_style_loader_src(string $src, string $handle): string
    {
        if (! $this->should_filter_handle($handle, $src)) {
            return $src;
        }

        if ($this->options->get_bool('am24h_css_asset_version_enabled')) {
            return $src;
        }

        return $this->remove_asset_version($src);
    }

    private function should_filter_handle(string $handle, string $src): bool
    {
        if (strpos($handle, self::HANDLE_PREFIX) === 0) {
            return true;
        }

        $theme_url = trailingslashit(get_template_directory_uri());

        return strpos($src, $theme_url) === 0;
    }
}
