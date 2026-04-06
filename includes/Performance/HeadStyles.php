<?php

class Am24h_HeadStyles
{
    private Am24h_AssetLocator $assets;
    private const MAIN_STYLE_HANDLE = 'am24h-main-style';
    private const COMPONENT_STYLE_PATH_FRAGMENT = '/assets/styles/Components/';

    public function __construct(Am24h_AssetLocator $assets)
    {
        $this->assets = $assets;
    }

    public function register_hooks(): void
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_main_stylesheet'), 20);
        add_action('wp_enqueue_scripts', array($this, 'dequeue_default_theme_style'), 100);
        add_filter('style_loader_tag', array($this, 'filter_main_stylesheet_tag'), 10, 4);
    }

    public function enqueue_main_stylesheet(): void
    {
        $relative = 'assets/styles/style.css';

        if (! $this->assets->is_readable($relative)) {
            return;
        }

        wp_enqueue_style(
            self::MAIN_STYLE_HANDLE,
            $this->assets->url($relative),
            array(),
            $this->assets->version($relative),
            'all'
        );
    }

    /**
     * Convert non-critical theme stylesheet tags to preload + onload.
     */
    public function filter_main_stylesheet_tag(string $html, string $handle, string $href, string $media): string
    {
        if ($href === '') {
            return $html;
        }

        $should_load_async = $handle === self::MAIN_STYLE_HANDLE || $this->is_component_stylesheet($href);

        if (! $should_load_async) {
            return $html;
        }

        return $this->build_async_stylesheet_tag($href, $media);
    }

    private function is_component_stylesheet(string $href): bool
    {
        return strpos($href, self::COMPONENT_STYLE_PATH_FRAGMENT) !== false;
    }

    private function build_async_stylesheet_tag(string $href, string $media): string
    {
        $safe_href = esc_url($href);
        $safe_media = $media !== '' ? esc_attr($media) : 'all';

        return sprintf(
            "<link rel=\"preload\" href=\"%s\" as=\"style\" onload=\"this.onload=null;this.rel='stylesheet'\" media=\"%s\" />\n<noscript><link rel=\"stylesheet\" href=\"%s\" media=\"%s\" /></noscript>\n",
            $safe_href,
            $safe_media,
            $safe_href,
            $safe_media
        );
    }

    public function dequeue_default_theme_style(): void
    {
        wp_dequeue_style('theme-style');
        wp_deregister_style('theme-style');
    }
}
