<?php

class Am24h_FontValidator
{
    /**
     * @var string[]
     */
    private array $allowed_hosts = array(
        'fonts.googleapis.com',
        'fonts.gstatic.com',
    );

    public function sanitize_family_name(string $family): string
    {
        $family = wp_strip_all_tags($family);
        $family = preg_replace('/\s+/', ' ', (string) $family);

        return trim((string) $family);
    }

    public function family_slug(string $family): string
    {
        return sanitize_title($this->sanitize_family_name($family));
    }

    public function sanitize_style(string $style): string
    {
        $style = strtolower(sanitize_key($style));

        return in_array($style, array('normal', 'italic'), true) ? $style : 'normal';
    }

    public function sanitize_weight($weight): int
    {
        $weight = (int) $weight;

        if ($weight < 100 || $weight > 900 || $weight % 100 !== 0) {
            return 400;
        }

        return $weight;
    }

    /**
     * @param mixed $variants
     * @return array<int, array{weight: int, style: string}>
     */
    public function sanitize_variant_payload($variants): array
    {
        if (! is_array($variants)) {
            return array();
        }

        $clean = array();

        foreach ($variants as $variant) {
            if (! is_string($variant) || strpos($variant, ':') === false) {
                continue;
            }

            list($weight_raw, $style_raw) = explode(':', $variant, 2);
            $weight = $this->sanitize_weight($weight_raw);
            $style = $this->sanitize_style($style_raw);
            $key = $weight . ':' . $style;
            $clean[$key] = array(
                'weight' => $weight,
                'style'  => $style,
            );
        }

        return array_values($clean);
    }

    public function is_allowed_remote_url(string $url): bool
    {
        $url = esc_url_raw($url);

        if ($url === '') {
            return false;
        }

        $parts = wp_parse_url($url);

        if (! is_array($parts) || empty($parts['host']) || empty($parts['scheme'])) {
            return false;
        }

        if (strtolower((string) $parts['scheme']) !== 'https') {
            return false;
        }

        $host = strtolower((string) $parts['host']);

        return in_array($host, $this->allowed_hosts, true);
    }

    public function is_valid_woff2_filename(string $filename): bool
    {
        $filename = sanitize_file_name($filename);

        if ($filename === '') {
            return false;
        }

        return (bool) preg_match('/\.woff2$/i', $filename);
    }

    public function is_valid_web_font_filename(string $filename): bool
    {
        $filename = sanitize_file_name($filename);

        if ($filename === '') {
            return false;
        }

        return (bool) preg_match('/\.(woff2|woff)$/i', $filename);
    }

    public function is_valid_woff2_mime(string $mime): bool
    {
        $mime = strtolower(trim($mime));

        return in_array(
            $mime,
            array(
                'font/woff2',
                'application/font-woff2',
                'application/octet-stream',
            ),
            true
        );
    }

    public function is_valid_woff2_binary(string $contents): bool
    {
        // WOFF2 files start with the ASCII signature: wOF2
        return strncmp($contents, 'wOF2', 4) === 0;
    }

    public function normalize_remote_font_format(string $format): string
    {
        $format = strtolower(trim($format));

        if (strpos($format, 'woff2') !== false) {
            return 'woff2';
        }

        if (strpos($format, 'woff') !== false) {
            return 'woff';
        }

        if ($format === 'woff2') {
            return 'woff2';
        }

        if ($format === 'woff') {
            return 'woff';
        }

        return '';
    }

    public function source_format_to_extension(string $format): string
    {
        $format = $this->normalize_remote_font_format($format);

        if ($format === 'woff2') {
            return 'woff2';
        }

        if ($format === 'woff') {
            return 'woff';
        }

        return 'bin';
    }

    public function is_supported_web_font_format(string $format): bool
    {
        $format = $this->normalize_remote_font_format($format);

        return in_array($format, array('woff2', 'woff'), true);
    }

    public function is_valid_remote_font_mime(string $format, string $mime): bool
    {
        $format = $this->normalize_remote_font_format($format);
        $mime = strtolower(trim($mime));

        if ($mime === '') {
            return true;
        }

        $allowed = array('application/octet-stream');

        if ($format === 'woff2') {
            $allowed[] = 'font/woff2';
            $allowed[] = 'application/font-woff2';
        }

        if ($format === 'woff') {
            $allowed[] = 'font/woff';
            $allowed[] = 'application/font-woff';
        }

        return in_array($mime, $allowed, true);
    }

    public function is_valid_source_binary(string $format, string $contents): bool
    {
        $format = $this->normalize_remote_font_format($format);

        if ($format === 'woff2') {
            return $this->is_valid_woff2_binary($contents);
        }

        if ($format === 'woff') {
            return strncmp($contents, 'wOFF', 4) === 0;
        }

        return false;
    }

    public function is_path_inside_base(string $path, string $base): bool
    {
        $path = wp_normalize_path($path);
        $base = trailingslashit(wp_normalize_path($base));

        return strpos($path, $base) === 0;
    }
}
