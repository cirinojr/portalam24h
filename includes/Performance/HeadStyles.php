<?php

class Am24h_HeadStyles
{
    private Am24h_AssetLocator $assets;
    private const MAIN_STYLE_HANDLE = 'am24h-main-style';
    private const COMPONENT_STYLE_PATH_FRAGMENT = '/assets/styles/Components/';
    private const SINGLE_FEATURED_IMAGE_SIZE = 'single-featured';
    private const SINGLE_FEATURED_IMAGE_FALLBACK_SIZE = 'large';
    private const SINGLE_FEATURED_IMAGE_SIZES = '(min-width: 768px) 728px, 100vw';

    public function __construct(Am24h_AssetLocator $assets)
    {
        $this->assets = $assets;
    }

    public function register_hooks(): void
    {
        add_action('wp_head', array($this, 'maybe_preload_custom_logo'), 1);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_main_stylesheet'), 20);
        add_action('wp_enqueue_scripts', array($this, 'dequeue_default_theme_style'), 100);
        add_action('wp_head', array($this, 'maybe_preload_single_featured_image'), 2);
        add_filter('style_loader_tag', array($this, 'filter_main_stylesheet_tag'), 10, 4);
    }

    /**
     * Preload custom logo to start the request before header HTML parsing.
     */
    public function maybe_preload_custom_logo(): void
    {
        if (is_admin()) {
            return;
        }

        $logo_id = (int) get_theme_mod('custom_logo');

        if ($logo_id <= 0) {
            return;
        }

        $logo_src = wp_get_attachment_image_url($logo_id, 'full');

        if (! is_string($logo_src) || $logo_src === '') {
            return;
        }

        $logo_srcset = wp_get_attachment_image_srcset($logo_id, 'full');

        echo '<link rel="preload" as="image" fetchpriority="high" href="' . esc_url($logo_src) . '"';

        if (is_string($logo_srcset) && $logo_srcset !== '') {
            echo ' imagesrcset="' . esc_attr($logo_srcset) . '"';
            echo ' imagesizes="100px"';
        }

        echo " />\n";
    }

    /**
     * Preload the single post featured image to improve LCP fetch start time.
     */
    public function maybe_preload_single_featured_image(): void
    {
        if (is_admin() || ! is_singular('post')) {
            return;
        }

        $post_id = get_queried_object_id();

        if (! is_int($post_id) || $post_id <= 0 || ! has_post_thumbnail($post_id)) {
            return;
        }

        $image_id = (int) get_post_thumbnail_id($post_id);

        if ($image_id <= 0) {
            return;
        }

        $size_name = $this->resolve_single_featured_size_name($image_id);
        $image_src = wp_get_attachment_image_url($image_id, $size_name);

        if (! is_string($image_src) || $image_src === '') {
            return;
        }

        $srcset = wp_get_attachment_image_srcset($image_id, $size_name);

        echo '<link rel="preload" as="image" fetchpriority="high" href="' . esc_url($image_src) . '"';

        if (is_string($srcset) && $srcset !== '') {
            echo ' imagesrcset="' . esc_attr($srcset) . '"';
            echo ' imagesizes="' . esc_attr(self::SINGLE_FEATURED_IMAGE_SIZES) . '"';
        }

        echo " />\n";
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

    private function resolve_single_featured_size_name(int $image_id): string
    {
        $metadata = wp_get_attachment_metadata($image_id);

        if (
            is_array($metadata)
            && isset($metadata['sizes'])
            && is_array($metadata['sizes'])
            && isset($metadata['sizes'][self::SINGLE_FEATURED_IMAGE_SIZE])
        ) {
            return self::SINGLE_FEATURED_IMAGE_SIZE;
        }

        return self::SINGLE_FEATURED_IMAGE_FALLBACK_SIZE;
    }
}
