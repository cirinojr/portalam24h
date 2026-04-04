<?php

class Am24h_ThemeSettingsPage
{
    private const SUBMENU_ITEMS = array(
        array(
            'slug' => 'am24h-theme',
            'page_title' => 'General Settings',
            'menu_title' => 'General Settings',
            'callback' => 'render_general_page',
        ),
        array(
            'slug' => 'am24h-colors',
            'page_title' => 'Color Settings',
            'menu_title' => 'Colors',
            'callback' => 'render_colors_page',
        ),
        array(
            'slug' => 'am24h-language',
            'page_title' => 'Language Settings',
            'menu_title' => 'Language',
            'callback' => 'render_language_page',
        ),
        array(
            'slug' => 'am24h-cookies',
            'page_title' => 'LGPD / Cookie Notice',
            'menu_title' => 'LGPD / Cookies',
            'callback' => 'render_cookie_page',
        ),
        array(
            'slug' => 'am24h-accessibility',
            'page_title' => 'Accessibility Popup',
            'menu_title' => 'Accessibility',
            'callback' => 'render_accessibility_page',
        ),
    );

    private Am24h_ThemeOptionsRepository $options;
    private Am24h_SettingsSanitizer $sanitizer;

    public function __construct(Am24h_ThemeOptionsRepository $options, Am24h_SettingsSanitizer $sanitizer)
    {
        $this->options = $options;
        $this->sanitizer = $sanitizer;
    }

