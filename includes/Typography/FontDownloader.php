<?php

class Am24h_FontDownloader
{
    private const MAX_FILE_SIZE = 5242880;

    private Am24h_FontProviderGoogle $provider;
    private Am24h_FontStorageManager $storage;
    private Am24h_FontValidator $validator;

    public function __construct(
        Am24h_FontProviderGoogle $provider,
        Am24h_FontStorageManager $storage,
        Am24h_FontValidator $validator
    ) {
        $this->provider = $provider;
        $this->storage = $storage;
        $this->validator = $validator;
    }

    /**
     * @param array<int, array{weight: int, style: string}> $variants
     * @return array{family: string, slug: string, variants: array<int, array{weight: int, style: string, format: string, file: string, url: string}>, preload_file: string, contains_non_woff2: bool}|WP_Error
     */
    public function import_family(string $family, array $variants)
    {
        $family = $this->validator->sanitize_family_name($family);

        if ($family === '') {
            return new WP_Error('am24h_font_invalid_family', __('A valid font family is required.', 'am24h'));
        }

        if (empty($variants)) {
            return new WP_Error('am24h_font_missing_variants', __('Select at least one weight/style before importing.', 'am24h'));
        }

        $css = $this->provider->fetch_stylesheet($family, $variants);

        if (is_wp_error($css)) {
            return $css;
        }

        $sources = $this->provider->extract_sources($css);

        if (empty($sources)) {
            return new WP_Error('am24h_font_no_sources', __('No web-ready font sources were found for the selected variants. This theme installs only local WOFF2 (preferred) or WOFF files and does not convert TTF/OTF at runtime.', 'am24h'));
        }

        $slug = $this->validator->family_slug($family);

        if ($slug === '') {
            return new WP_Error('am24h_font_invalid_slug', __('Unable to create a safe slug for the selected font.', 'am24h'));
        }

        $stored_variants = array();
        $contains_non_woff2 = false;

        foreach ($sources as $source) {
            $downloaded = $this->download_remote_font($source['remote_url'], $source['source_format']);

            if (is_wp_error($downloaded)) {
                $this->storage->remove_family($slug);
                return $downloaded;
            }

            $format = $this->validator->normalize_remote_font_format($source['source_format']);

            if (! $this->validator->is_supported_web_font_format($format)) {
                $this->storage->remove_family($slug);
                return new WP_Error('am24h_font_unsupported_source_format', __('Selected source is not a supported web font format. Installable formats are WOFF2 (preferred) and WOFF.', 'am24h'));
            }

            if (! $this->validator->is_valid_source_binary($format, $downloaded)) {
                $this->storage->remove_family($slug);
                return new WP_Error('am24h_font_invalid_signature', __('Downloaded font file does not match the expected source format signature.', 'am24h'));
            }

            $extension = $this->validator->source_format_to_extension($format);
            $filename = $slug . '-' . $source['weight'] . '-' . $source['style'] . '.' . $extension;
            $stored = $this->storage->store_font_file($slug, $filename, $downloaded);

            if (is_wp_error($stored)) {
                $this->storage->remove_family($slug);
                return $stored;
            }

            if ($format !== 'woff2') {
                $contains_non_woff2 = true;
            }

            $stored_variants[] = array(
                'weight' => (int) $source['weight'],
                'style'  => (string) $source['style'],
                'format' => $format,
                'file'   => (string) $stored['file'],
                'url'    => esc_url_raw((string) $stored['url']),
            );
        }

        usort(
            $stored_variants,
            static function (array $a, array $b): int {
                if ($a['weight'] === $b['weight']) {
                    return strcmp($a['style'], $b['style']);
                }

                return $a['weight'] <=> $b['weight'];
            }
        );

        $preload_file = $this->choose_preload_file($stored_variants);

        return array(
            'family'       => $family,
            'slug'         => $slug,
            'variants'     => $stored_variants,
            'preload_file' => $preload_file,
            'contains_non_woff2' => $contains_non_woff2,
        );
    }

    /**
     * @return string|WP_Error
     */
    private function download_remote_font(string $remote_url, string $source_format)
    {
        if (! $this->validator->is_allowed_remote_url($remote_url)) {
            return new WP_Error('am24h_font_invalid_domain', __('Blocked remote font file URL.', 'am24h'));
        }

        $response = wp_safe_remote_get(
            $remote_url,
            array(
                'timeout' => 30,
                'redirection' => 3,
                'reject_unsafe_urls' => true,
                'limit_response_size' => self::MAX_FILE_SIZE + 1,
            )
        );

        if (is_wp_error($response)) {
            return new WP_Error('am24h_font_download_failed', __('Unable to download selected font file.', 'am24h'));
        }

        if ((int) wp_remote_retrieve_response_code($response) !== 200) {
            return new WP_Error('am24h_font_download_http_error', __('Font file request returned a non-200 status code.', 'am24h'));
        }

        $content_length = (int) wp_remote_retrieve_header($response, 'content-length');

        if ($content_length > self::MAX_FILE_SIZE) {
            return new WP_Error('am24h_font_too_large', __('Downloaded font file exceeded size limit.', 'am24h'));
        }

        $mime = (string) wp_remote_retrieve_header($response, 'content-type');

        $normalized_format = $this->validator->normalize_remote_font_format($source_format);

        if (! $this->validator->is_supported_web_font_format($normalized_format)) {
            return new WP_Error('am24h_font_unsupported_source_format', __('Selected source is not installable for web delivery. Use sources that provide WOFF2 (preferred) or WOFF.', 'am24h'));
        }

        if (! $this->validator->is_valid_remote_font_mime($normalized_format, $mime)) {
            return new WP_Error('am24h_font_invalid_mime', __('Downloaded font has an invalid MIME type.', 'am24h'));
        }

        $body = wp_remote_retrieve_body($response);

        if (! is_string($body) || $body === '') {
            return new WP_Error('am24h_font_empty_file', __('Downloaded font file is empty.', 'am24h'));
        }

        if (strlen($body) > self::MAX_FILE_SIZE) {
            return new WP_Error('am24h_font_too_large', __('Downloaded font file exceeded size limit.', 'am24h'));
        }

        return $body;
    }

    /**
     * @param array<int, array{weight: int, style: string, file: string, url: string}> $variants
     */
    private function choose_preload_file(array $variants): string
    {
        foreach ($variants as $variant) {
            if ($variant['weight'] === 400 && $variant['style'] === 'normal') {
                return $variant['file'];
            }
        }

        return $variants[0]['file'];
    }
}
