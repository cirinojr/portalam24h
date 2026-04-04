<?php

class Am24h_FontStorageManager
{
    private Am24h_FontValidator $validator;

    public function __construct(Am24h_FontValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @return array{base_dir: string, base_url: string}|WP_Error
     */
    public function get_base_location()
    {
        $uploads = wp_upload_dir();

        if (! empty($uploads['error'])) {
            return new WP_Error('am24h_font_uploads_error', __('Unable to resolve uploads directory for fonts.', 'am24h'));
        }

        $base_dir = trailingslashit((string) $uploads['basedir']) . 'am24h/fonts';
        $base_url = trailingslashit((string) $uploads['baseurl']) . 'am24h/fonts';

        if (! wp_mkdir_p($base_dir)) {
            return new WP_Error('am24h_font_dir_create_error', __('Unable to create fonts directory inside uploads.', 'am24h'));
        }

        return array(
            'base_dir' => $base_dir,
            'base_url' => $base_url,
        );
    }

    /**
     * @return array{dir: string, url: string}|WP_Error
     */
    public function get_family_location(string $family_slug)
    {
        $base = $this->get_base_location();

        if (is_wp_error($base)) {
            return $base;
        }

        $slug = sanitize_title($family_slug);

        if ($slug === '') {
            return new WP_Error('am24h_font_invalid_slug', __('Invalid font slug.', 'am24h'));
        }

        $dir = trailingslashit($base['base_dir']) . $slug;
        $url = trailingslashit($base['base_url']) . $slug;

        if (! wp_mkdir_p($dir)) {
            return new WP_Error('am24h_font_family_dir_error', __('Unable to create local directory for selected font.', 'am24h'));
        }

        return array(
            'dir' => $dir,
            'url' => $url,
        );
    }

    /**
     * @return array{file: string, path: string, url: string}|WP_Error
     */
    public function store_font_file(string $family_slug, string $filename, string $contents)
    {
        if (! $this->validator->is_valid_web_font_filename($filename)) {
            return new WP_Error('am24h_font_invalid_extension', __('Only .woff2 or .woff files are accepted for local storage.', 'am24h'));
        }

        $location = $this->get_family_location($family_slug);

        if (is_wp_error($location)) {
            return $location;
        }

        $safe_name = sanitize_file_name($filename);
        $target_path = trailingslashit($location['dir']) . $safe_name;

        if (! $this->validator->is_path_inside_base($target_path, $location['dir'])) {
            return new WP_Error('am24h_font_invalid_target', __('Invalid target path for font file.', 'am24h'));
        }

        $filesystem = $this->get_filesystem();

        if (is_wp_error($filesystem)) {
            return $filesystem;
        }

        $written = $filesystem->put_contents($target_path, $contents, FS_CHMOD_FILE);

        if ($written === false) {
            return new WP_Error('am24h_font_write_error', __('Unable to write font file to local storage.', 'am24h'));
        }

        return array(
            'file' => $safe_name,
            'path' => $target_path,
            'url'  => trailingslashit($location['url']) . $safe_name,
        );
    }

    public function remove_family(string $family_slug): bool
    {
        $base = $this->get_base_location();

        if (is_wp_error($base)) {
            return false;
        }

        $slug = sanitize_title($family_slug);

        if ($slug === '') {
            return false;
        }

        $directory = trailingslashit($base['base_dir']) . $slug;

        if (! $this->validator->is_path_inside_base($directory, $base['base_dir'])) {
            return false;
        }

        return $this->delete_directory_recursive($directory, $base['base_dir']);
    }

    private function delete_directory_recursive(string $directory, string $base_directory): bool
    {
        $filesystem = $this->get_filesystem();

        if (is_wp_error($filesystem)) {
            return false;
        }

        if (! $filesystem->is_dir($directory)) {
            return true;
        }

        $items = $filesystem->dirlist($directory);

        if (! is_array($items)) {
            return false;
        }

        foreach ($items as $item_name => $item_data) {
            $path = trailingslashit($directory) . $item_name;

            if (! $this->validator->is_path_inside_base($path, $base_directory)) {
                return false;
            }

            if (isset($item_data['type']) && $item_data['type'] === 'd') {
                if (! $this->delete_directory_recursive($path, $base_directory)) {
                    return false;
                }

                continue;
            }

            if (! $filesystem->delete($path, false, 'f')) {
                return false;
            }
        }

        return $filesystem->rmdir($directory, false);
    }

    /**
     * @return WP_Filesystem_Base|WP_Error
     */
    private function get_filesystem()
    {
        global $wp_filesystem;

        if (! function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $initialized = WP_Filesystem();

        if (! $initialized || ! isset($wp_filesystem)) {
            return new WP_Error('am24h_font_fs_init_failed', __('Unable to initialize WordPress filesystem.', 'am24h'));
        }

        return $wp_filesystem;
    }
}