    public function register_hooks(): void
    {
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_init', array($this, 'handle_actions'));
        add_action('admin_notices', array($this, 'maybe_show_plugins_notice'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function register_menu(): void
    {
        add_menu_page(
            __('Theme Settings', 'am24h'),
            __('Portal Am24h', 'am24h'),
            'manage_options',
            'am24h-theme',
            array($this, 'render_general_page'),
            'dashicons-admin-appearance',
            30
        );

        foreach (self::SUBMENU_ITEMS as $item) {
            add_submenu_page(
                'am24h-theme',
                __($item['page_title'], 'am24h'),
                __($item['menu_title'], 'am24h'),
                'manage_options',
                $item['slug'],
                array($this, $item['callback'])
            );
        }
    }

    public function handle_actions(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $action = isset($_POST['am24h_admin_action']) ? sanitize_key(wp_unslash($_POST['am24h_admin_action'])) : '';

        if ($action === 'reset_colors' && $this->is_valid_action_nonce('am24h_reset_colors_nonce', 'am24h_reset_colors')) {
            delete_option('am24h_primary_color');
            delete_option('am24h_secondary_color');
            delete_option('am24h_text_color');
            delete_option('am24h_background_color');
            delete_option('am24h_success_color');
            delete_option('am24h_danger_color');
            add_settings_error('am24h_color_settings', 'am24h_colors_reset', __('Colors reset to defaults.', 'am24h'), 'updated');
        }

        if ($action === 'reset_language' && $this->is_valid_action_nonce('am24h_reset_language_nonce', 'am24h_reset_language')) {
            update_option('am24h_site_language', 'pt_BR');
            update_option('WPLANG', 'pt_BR');
            add_settings_error('am24h_language_settings', 'am24h_language_reset', __('Language reset to Portuguese (Brazil).', 'am24h'), 'updated');
        }

        if ($action === 'quick_change_language' && $this->is_valid_action_nonce('am24h_change_language_nonce', 'am24h_change_language')) {
            $selected = isset($_POST['am24h_site_language']) ? wp_unslash($_POST['am24h_site_language']) : '';
            $language = $this->sanitizer->sanitize_language($selected);
            update_option('am24h_site_language', $language);
            update_option('WPLANG', $language);
            add_settings_error(
                'am24h_language_settings',
                'am24h_language_changed',
                sprintf(
                    __('Language changed to %s.', 'am24h'),
                    esc_html(Am24h_LanguageCatalog::label($language))
                ),
                'updated'
            );
        }
    }

    public function maybe_show_plugins_notice(): void
    {
        $screen = get_current_screen();

        if (! $screen || $screen->base !== 'plugins') {
            return;
        }

        $settings_url = admin_url('admin.php?page=am24h-theme');
        echo '<div class="notice notice-info"><p><strong>' . esc_html__('Portal Am24h:', 'am24h') . '</strong> ';
        echo sprintf(
            wp_kses_post(__('Settings available at <a href="%s">Portal Am24h</a>.', 'am24h')),
            esc_url($settings_url)
        );
        echo '</p></div>';
    }

    public function render_general_page(): void
    {
        $selected_categories = $this->options->get_home_categories();
        $cleanup_flags = $this->options->get_cleanup_flags();
        $customize_logo_url = admin_url('customize.php?autofocus[section]=title_tagline');
        $logo_id = (int) get_theme_mod('custom_logo');
        $logo_preview = $logo_id > 0 ? wp_get_attachment_image($logo_id, 'thumbnail', false, array('style' => 'max-width:120px;height:auto;display:block;')) : '';
        $categories = get_categories(
            array(
                'orderby'    => 'name',
                'order'      => 'ASC',
                'hide_empty' => false,
            )
        );
        ?>
        <?php $this->render_panel_start(__('General Theme Settings', 'am24h'), __('Manage logo and editorial homepage configuration.', 'am24h')); ?>
            <?php settings_errors('am24h_theme_settings'); ?>

            <form method="post" action="options.php" class="am24h-panel-form">
                <?php settings_fields('am24h_theme_settings'); ?>

                <section class="am24h-card">
                    <h2><?php esc_html_e('Logo Settings', 'am24h'); ?></h2>

                    <div class="am24h-field">
                        <p class="description"><?php esc_html_e('Logo uses the native WordPress Customizer (Site Identity).', 'am24h'); ?></p>
                        <?php if ($logo_preview !== '') : ?>
                            <div><?php echo wp_kses_post($logo_preview); ?></div>
                        <?php else : ?>
                            <p class="description"><?php esc_html_e('No custom logo set. Site title will be used as fallback.', 'am24h'); ?></p>
                        <?php endif; ?>
                        <div class="am24h-actions" style="justify-content:flex-start;">
                            <a class="button button-primary" href="<?php echo esc_url($customize_logo_url); ?>"><?php esc_html_e('Open Customizer Logo Settings', 'am24h'); ?></a>
                        </div>
                    </div>
                </section>

                <section class="am24h-card">
                    <h2><?php esc_html_e('Homepage Curation', 'am24h'); ?></h2>
                    <div class="am24h-field">
                        <label for="am24h_home_categories"><?php esc_html_e('Home Page Categories', 'am24h'); ?></label>
                        <select name="am24h_home_categories[]" id="am24h_home_categories" multiple="multiple" class="am24h-select-multiple">
                            <?php foreach ($categories as $category) : ?>
                                <option value="<?php echo esc_attr((string) $category->term_id); ?>" <?php selected(in_array((int) $category->term_id, $selected_categories, true)); ?>>
                                    <?php echo esc_html($category->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select one or more categories used as primary blocks on the homepage.', 'am24h'); ?></p>
                    </div>
                </section>

                <section class="am24h-card">
                    <h2><?php esc_html_e('WordPress Cleanup', 'am24h'); ?></h2>
                    <p class="description"><?php esc_html_e('Keep enabled to remove non-essential WordPress output and keep front-end HTML cleaner.', 'am24h'); ?></p>

                    <div class="am24h-field am24h-checkbox-grid">
                        <label class="am24h-inline-control" for="am24h_cleanup_emojis">
                            <input name="am24h_cleanup_emojis" type="checkbox" id="am24h_cleanup_emojis" value="1" <?php checked(true, $cleanup_flags['am24h_cleanup_emojis']); ?> />
                            <span><?php esc_html_e('Remove emoji scripts and styles', 'am24h'); ?></span>
                        </label>

                        <label class="am24h-inline-control" for="am24h_cleanup_rsd">
                            <input name="am24h_cleanup_rsd" type="checkbox" id="am24h_cleanup_rsd" value="1" <?php checked(true, $cleanup_flags['am24h_cleanup_rsd']); ?> />
                            <span><?php esc_html_e('Remove RSD link', 'am24h'); ?></span>
                        </label>

                        <label class="am24h-inline-control" for="am24h_cleanup_generator">
                            <input name="am24h_cleanup_generator" type="checkbox" id="am24h_cleanup_generator" value="1" <?php checked(true, $cleanup_flags['am24h_cleanup_generator']); ?> />
                            <span><?php esc_html_e('Remove WordPress generator meta', 'am24h'); ?></span>
                        </label>

                        <label class="am24h-inline-control" for="am24h_cleanup_feed_links">
                            <input name="am24h_cleanup_feed_links" type="checkbox" id="am24h_cleanup_feed_links" value="1" <?php checked(true, $cleanup_flags['am24h_cleanup_feed_links']); ?> />
                            <span><?php esc_html_e('Remove feed links', 'am24h'); ?></span>
                        </label>

                        <label class="am24h-inline-control" for="am24h_cleanup_wlwmanifest">
                            <input name="am24h_cleanup_wlwmanifest" type="checkbox" id="am24h_cleanup_wlwmanifest" value="1" <?php checked(true, $cleanup_flags['am24h_cleanup_wlwmanifest']); ?> />
                            <span><?php esc_html_e('Remove Windows Live Writer manifest', 'am24h'); ?></span>
                        </label>

                        <label class="am24h-inline-control" for="am24h_cleanup_prev_next_links">
                            <input name="am24h_cleanup_prev_next_links" type="checkbox" id="am24h_cleanup_prev_next_links" value="1" <?php checked(true, $cleanup_flags['am24h_cleanup_prev_next_links']); ?> />
                            <span><?php esc_html_e('Remove adjacent post rel links', 'am24h'); ?></span>
                        </label>

                        <label class="am24h-inline-control" for="am24h_cleanup_shortlink">
                            <input name="am24h_cleanup_shortlink" type="checkbox" id="am24h_cleanup_shortlink" value="1" <?php checked(true, $cleanup_flags['am24h_cleanup_shortlink']); ?> />
                            <span><?php esc_html_e('Remove shortlink tag', 'am24h'); ?></span>
                        </label>

                        <label class="am24h-inline-control" for="am24h_cleanup_rest_links">
                            <input name="am24h_cleanup_rest_links" type="checkbox" id="am24h_cleanup_rest_links" value="1" <?php checked(true, $cleanup_flags['am24h_cleanup_rest_links']); ?> />
                            <span><?php esc_html_e('Remove REST API discovery links', 'am24h'); ?></span>
                        </label>

                        <label class="am24h-inline-control" for="am24h_cleanup_oembed_links">
                            <input name="am24h_cleanup_oembed_links" type="checkbox" id="am24h_cleanup_oembed_links" value="1" <?php checked(true, $cleanup_flags['am24h_cleanup_oembed_links']); ?> />
                            <span><?php esc_html_e('Remove oEmbed discovery links', 'am24h'); ?></span>
                        </label>

                        <label class="am24h-inline-control" for="am24h_cleanup_admin_bar">
                            <input name="am24h_cleanup_admin_bar" type="checkbox" id="am24h_cleanup_admin_bar" value="1" <?php checked(true, $cleanup_flags['am24h_cleanup_admin_bar']); ?> />
                            <span><?php esc_html_e('Hide front-end admin bar', 'am24h'); ?></span>
                        </label>

                        <label class="am24h-inline-control" for="am24h_cleanup_block_styles_on_demand">
                            <input name="am24h_cleanup_block_styles_on_demand" type="checkbox" id="am24h_cleanup_block_styles_on_demand" value="1" <?php checked(true, $cleanup_flags['am24h_cleanup_block_styles_on_demand']); ?> />
                            <span><?php esc_html_e('Load Gutenberg core block styles on demand', 'am24h'); ?></span>
                        </label>

                        <label class="am24h-inline-control" for="am24h_cleanup_block_styles">
                            <input name="am24h_cleanup_block_styles" type="checkbox" id="am24h_cleanup_block_styles" value="1" <?php checked(true, $cleanup_flags['am24h_cleanup_block_styles']); ?> />
                            <span><?php esc_html_e('Disable Gutenberg core block styles (advanced)', 'am24h'); ?></span>
                        </label>
                        <p class="description"><?php esc_html_e('Use full disable only if your theme fully styles every block used in content. On-demand loading is safer for mixed or evolving block usage.', 'am24h'); ?></p>

                        <label class="am24h-inline-control" for="am24h_cleanup_multilingualpress_hreflang">
                            <input name="am24h_cleanup_multilingualpress_hreflang" type="checkbox" id="am24h_cleanup_multilingualpress_hreflang" value="1" <?php checked(true, $cleanup_flags['am24h_cleanup_multilingualpress_hreflang']); ?> />
                            <span><?php esc_html_e('Disable MultilingualPress hreflang output', 'am24h'); ?></span>
                        </label>
                    </div>
                </section>

                <div class="am24h-actions">
                    <?php submit_button(__('Save Changes', 'am24h'), 'primary', 'submit', false); ?>
                </div>
            </form>

        <?php $this->render_panel_end(); ?>
        <?php
    }

    public function render_colors_page(): void
    {
        $colors = $this->options->get_color_set();
        $defer_visual_overrides = $this->options->should_defer_visual_overrides();
        ?>
        <?php $this->render_panel_start(__('Color Settings', 'am24h'), __('Tune the visual identity of the theme with a controlled color system.', 'am24h')); ?>
            <?php settings_errors('am24h_color_settings'); ?>

            <form method="post" action="options.php" class="am24h-panel-form">
                <?php settings_fields('am24h_color_settings'); ?>

                <section class="am24h-card am24h-color-grid">
                    <label class="am24h-color-item"><?php esc_html_e('Primary Color', 'am24h'); ?><input type="color" name="am24h_primary_color" value="<?php echo esc_attr($colors['primary']); ?>" /></label>
                    <label class="am24h-color-item"><?php esc_html_e('Secondary Color', 'am24h'); ?><input type="color" name="am24h_secondary_color" value="<?php echo esc_attr($colors['secondary']); ?>" /></label>
                    <label class="am24h-color-item"><?php esc_html_e('Text Color', 'am24h'); ?><input type="color" name="am24h_text_color" value="<?php echo esc_attr($colors['text']); ?>" /></label>
                    <label class="am24h-color-item"><?php esc_html_e('Background Color', 'am24h'); ?><input type="color" name="am24h_background_color" value="<?php echo esc_attr($colors['background']); ?>" /></label>
                    <label class="am24h-color-item"><?php esc_html_e('Success Color', 'am24h'); ?><input type="color" name="am24h_success_color" value="<?php echo esc_attr($colors['success']); ?>" /></label>
                    <label class="am24h-color-item"><?php esc_html_e('Danger Color', 'am24h'); ?><input type="color" name="am24h_danger_color" value="<?php echo esc_attr($colors['danger']); ?>" /></label>
                </section>

                <section class="am24h-card">
                    <h2><?php esc_html_e('Render Strategy', 'am24h'); ?></h2>
                    <label class="am24h-inline-control" for="am24h_defer_visual_overrides">
                        <input name="am24h_defer_visual_overrides" type="checkbox" id="am24h_defer_visual_overrides" value="1" <?php checked(true, $defer_visual_overrides); ?> />
                        <span><?php esc_html_e('Load custom colors and fonts after initial critical render', 'am24h'); ?></span>
                    </label>
                    <p class="description"><?php esc_html_e('Keeps critical CSS stable as a base skeleton and applies personalized appearance after first paint.', 'am24h'); ?></p>
                </section>

                <div class="am24h-actions">
                    <?php submit_button(__('Save Changes', 'am24h'), 'primary', 'submit', false); ?>
                </div>
            </form>

            <form method="post" action="" class="am24h-reset-form">
                <?php wp_nonce_field('am24h_reset_colors', 'am24h_reset_colors_nonce'); ?>
                <input type="hidden" name="am24h_admin_action" value="reset_colors" />
                <?php submit_button(__('Reset Colors', 'am24h'), 'secondary', 'submit', false); ?>
            </form>
        <?php $this->render_panel_end(); ?>
        <?php
    }

    public function render_cookie_page(): void
    {
        $cookie_consent = $this->options->get_cookie_consent_settings();
        ?>
        <?php $this->render_panel_start(__('LGPD / Cookie Notice', 'am24h'), __('Configure an optional lightweight consent banner for frontend notices.', 'am24h')); ?>
            <?php settings_errors('am24h_theme_settings'); ?>

            <form method="post" action="options.php" class="am24h-panel-form">
                <?php settings_fields('am24h_theme_settings'); ?>

                <section id="am24h-cookie-consent-settings" class="am24h-card">
                    <h2><?php esc_html_e('Banner Settings', 'am24h'); ?></h2>
                    <p class="description"><?php esc_html_e('Optional lightweight consent notice. Disabled by default and intended for basic consent UI only.', 'am24h'); ?></p>

                    <label class="am24h-inline-control" for="am24h_cookie_consent_enabled">
                        <input type="hidden" name="am24h_cookie_consent_enabled" value="0" />
                        <input name="am24h_cookie_consent_enabled" type="checkbox" id="am24h_cookie_consent_enabled" value="1" <?php checked(true, (bool) $cookie_consent['enabled']); ?> />
                        <span><?php esc_html_e('Enable cookie consent banner', 'am24h'); ?></span>
                    </label>

                    <div class="am24h-field">
                        <label for="am24h_cookie_consent_message"><?php esc_html_e('Banner message', 'am24h'); ?></label>
                        <input type="text" class="regular-text" id="am24h_cookie_consent_message" name="am24h_cookie_consent_message" value="<?php echo esc_attr($cookie_consent['message']); ?>" maxlength="280" />
                    </div>

                    <div class="am24h-field">
                        <label for="am24h_cookie_consent_mode"><?php esc_html_e('Banner mode', 'am24h'); ?></label>
                        <select id="am24h_cookie_consent_mode" name="am24h_cookie_consent_mode" class="am24h-select-single">
                            <option value="choice" <?php selected($cookie_consent['mode'], 'choice'); ?>><?php esc_html_e('Accept and Reject', 'am24h'); ?></option>
                            <option value="informational" <?php selected($cookie_consent['mode'], 'informational'); ?>><?php esc_html_e('Informational (Accept + Close)', 'am24h'); ?></option>
                        </select>
                    </div>

                    <div class="am24h-field">
                        <label for="am24h_cookie_consent_accept_label"><?php esc_html_e('Accept button label', 'am24h'); ?></label>
                        <input type="text" class="regular-text" id="am24h_cookie_consent_accept_label" name="am24h_cookie_consent_accept_label" value="<?php echo esc_attr($cookie_consent['accept_label']); ?>" maxlength="60" />
                    </div>

                    <div class="am24h-field">
                        <label for="am24h_cookie_consent_reject_label"><?php esc_html_e('Reject / Close button label', 'am24h'); ?></label>
                        <input type="text" class="regular-text" id="am24h_cookie_consent_reject_label" name="am24h_cookie_consent_reject_label" value="<?php echo esc_attr($cookie_consent['reject_label']); ?>" maxlength="60" />
                    </div>

                    <div class="am24h-field">
                        <label for="am24h_cookie_consent_policy_url"><?php esc_html_e('Privacy policy URL', 'am24h'); ?></label>
                        <input type="url" class="regular-text" id="am24h_cookie_consent_policy_url" name="am24h_cookie_consent_policy_url" value="<?php echo esc_attr($cookie_consent['policy_url']); ?>" placeholder="https://example.com/privacy-policy" />
                    </div>

                    <div class="am24h-field">
                        <label for="am24h_cookie_consent_policy_label"><?php esc_html_e('Privacy policy link label', 'am24h'); ?></label>
                        <input type="text" class="regular-text" id="am24h_cookie_consent_policy_label" name="am24h_cookie_consent_policy_label" value="<?php echo esc_attr($cookie_consent['policy_label']); ?>" maxlength="60" />
                    </div>

                    <div class="am24h-field">
                        <label for="am24h_cookie_consent_position"><?php esc_html_e('Banner position', 'am24h'); ?></label>
                        <select id="am24h_cookie_consent_position" name="am24h_cookie_consent_position" class="am24h-select-single">
                            <option value="bottom-full" <?php selected($cookie_consent['position'], 'bottom-full'); ?>><?php esc_html_e('Bottom full width', 'am24h'); ?></option>
                            <option value="top-full" <?php selected($cookie_consent['position'], 'top-full'); ?>><?php esc_html_e('Top full width', 'am24h'); ?></option>
                            <option value="bottom-left" <?php selected($cookie_consent['position'], 'bottom-left'); ?>><?php esc_html_e('Bottom left', 'am24h'); ?></option>
                            <option value="bottom-right" <?php selected($cookie_consent['position'], 'bottom-right'); ?>><?php esc_html_e('Bottom right', 'am24h'); ?></option>
                            <option value="bottom-center" <?php selected($cookie_consent['position'], 'bottom-center'); ?>><?php esc_html_e('Floating center bottom', 'am24h'); ?></option>
                        </select>
                    </div>

                    <div class="am24h-field">
                        <label for="am24h_cookie_consent_variant"><?php esc_html_e('Style variant', 'am24h'); ?></label>
                        <select id="am24h_cookie_consent_variant" name="am24h_cookie_consent_variant" class="am24h-select-single">
                            <option value="light" <?php selected($cookie_consent['variant'], 'light'); ?>><?php esc_html_e('Light', 'am24h'); ?></option>
                            <option value="dark" <?php selected($cookie_consent['variant'], 'dark'); ?>><?php esc_html_e('Dark', 'am24h'); ?></option>
                        </select>
                    </div>
                </section>

                <div class="am24h-actions">
                    <?php submit_button(__('Save Changes', 'am24h'), 'primary', 'submit', false); ?>
                </div>
            </form>

        <?php $this->render_panel_end(); ?>
        <?php
    }

    public function render_language_page(): void
    {
        $current_language = $this->options->get_site_language();
        $labels = Am24h_LanguageCatalog::labels();
        ?>
        <?php $this->render_panel_start(__('Language Settings', 'am24h'), __('Control active locale for the theme interface and content labels.', 'am24h')); ?>
            <?php settings_errors('am24h_language_settings'); ?>

            <form method="post" action="options.php" class="am24h-panel-form">
                <?php settings_fields('am24h_language_settings'); ?>
                <section class="am24h-card">
                    <div class="am24h-field">
                        <label for="am24h_site_language"><?php esc_html_e('Site Language', 'am24h'); ?></label>
                        <select name="am24h_site_language" id="am24h_site_language" class="am24h-select-single">
                            <?php foreach ($labels as $code => $label) : ?>
                                <option value="<?php echo esc_attr($code); ?>" <?php selected($current_language, $code); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </section>
                <div class="am24h-actions">
                    <?php submit_button(__('Save Changes', 'am24h'), 'primary', 'submit', false); ?>
                </div>
            </form>

            <section class="am24h-card">
                <h2><?php esc_html_e('Quick Language Switch', 'am24h'); ?></h2>
                <div class="am24h-quick-switch">
                    <?php foreach (array('pt_BR', 'en_US', 'es_ES', 'fr_FR') as $quick_code) : ?>
                        <form method="post" action="">
                            <?php wp_nonce_field('am24h_change_language', 'am24h_change_language_nonce'); ?>
                            <input type="hidden" name="am24h_admin_action" value="quick_change_language" />
                            <input type="hidden" name="am24h_site_language" value="<?php echo esc_attr($quick_code); ?>" />
                            <button type="submit" class="button <?php echo $current_language === $quick_code ? 'button-primary' : 'button-secondary'; ?>">
                                <?php echo esc_html($labels[$quick_code]); ?>
                            </button>
                        </form>
                    <?php endforeach; ?>
                </div>
            </section>

            <form method="post" action="" class="am24h-reset-form">
                <?php wp_nonce_field('am24h_reset_language', 'am24h_reset_language_nonce'); ?>
                <input type="hidden" name="am24h_admin_action" value="reset_language" />
                <?php submit_button(__('Reset to Default Language', 'am24h'), 'secondary', 'submit', false); ?>
            </form>
        <?php $this->render_panel_end(); ?>
        <?php
    }

    public function render_accessibility_page(): void
    {
        $popup = $this->options->get_accessibility_popup_settings();
        ?>
        <?php $this->render_panel_start(__('Accessibility Adjustments', 'am24h'), __('Configure an optional lightweight accessibility adjustments panel for frontend users.', 'am24h')); ?>
            <?php settings_errors('am24h_theme_settings'); ?>

            <form method="post" action="options.php" class="am24h-panel-form">
                <?php settings_fields('am24h_theme_settings'); ?>

                <section class="am24h-card">
                    <h2><?php esc_html_e('Popup Settings', 'am24h'); ?></h2>
                    <p class="description"><?php esc_html_e('Lightweight informational popup only. Disabled by default.', 'am24h'); ?></p>

                    <label class="am24h-inline-control" for="am24h_accessibility_popup_enabled">
                        <input type="hidden" name="am24h_accessibility_popup_enabled" value="0" />
                        <input name="am24h_accessibility_popup_enabled" type="checkbox" id="am24h_accessibility_popup_enabled" value="1" <?php checked(true, (bool) $popup['enabled']); ?> />
                        <span><?php esc_html_e('Enable accessibility popup', 'am24h'); ?></span>
                    </label>

                    <div class="am24h-field">
                        <label for="am24h_accessibility_popup_title"><?php esc_html_e('Popup title', 'am24h'); ?></label>
                        <input type="text" class="regular-text" id="am24h_accessibility_popup_title" name="am24h_accessibility_popup_title" value="<?php echo esc_attr($popup['title']); ?>" maxlength="80" />
                    </div>

                    <div class="am24h-field">
                        <label for="am24h_accessibility_popup_description"><?php esc_html_e('Popup description', 'am24h'); ?></label>
                        <input type="text" class="regular-text" id="am24h_accessibility_popup_description" name="am24h_accessibility_popup_description" value="<?php echo esc_attr($popup['description']); ?>" maxlength="240" />
                    </div>

                    <div class="am24h-field">
                        <label for="am24h_accessibility_popup_trigger_label"><?php esc_html_e('Trigger button label', 'am24h'); ?></label>
                        <input type="text" class="regular-text" id="am24h_accessibility_popup_trigger_label" name="am24h_accessibility_popup_trigger_label" value="<?php echo esc_attr($popup['trigger_label']); ?>" maxlength="60" />
                    </div>

                    <div class="am24h-field">
                        <label for="am24h_accessibility_popup_close_label"><?php esc_html_e('Close button label', 'am24h'); ?></label>
                        <input type="text" class="regular-text" id="am24h_accessibility_popup_close_label" name="am24h_accessibility_popup_close_label" value="<?php echo esc_attr($popup['close_label']); ?>" maxlength="60" />
                    </div>

                    <div class="am24h-field">
                        <label for="am24h_accessibility_popup_trigger_position"><?php esc_html_e('Trigger button position', 'am24h'); ?></label>
                        <select id="am24h_accessibility_popup_trigger_position" name="am24h_accessibility_popup_trigger_position" class="am24h-select-single">
                            <option value="bottom-right" <?php selected($popup['trigger_position'], 'bottom-right'); ?>><?php esc_html_e('Bottom right', 'am24h'); ?></option>
                            <option value="bottom-left" <?php selected($popup['trigger_position'], 'bottom-left'); ?>><?php esc_html_e('Bottom left', 'am24h'); ?></option>
                            <option value="top-right" <?php selected($popup['trigger_position'], 'top-right'); ?>><?php esc_html_e('Top right', 'am24h'); ?></option>
                            <option value="top-left" <?php selected($popup['trigger_position'], 'top-left'); ?>><?php esc_html_e('Top left', 'am24h'); ?></option>
                        </select>
                    </div>

                    <div class="am24h-field">
                        <label for="am24h_accessibility_popup_features"><?php esc_html_e('Accessibility hints (one line per item)', 'am24h'); ?></label>
                        <textarea id="am24h_accessibility_popup_features" name="am24h_accessibility_popup_features" rows="6" class="large-text"><?php echo esc_textarea($popup['features']); ?></textarea>
                    </div>
                </section>

                <div class="am24h-actions">
                    <?php submit_button(__('Save Changes', 'am24h'), 'primary', 'submit', false); ?>
                </div>
            </form>

        <?php $this->render_panel_end(); ?>
        <?php
    }

    public function enqueue_admin_assets(): void
    {
        $screen = get_current_screen();

        if (! $screen || strpos($screen->id, 'am24h') === false) {
            return;
        }

        $relative = 'assets/styles/admin/admin-panel.css';
        $path = trailingslashit(get_template_directory()) . $relative;

        if (! is_readable($path)) {
            return;
        }

        wp_enqueue_style(
            'am24h-admin-panel',
            trailingslashit(get_template_directory_uri()) . $relative,
            array(),
            (string) filemtime($path)
        );
    }

    private function render_panel_start(string $title, string $subtitle): void
    {
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : 'am24h-theme';
        ?>
        <div class="wrap am24h-panel">
            <div class="am24h-panel-grid">
                <aside class="am24h-panel-nav">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=am24h-theme')); ?>" class="<?php echo $page === 'am24h-theme' ? 'am24h-active' : ''; ?>"><?php esc_html_e('General', 'am24h'); ?></a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=am24h-cookies')); ?>" class="<?php echo $page === 'am24h-cookies' ? 'am24h-active' : ''; ?>"><?php esc_html_e('LGPD / Cookies', 'am24h'); ?></a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=am24h-accessibility')); ?>" class="<?php echo $page === 'am24h-accessibility' ? 'am24h-active' : ''; ?>"><?php esc_html_e('Accessibility', 'am24h'); ?></a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=am24h-colors')); ?>" class="<?php echo $page === 'am24h-colors' ? 'am24h-active' : ''; ?>"><?php esc_html_e('Colors', 'am24h'); ?></a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=am24h-language')); ?>" class="<?php echo $page === 'am24h-language' ? 'am24h-active' : ''; ?>"><?php esc_html_e('Language', 'am24h'); ?></a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=am24h-typography')); ?>" class="<?php echo $page === 'am24h-typography' ? 'am24h-active' : ''; ?>"><?php esc_html_e('Typography', 'am24h'); ?></a>
                </aside>

                <main class="am24h-panel-main">
                    <header class="am24h-panel-head">
                        <div>
                            <h1><?php echo esc_html($title); ?></h1>
                            <p><?php echo esc_html($subtitle); ?></p>
                        </div>
                    </header>
        <?php
    }

    private function render_panel_end(): void
    {
        echo '</main></div></div>';
    }

    private function is_valid_action_nonce(string $field_name, string $action): bool
    {
        if (! isset($_POST[$field_name])) {
            return false;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST[$field_name]));

        return wp_verify_nonce($nonce, $action) !== false;
    }
}
