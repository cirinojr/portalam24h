<?php

class Am24h_FontLoader
{
    private Am24h_FontRegistry $registry;
    private Am24h_TypographySettings $settings;
    private Am24h_FontFaceGenerator $generator;
    private Am24h_ThemeOptionsRepository $options;

    public function __construct(
        Am24h_FontRegistry $registry,
        Am24h_TypographySettings $settings,
        Am24h_FontFaceGenerator $generator,
        Am24h_ThemeOptionsRepository $options
    ) {
        $this->registry = $registry;
        $this->settings = $settings;
        $this->generator = $generator;
        $this->options = $options;
    }

    public function register_hooks(): void
    {
        add_action('wp_head', array($this, 'render_preload'), 14);
        add_action('wp_head', array($this, 'render_font_faces'), 15);
        add_action('wp_footer', array($this, 'render_font_faces_deferred'), 4);
    }

    public function render_preload(): void
    {
        if ($this->options->should_defer_visual_overrides()) {
            return;
        }

        if (! $this->settings->is_preload_enabled()) {
            return;
        }

        $font = $this->registry->active();

        if ($font === null) {
            return;
        }

        $url = $this->generator->primary_preload_url($font);

        if ($url === '') {
            return;
        }

        echo '<link rel="preload" as="font" type="font/woff2" href="' . esc_url($url) . '" crossorigin>';
    }

    public function render_font_faces(): void
    {
        if ($this->options->should_defer_visual_overrides()) {
            return;
        }

        $this->output_font_faces();
    }

    public function render_font_faces_deferred(): void
    {
        if (! $this->options->should_defer_visual_overrides()) {
            return;
        }

        $this->output_font_faces();
    }

    private function output_font_faces(): void
    {
        $font = $this->registry->active();

        if ($font === null) {
            return;
        }

        $css = $this->generator->build_css($font, $this->settings->get_fallback_stack());

        if ($css === '') {
            return;
        }

        echo '<style id="am24h-font-faces">' . am24h_sanitize_inline_css($css) . '</style>';
    }
}
