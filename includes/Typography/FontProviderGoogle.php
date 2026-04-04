<?php

class Am24h_FontProviderGoogle
{
    private const CSS_ENDPOINT = 'https://fonts.googleapis.com/css2';
    private const WEBFONTS_ENDPOINT = 'https://www.googleapis.com/webfonts/v1/webfonts';
    private const CATALOG_TRANSIENT = 'am24h_google_fonts_catalog_v1';

    private Am24h_FontValidator $validator;

    public function __construct(Am24h_FontValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @return array<int, array{family: string, variants: array<int, string>}>
     */
    public function search_families(string $query = '', int $limit = 20): array
    {
        $catalog = $this->get_catalog();

        if ($query !== '') {
            $catalog = array_values(
                array_filter(
                    $catalog,
                    static function (array $item) use ($query): bool {
                        return stripos($item['family'], $query) !== false;
                    }
                )
            );
        }

        return array_slice($catalog, 0, max(1, $limit));
    }

    /**
     * @return string[]
     */
    public function get_family_variants(string $family): array
    {
        $family = $this->validator->sanitize_family_name($family);
        $catalog = $this->get_catalog();

        foreach ($catalog as $item) {
            if ($item['family'] === $family) {
                return $item['variants'];
            }
        }

        return array('regular', '500', '600', '700', 'italic', '700italic');
    }

    /**
     * @param array<int, array{weight: int, style: string}> $variants
     * @return string|WP_Error
     */
    public function fetch_stylesheet(string $family, array $variants)
    {
        $family = $this->validator->sanitize_family_name($family);

        if ($family === '') {
            return new WP_Error('am24h_font_family_invalid', __('Font family is required.', 'am24h'));
        }

        $stylesheet_url = $this->build_stylesheet_url($family, $variants);

        if (! $this->validator->is_allowed_remote_url($stylesheet_url)) {
            return new WP_Error('am24h_font_provider_invalid_url', __('Blocked remote font URL. Allowed Google endpoints only.', 'am24h'));
        }

        $response = wp_safe_remote_get(
            $stylesheet_url,
            array(
                'timeout' => 20,
                'reject_unsafe_urls' => true,
                'limit_response_size' => 262144,
                'headers' => array(
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
                    'Accept' => 'text/css,*/*;q=0.1',
                ),
            )
        );

        if (is_wp_error($response)) {
            return new WP_Error('am24h_font_provider_request_failed', __('Failed to fetch Google Fonts stylesheet.', 'am24h'));
        }

        if ((int) wp_remote_retrieve_response_code($response) !== 200) {
            return new WP_Error('am24h_font_provider_http_error', __('Google Fonts stylesheet request returned a non-200 status.', 'am24h'));
        }

        $body = wp_remote_retrieve_body($response);

        if (! is_string($body) || trim($body) === '') {
            return new WP_Error('am24h_font_provider_empty_css', __('Google Fonts stylesheet response was empty.', 'am24h'));
        }

        return $body;
    }

    /**
     * @param array<int, array{weight: int, style: string}> $variants
     */
    private function build_stylesheet_url(string $family, array $variants): string
    {
        $family_token = str_replace(' ', '+', $family);

        $normal_weights = array();
        $italic_weights = array();

        foreach ($variants as $variant) {
            $weight = $this->validator->sanitize_weight($variant['weight']);
            $style = $this->validator->sanitize_style($variant['style']);

            if ($style === 'italic') {
                $italic_weights[] = $weight;
                continue;
            }

            $normal_weights[] = $weight;
        }

        $normal_weights = array_values(array_unique($normal_weights));
        $italic_weights = array_values(array_unique($italic_weights));
        sort($normal_weights);
        sort($italic_weights);

        if (! empty($italic_weights)) {
            if (empty($normal_weights)) {
                $normal_weights[] = 400;
            }

            $pairs = array();

            foreach ($normal_weights as $weight) {
                $pairs[] = '0,' . $weight;
            }

            foreach ($italic_weights as $weight) {
                $pairs[] = '1,' . $weight;
            }

            $family_token .= ':ital,wght@' . implode(';', $pairs);
        } else {
            if (empty($normal_weights)) {
                $normal_weights[] = 400;
            }

            $family_token .= ':wght@' . implode(';', $normal_weights);
        }

        return add_query_arg(
            array(
                'family'  => $family_token,
                'display' => 'swap',
            ),
            self::CSS_ENDPOINT
        );
    }

    /**
     * @param string $css
     * @return array<int, array{weight: int, style: string, subset: string, source_format: string, remote_url: string}>
     */
    public function extract_sources(string $css): array
    {
        $matches = array();
        preg_match_all('/(?:\/\*\s*([^*]+?)\s*\*\/\s*)?@font-face\s*\{([^}]+)\}/i', $css, $matches, PREG_SET_ORDER);

        $rows = array();

        foreach ($matches as $match) {
            $subset = isset($match[1]) ? sanitize_text_field((string) $match[1]) : 'unknown';
            $block = isset($match[2]) ? (string) $match[2] : '';

            if (! preg_match('/font-style\s*:\s*([^;]+);/i', $block, $style_match)) {
                continue;
            }

            if (! preg_match('/font-weight\s*:\s*([0-9]{3});/i', $block, $weight_match)) {
                continue;
            }

            $source = $this->extract_best_source($block);

            if (! is_array($source)) {
                continue;
            }

            $remote_url = $source['remote_url'];

            if (! $this->validator->is_allowed_remote_url($remote_url)) {
                continue;
            }

            $rows[] = array(
                'weight'     => $this->validator->sanitize_weight($weight_match[1]),
                'style'      => $this->validator->sanitize_style($style_match[1]),
                'subset'     => strtolower($subset),
                'source_format' => $source['source_format'],
                'remote_url' => $remote_url,
            );
        }

        return $this->select_single_subset_per_variant($rows);
    }

    /**
     * @param array<int, array{weight: int, style: string, subset: string, source_format: string, remote_url: string}> $rows
     * @return array<int, array{weight: int, style: string, subset: string, source_format: string, remote_url: string}>
     */
    private function select_single_subset_per_variant(array $rows): array
    {
        $priorities = array('latin', 'latin-ext', 'vietnamese', 'cyrillic', 'greek', 'unknown');
        $grouped = array();

        foreach ($rows as $row) {
            $key = $row['weight'] . ':' . $row['style'];

            if (! isset($grouped[$key])) {
                $grouped[$key] = array();
            }

            $grouped[$key][] = $row;
        }

        $selected = array();

        foreach ($grouped as $variants) {
            $picked = null;

            foreach ($priorities as $subset) {
                foreach ($variants as $candidate) {
                    if ($candidate['subset'] === $subset) {
                        $picked = $candidate;
                        break 2;
                    }
                }
            }

            if ($picked === null) {
                $picked = $variants[0];
            }

            $selected[] = $picked;
        }

        return $selected;
    }

    /**
     * @return array{remote_url: string, source_format: string}|null
     */
    private function extract_best_source(string $block): ?array
    {
        $matches = array();
        preg_match_all('/url\(([^)]+)\)\s*format\(\s*(?:["\']?)([^"\')]+)(?:["\']?)\s*\)/i', $block, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return null;
        }

        $best = null;
        $best_priority = PHP_INT_MAX;
        $priority_map = array(
            'woff2' => 1,
            'woff' => 2,
        );

        foreach ($matches as $match) {
            $remote_url = isset($match[1]) ? trim((string) $match[1], "'\" ") : '';
            $source_format = isset($match[2]) ? $this->validator->normalize_remote_font_format((string) $match[2]) : '';

            if ($remote_url === '' || $source_format === '' || ! isset($priority_map[$source_format])) {
                continue;
            }

            $priority = $priority_map[$source_format];

            if ($priority < $best_priority) {
                $best_priority = $priority;
                $best = array(
                    'remote_url' => $remote_url,
                    'source_format' => $source_format,
                );
            }
        }

        return $best;
    }

    /**
     * @return array<int, array{family: string, variants: array<int, string>}>
     */
    private function get_catalog(): array
    {
        $cached = get_transient(self::CATALOG_TRANSIENT);

        if (is_array($cached) && ! empty($cached)) {
            return $cached;
        }

        $catalog = $this->fetch_catalog_from_api();

        if (empty($catalog)) {
            $catalog = $this->fallback_catalog();
        }

        set_transient(self::CATALOG_TRANSIENT, $catalog, 12 * HOUR_IN_SECONDS);

        return $catalog;
    }

    /**
     * @return array<int, array{family: string, variants: array<int, string>}>
     */
    private function fetch_catalog_from_api(): array
    {
        $api_key = (string) apply_filters('am24h_google_fonts_api_key', '');

        if ($api_key === '' && defined('AM24H_GOOGLE_FONTS_API_KEY')) {
            $api_key = (string) AM24H_GOOGLE_FONTS_API_KEY;
        }

        if ($api_key === '') {
            return array();
        }

        $url = add_query_arg(
            array(
                'key'  => $api_key,
                'sort' => 'popularity',
            ),
            self::WEBFONTS_ENDPOINT
        );

        $response = wp_safe_remote_get($url, array('timeout' => 20));

        if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
            return array();
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode((string) $body, true);

        if (! is_array($data) || ! isset($data['items']) || ! is_array($data['items'])) {
            return array();
        }

        $catalog = array();

        foreach ($data['items'] as $item) {
            if (! is_array($item) || empty($item['family'])) {
                continue;
            }

            $family = $this->validator->sanitize_family_name((string) $item['family']);

            if ($family === '') {
                continue;
            }

            $variants = array('regular', '500', '600', '700', 'italic', '700italic');

            if (isset($item['variants']) && is_array($item['variants'])) {
                $variants = array_values(array_unique(array_map('sanitize_text_field', $item['variants'])));
            }

            $catalog[] = array(
                'family'   => $family,
                'variants' => $variants,
            );
        }

        return $catalog;
    }

    /**
     * @return array<int, array{family: string, variants: array<int, string>}>
     */
    private function fallback_catalog(): array
    {
        return array(
            array('family' => 'Inter', 'variants' => array('regular', '500', '600', '700', 'italic', '700italic')),
            array('family' => 'Roboto', 'variants' => array('regular', '500', '700', 'italic', '700italic')),
            array('family' => 'Open Sans', 'variants' => array('regular', '600', '700', 'italic', '700italic')),
            array('family' => 'Lato', 'variants' => array('regular', '700', '900', 'italic', '700italic')),
            array('family' => 'Merriweather', 'variants' => array('300', 'regular', '700', '900', 'italic', '700italic')),
            array('family' => 'Source Sans 3', 'variants' => array('regular', '500', '600', '700', 'italic')),
            array('family' => 'PT Serif', 'variants' => array('regular', '700', 'italic', '700italic')),
            array('family' => 'Noto Sans', 'variants' => array('regular', '500', '700', 'italic', '700italic')),
            array('family' => 'Nunito Sans', 'variants' => array('regular', '600', '700', '800', 'italic')),
            array('family' => 'Work Sans', 'variants' => array('regular', '500', '600', '700', 'italic')),
            array('family' => 'Archivo', 'variants' => array('regular', '500', '600', '700', 'italic')),
            array('family' => 'Fira Sans', 'variants' => array('regular', '500', '600', '700', 'italic')),
            array('family' => 'IBM Plex Sans', 'variants' => array('regular', '500', '600', '700', 'italic')),
            array('family' => 'Poppins', 'variants' => array('regular', '500', '600', '700', 'italic')),
            array('family' => 'Montserrat', 'variants' => array('regular', '500', '600', '700', 'italic')),
        );
    }
}
