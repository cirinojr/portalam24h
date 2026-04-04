<?php

class Am24h_FontAdminController
{
    private Am24h_FontProviderGoogle $provider;
    private Am24h_FontDownloader $downloader;
    private Am24h_FontRegistry $registry;
    private Am24h_TypographySettings $settings;
    private Am24h_FontValidator $validator;

    public function __construct(
        Am24h_FontProviderGoogle $provider,
        Am24h_FontDownloader $downloader,
        Am24h_FontRegistry $registry,
        Am24h_TypographySettings $settings,
        Am24h_FontValidator $validator
    ) {
        $this->provider = $provider;
        $this->downloader = $downloader;
        $this->registry = $registry;
        $this->settings = $settings;
        $this->validator = $validator;
    }

    public function register_hooks(): void
    {
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_init', array($this, 'handle_actions'));
    }

    public function register_menu(): void
    {
        add_submenu_page(
            'am24h-theme',
            __('Typography Settings', 'am24h'),
            __('Typography', 'am24h'),
            'manage_options',
            'am24h-typography',
            array($this, 'render_page')
        );
    }

    public function handle_actions(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        if (! isset($_POST['am24h_typography_action'])) {
            return;
        }

        $action = sanitize_key(wp_unslash($_POST['am24h_typography_action']));

        if (! $this->is_valid_nonce()) {
            add_settings_error('am24h_typography_settings', 'am24h_typography_nonce', __('Invalid security token. Please try again.', 'am24h'), 'error');
            return;
        }

        if ($action === 'import_font') {
            $this->handle_import();
            return;
        }

        if ($action === 'activate_font') {
            $this->handle_activate();
            return;
        }

        if ($action === 'remove_font') {
            $this->handle_remove();
            return;
        }

        if ($action === 'update_preferences') {
            $this->handle_preferences_update();
        }
    }

