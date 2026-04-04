<?php

class Am24h_FontFaceGenerator
{
    /**
     * @param array{id: string, family: string, slug: string, variants: array<int, array{weight: int, style: string, format?: string, file: string, url: string}>, preload_file: string, created_at: int} $font
     */
    public function build_css(array $font, string $fallback_stack): string
    {
        $family = isset($font['family']) ? sanitize_text_field((string) $font['family']) : '';

        if ($family === '' || ! isset($font['variants']) || ! is_array($font['variants']) || empty($font['variants'])) {
            return '';
        }

        $css = '';

        foreach ($font['variants'] as $variant) {
            $url = isset($variant['url']) ? esc_url_raw((string) $variant['url']) : '';

            if ($url === '') {
                continue;
            }

            $weight = isset($variant['weight']) ? (int) $variant['weight'] : 400;
            $style = isset($variant['style']) ? sanitize_key((string) $variant['style']) : 'normal';
            $format = isset($variant['format']) ? strtolower(sanitize_key((string) $variant['format'])) : 'woff2';

            if ($style !== 'italic') {
                $style = 'normal';
            }

            if (! in_array($format, array('woff2', 'woff'), true)) {
                $format = 'woff2';
            }

            $css .= '@font-face{';
            $css .= 'font-family:"' . esc_attr($family) . '";';
            $css .= 'src:url("' . esc_url($url) . '") format("' . $format . '");';
            $css .= 'font-weight:' . $weight . ';';
            $css .= 'font-style:' . $style . ';';
            $css .= 'font-display:swap;';
            $css .= '}';
        }

        if ($css === '') {
            return '';
        }

        $stack = '"' . esc_attr($family) . '",' . sanitize_text_field($fallback_stack);
        $css .= ':root{--cc-font-family-base:' . $stack . ';}';

        return $css;
    }

    /**
     * @param array{id: string, family: string, slug: string, variants: array<int, array{weight: int, style: string, format?: string, file: string, url: string}>, preload_file: string, created_at: int} $font
     */
    public function primary_preload_url(array $font): string
    {
        if (! isset($font['variants']) || ! is_array($font['variants']) || empty($font['variants'])) {
            return '';
        }

        foreach ($font['variants'] as $variant) {
            if ((int) ($variant['weight'] ?? 0) === 400 && ($variant['style'] ?? '') === 'normal' && ($variant['format'] ?? 'woff2') === 'woff2') {
                return esc_url_raw((string) ($variant['url'] ?? ''));
            }
        }

        foreach ($font['variants'] as $variant) {
            if (($variant['format'] ?? 'woff2') === 'woff2') {
                return esc_url_raw((string) ($variant['url'] ?? ''));
            }
        }

        return '';
    }
}
