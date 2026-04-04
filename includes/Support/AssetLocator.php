<?php

class Am24h_AssetLocator
{
    /**
     * Build an absolute file path from a theme-relative path.
     */
    public function path(string $relative_path): string
    {
        $relative_path = $this->normalize_relative_path($relative_path);

        if ($relative_path === '') {
            return '';
        }

        return trailingslashit(get_template_directory()) . $relative_path;
    }

    /**
     * Build a URL from a theme-relative path.
     */
    public function url(string $relative_path): string
    {
        $relative_path = $this->normalize_relative_path($relative_path);

        if ($relative_path === '') {
            return '';
        }

        return trailingslashit(get_template_directory_uri()) . $relative_path;
    }

    /**
     * Return an asset version suitable for cache busting.
     */
    public function version(string $relative_path): ?string
    {
        $path = $this->path($relative_path);

        if ($path === '' || ! is_readable($path)) {
            return null;
        }

        if (! (defined('WP_DEBUG') && WP_DEBUG)) {
            return (string) wp_get_theme()->get('Version');
        }

        return (string) filemtime($path);
    }

    /**
     * Determine whether a relative asset exists and is readable.
     */
    public function is_readable(string $relative_path): bool
    {
        $path = $this->path($relative_path);

        return $path !== '' && is_readable($path);
    }

    private function normalize_relative_path(string $relative_path): string
    {
        $relative_path = str_replace("\0", '', $relative_path);
        $relative_path = ltrim(wp_normalize_path($relative_path), '/');

        if ($relative_path === '' || strpos($relative_path, '../') !== false || $relative_path === '..') {
            return '';
        }

        return $relative_path;
    }
}