    public function render_page(): void
    {
        $query = isset($_GET['font_query']) ? sanitize_text_field(wp_unslash($_GET['font_query'])) : '';
        $selected_family = isset($_GET['font_family']) ? $this->validator->sanitize_family_name(wp_unslash($_GET['font_family'])) : '';
        $results = $this->provider->search_families($query, 20);
        $variants = $selected_family !== '' ? $this->provider->get_family_variants($selected_family) : array();
        $installed_fonts = $this->registry->all();
        $active_font_id = $this->settings->get_active_font_id();
        ?>
        <div class="wrap am24h-panel">
            <div class="am24h-panel-grid">
                <aside class="am24h-panel-nav">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=am24h-theme')); ?>"><?php esc_html_e('General', 'am24h'); ?></a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=am24h-colors')); ?>"><?php esc_html_e('Colors', 'am24h'); ?></a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=am24h-language')); ?>"><?php esc_html_e('Language', 'am24h'); ?></a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=am24h-typography')); ?>" class="am24h-active"><?php esc_html_e('Typography', 'am24h'); ?></a>
                </aside>

                <main class="am24h-panel-main">
                    <header class="am24h-panel-head">
                        <div>
                            <h1><?php esc_html_e('Typography Settings', 'am24h'); ?></h1>
                            <p><?php esc_html_e('Import Google Fonts and serve them locally from uploads to keep rendering stable and predictable.', 'am24h'); ?></p>
                        </div>
                    </header>

                    <?php settings_errors('am24h_typography_settings'); ?>

                    <div class="notice notice-info inline"><p><?php esc_html_e('Local installation accepts web-ready files only. WOFF2 is preferred for production performance; WOFF may be used as a fallback when WOFF2 is unavailable. TTF/OTF are not installed by this workflow.', 'am24h'); ?></p></div>

                    <section class="am24h-card">
                        <h2><?php esc_html_e('1) Find Font Family', 'am24h'); ?></h2>
                        <form method="get" action="" class="am24h-panel-form">
                            <input type="hidden" name="page" value="am24h-typography" />
                            <div class="am24h-field">
                                <label for="am24h_font_query"><?php esc_html_e('Google Fonts family', 'am24h'); ?></label>
                                <input type="text" id="am24h_font_query" name="font_query" value="<?php echo esc_attr($query); ?>" class="regular-text" placeholder="<?php echo esc_attr__('Inter, Roboto, Merriweather...', 'am24h'); ?>" />
                            </div>
                            <div class="am24h-actions" style="justify-content:flex-start;">
                                <button type="submit" class="button button-secondary"><?php esc_html_e('Search', 'am24h'); ?></button>
                            </div>
                        </form>

                        <?php if (! empty($results)) : ?>
                            <div class="am24h-font-search-results">
                                <?php foreach ($results as $result) : ?>
                                    <a class="button <?php echo $selected_family === $result['family'] ? 'button-primary' : 'button-secondary'; ?>" href="<?php echo esc_url(add_query_arg(array('page' => 'am24h-typography', 'font_query' => $query, 'font_family' => $result['family']), admin_url('admin.php'))); ?>">
                                        <?php echo esc_html($result['family']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                    <?php if ($selected_family !== '') : ?>
                        <section class="am24h-card" style="margin-top:16px;">
                            <h2><?php esc_html_e('2) Import Selected Variants', 'am24h'); ?></h2>
                            <p>
                                <?php
                                printf(
                                    esc_html__('Selected family: %s', 'am24h'),
                                    esc_html($selected_family)
                                );
                                ?>
                            </p>

                            <form method="post" action="" class="am24h-panel-form">
                                <?php wp_nonce_field('am24h_typography_action', 'am24h_typography_nonce'); ?>
                                <input type="hidden" name="am24h_typography_action" value="import_font" />
                                <input type="hidden" name="am24h_font_family" value="<?php echo esc_attr($selected_family); ?>" />

                                <div class="am24h-font-variants">
                                    <?php foreach ($variants as $variant) : ?>
                                        <?php
                                        $token = $this->variant_token_from_google((string) $variant);
                                        if ($token === '') {
                                            continue;
                                        }
                                        ?>
                                        <label>
                                            <input type="checkbox" name="am24h_font_variants[]" value="<?php echo esc_attr($token); ?>" <?php checked(in_array($token, array('400:normal', '700:normal'), true)); ?> />
                                            <?php echo esc_html($this->variant_label($token)); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                                <label class="am24h-inline-control" for="am24h_activate_after_import">
                                    <input type="checkbox" id="am24h_activate_after_import" name="am24h_activate_after_import" value="1" checked />
                                    <span><?php esc_html_e('Activate this font after import', 'am24h'); ?></span>
                                </label>

                                <div class="am24h-actions" style="justify-content:flex-start;">
                                    <button type="submit" class="button button-primary"><?php esc_html_e('Download and Install Locally', 'am24h'); ?></button>
                                </div>
                            </form>
                        </section>
                    <?php endif; ?>

                    <section class="am24h-card" style="margin-top:16px;">
                        <h2><?php esc_html_e('Installed Fonts', 'am24h'); ?></h2>

                        <?php if (empty($installed_fonts)) : ?>
                            <p><?php esc_html_e('No fonts installed yet.', 'am24h'); ?></p>
                        <?php else : ?>
                            <table class="widefat striped">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Family', 'am24h'); ?></th>
                                        <th><?php esc_html_e('Variants', 'am24h'); ?></th>
                                        <th><?php esc_html_e('Status', 'am24h'); ?></th>
                                        <th><?php esc_html_e('Actions', 'am24h'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($installed_fonts as $font_id => $font) : ?>
                                        <tr>
                                            <td><?php echo esc_html((string) ($font['family'] ?? $font_id)); ?></td>
                                            <td><?php echo esc_html((string) count((array) ($font['variants'] ?? array()))); ?></td>
                                            <td><?php echo $font_id === $active_font_id ? esc_html__('Active', 'am24h') : esc_html__('Installed', 'am24h'); ?></td>
                                            <td>
                                                <?php if ($font_id !== $active_font_id) : ?>
                                                    <form method="post" action="" style="display:inline-block; margin-right:8px;">
                                                        <?php wp_nonce_field('am24h_typography_action', 'am24h_typography_nonce'); ?>
                                                        <input type="hidden" name="am24h_typography_action" value="activate_font" />
                                                        <input type="hidden" name="am24h_font_id" value="<?php echo esc_attr($font_id); ?>" />
                                                        <button type="submit" class="button button-secondary"><?php esc_html_e('Activate', 'am24h'); ?></button>
                                                    </form>
                                                <?php endif; ?>
                                                <form method="post" action="" style="display:inline-block;">
                                                    <?php wp_nonce_field('am24h_typography_action', 'am24h_typography_nonce'); ?>
                                                    <input type="hidden" name="am24h_typography_action" value="remove_font" />
                                                    <input type="hidden" name="am24h_font_id" value="<?php echo esc_attr($font_id); ?>" />
                                                    <button type="submit" class="button button-link-delete" onclick="return confirm('<?php echo esc_js(__('Remove this local font and its files from uploads?', 'am24h')); ?>');"><?php esc_html_e('Remove', 'am24h'); ?></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </section>

                    <section class="am24h-card" style="margin-top:16px;">
                        <h2><?php esc_html_e('Typography Preferences', 'am24h'); ?></h2>
                        <form method="post" action="" class="am24h-panel-form">
                            <?php wp_nonce_field('am24h_typography_action', 'am24h_typography_nonce'); ?>
                            <input type="hidden" name="am24h_typography_action" value="update_preferences" />

                            <div class="am24h-field">
                                <label for="am24h_fallback_stack"><?php esc_html_e('Fallback stack', 'am24h'); ?></label>
                                <input type="text" id="am24h_fallback_stack" name="am24h_fallback_stack" class="regular-text" value="<?php echo esc_attr($this->settings->get_fallback_stack()); ?>" />
                                <p class="description"><?php esc_html_e('Used before custom font loads. Keep this stack metrics-compatible to reduce CLS.', 'am24h'); ?></p>
                            </div>

                            <label class="am24h-inline-control" for="am24h_preload_enabled">
                                <input type="checkbox" id="am24h_preload_enabled" name="am24h_preload_enabled" value="1" <?php checked($this->settings->is_preload_enabled()); ?> />
                                <span><?php esc_html_e('Preload only primary WOFF2 file (optional optimization)', 'am24h'); ?></span>
                            </label>

                            <div class="am24h-actions" style="justify-content:flex-start;">
                                <button type="submit" class="button button-primary"><?php esc_html_e('Save Typography Preferences', 'am24h'); ?></button>
                            </div>
                        </form>
                    </section>
                </main>
            </div>
        </div>

        <?php
    }

    private function handle_import(): void
    {
        $family = isset($_POST['am24h_font_family']) ? $this->validator->sanitize_family_name(wp_unslash($_POST['am24h_font_family'])) : '';
        $variants_payload = isset($_POST['am24h_font_variants']) ? wp_unslash($_POST['am24h_font_variants']) : array();
        $variants = $this->validator->sanitize_variant_payload($variants_payload);

        $existing_slug = $this->validator->family_slug($family);

        if ($existing_slug !== '' && isset($this->registry->all()[$existing_slug])) {
            $this->registry->remove($existing_slug);
        }

        $import = $this->downloader->import_family($family, $variants);

        if (is_wp_error($import)) {
            add_settings_error('am24h_typography_settings', 'am24h_typography_import_error', $import->get_error_message(), 'error');
            return;
        }

        $this->registry->upsert(
            array(
                'family'       => $import['family'],
                'slug'         => $import['slug'],
                'variants'     => $import['variants'],
                'preload_file' => $import['preload_file'],
                'created_at'   => time(),
            )
        );

        $activate = isset($_POST['am24h_activate_after_import']) && (int) wp_unslash($_POST['am24h_activate_after_import']) === 1;

        if ($activate) {
            $this->registry->activate($import['slug']);
        }

        add_settings_error('am24h_typography_settings', 'am24h_typography_import_success', __('Font downloaded, validated, and installed locally.', 'am24h'), 'updated');

        if (! empty($import['contains_non_woff2'])) {
            add_settings_error('am24h_typography_settings', 'am24h_typography_import_format_notice', __('Installed variants include WOFF fallback files. Prefer WOFF2 variants in production when available to reduce payload and improve render performance.', 'am24h'), 'warning');
        }
    }

    private function handle_activate(): void
    {
        $font_id = isset($_POST['am24h_font_id']) ? sanitize_key(wp_unslash($_POST['am24h_font_id'])) : '';

        if ($font_id === '' || ! $this->registry->activate($font_id)) {
            add_settings_error('am24h_typography_settings', 'am24h_typography_activate_error', __('Unable to activate selected font.', 'am24h'), 'error');
            return;
        }

        add_settings_error('am24h_typography_settings', 'am24h_typography_activate_success', __('Font activated successfully.', 'am24h'), 'updated');
    }

    private function handle_remove(): void
    {
        $font_id = isset($_POST['am24h_font_id']) ? sanitize_key(wp_unslash($_POST['am24h_font_id'])) : '';

        if ($font_id === '' || ! $this->registry->remove($font_id)) {
            add_settings_error('am24h_typography_settings', 'am24h_typography_remove_error', __('Unable to remove selected font.', 'am24h'), 'error');
            return;
        }

        add_settings_error('am24h_typography_settings', 'am24h_typography_remove_success', __('Font removed from registry and uploads.', 'am24h'), 'updated');
    }

    private function handle_preferences_update(): void
    {
        $fallback = isset($_POST['am24h_fallback_stack']) ? sanitize_text_field(wp_unslash($_POST['am24h_fallback_stack'])) : '';
        $preload_enabled = isset($_POST['am24h_preload_enabled']) && (int) wp_unslash($_POST['am24h_preload_enabled']) === 1;

        $this->settings->update_fallback_stack($fallback);
        $this->settings->set_preload_enabled($preload_enabled);

        add_settings_error('am24h_typography_settings', 'am24h_typography_preferences_saved', __('Typography preferences updated.', 'am24h'), 'updated');
    }

    private function is_valid_nonce(): bool
    {
        if (! isset($_POST['am24h_typography_nonce'])) {
            return false;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST['am24h_typography_nonce']));

        return wp_verify_nonce($nonce, 'am24h_typography_action') !== false;
    }

    private function variant_token_from_google(string $variant): string
    {
        $variant = strtolower(trim($variant));

        if ($variant === 'regular') {
            return '400:normal';
        }

        if ($variant === 'italic') {
            return '400:italic';
        }

        if (preg_match('/^([1-9]00)(italic)?$/', $variant, $matches)) {
            $weight = (int) $matches[1];
            $style = isset($matches[2]) && $matches[2] === 'italic' ? 'italic' : 'normal';

            return $weight . ':' . $style;
        }

        return '';
    }

    private function variant_label(string $token): string
    {
        list($weight, $style) = explode(':', $token, 2);

        return sprintf('%s %s', $weight, ucfirst($style));
    }
}
