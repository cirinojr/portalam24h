<?php

class Am24h_CriticalCss
{
    private const MAX_INLINE_CSS_BYTES = 131072;

    private Am24h_AssetLocator $assets;

    public function __construct(Am24h_AssetLocator $assets)
    {
        $this->assets = $assets;
    }

    public function register_hooks(): void
    {
        add_action('wp_head', array($this, 'render_inline_critical_css'), 1);
    }

    /**
     * Inline critical CSS to style above-the-fold content during first paint.
     */
    public function render_inline_critical_css(): void
    {
        $relative = 'assets/styles/Critical/critical.min.css';

        if (! $this->assets->is_readable($relative)) {
            return;
        }

        $path = $this->assets->path($relative);
        $file_size = @filesize($path);

        if (is_int($file_size) && $file_size > self::MAX_INLINE_CSS_BYTES) {
            return;
        }

        $critical_css = file_get_contents($path, false, null, 0, self::MAX_INLINE_CSS_BYTES + 1);

        if (! is_string($critical_css) || $critical_css === '' || strlen($critical_css) > self::MAX_INLINE_CSS_BYTES) {
            return;
        }

        echo '<style id="critical-css">' . am24h_sanitize_inline_css($critical_css) . '</style>';
    }
}
