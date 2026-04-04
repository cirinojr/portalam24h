<?php

class Am24h_ThemeCustomCss
{
    private const HEAD_HELPER_CSS = '.cc-logo-image{width:100%;height:100%;object-fit:contain;display:block;}.cc-news-card__excerpt{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;text-overflow:ellipsis;max-height:3em;line-height:1.5;}@media (min-width:768px){.cc-header__bottom-container{display:flex;justify-content:center;align-items:center;gap:1rem;flex-wrap:nowrap;}.cc-header__bottom-menu{display:flex;align-items:center;gap:1rem;list-style:none;margin:0;padding:0;}.cc-header__bottom-menu li{margin:0;padding:0;}}';

    private Am24h_ThemeOptionsRepository $options;

    public function __construct(Am24h_ThemeOptionsRepository $options)
    {
        $this->options = $options;
    }

    public function register_hooks(): void
    {
        add_action('wp_enqueue_scripts', array($this, 'attach_inline_styles'), 30);
        add_action('wp_footer', array($this, 'output_deferred_visual_css'), 5);
    }

    public function attach_inline_styles(): void
    {
        if (! wp_style_is('am24h-main-style', 'enqueued')) {
            return;
        }

        wp_add_inline_style('am24h-main-style', am24h_sanitize_inline_css(self::HEAD_HELPER_CSS));

        if (! $this->options->should_defer_visual_overrides()) {
            $css = $this->build_visual_overrides_css();

            if ($css !== '') {
                wp_add_inline_style('am24h-main-style', am24h_sanitize_inline_css($css));
            }
        }
    }

    public function output_deferred_visual_css(): void
    {
        if (! $this->options->should_defer_visual_overrides()) {
            return;
        }

        $css = $this->build_visual_overrides_css();

        if ($css === '') {
            return;
        }

        $this->output_style_tag('am24h-visual-overrides-deferred', $css);
    }

    private function output_style_tag(string $id, string $css): void
    {
        $id = sanitize_key($id);

        if ($id === '') {
            $id = 'am24h-inline-style';
        }

        echo '<style id="' . esc_attr($id) . '">' . am24h_sanitize_inline_css($css) . '</style>';
    }

    private function build_visual_overrides_css(): string
    {
        $colors = $this->options->get_color_set();

        $has_overrides = $colors['primary'] !== '#cc0000'
            || $colors['secondary'] !== '#f3f3f3'
            || $colors['text'] !== '#111111'
            || $colors['background'] !== '#f5f5f5'
            || $colors['success'] !== '#0b7a4b'
            || $colors['danger'] !== '#cc0000';

        if (! $has_overrides) {
            return '';
        }

        $css = ':root{';

        if ($colors['primary'] !== '#cc0000') {
            $css .= '--cc-color-primary-600:' . esc_attr($colors['primary']) . ';';
            $css .= '--cc-color-primary-700:' . esc_attr(Am24h_ColorUtils::darken($colors['primary'], 20)) . ';';
            $css .= '--cc-color-primary-800:' . esc_attr(Am24h_ColorUtils::darken($colors['primary'], 40)) . ';';
        }

        if ($colors['secondary'] !== '#f3f3f3') {
            $css .= '--cc-color-primary-100:' . esc_attr($colors['secondary']) . ';';
        }

        if ($colors['text'] !== '#111111') {
            $css .= '--cc-color-black:' . esc_attr($colors['text']) . ';';
        }

        if ($colors['background'] !== '#f5f5f5') {
            $css .= '--cc-color-neutral-100:' . esc_attr($colors['background']) . ';';
        }

        if ($colors['success'] !== '#0b7a4b') {
            $css .= '--cc-color-success-700:' . esc_attr($colors['success']) . ';';
        }

        if ($colors['danger'] !== '#cc0000') {
            $css .= '--cc-color-danger-700:' . esc_attr($colors['danger']) . ';';
        }

        $css .= '}';

        return $css;
    }

}
