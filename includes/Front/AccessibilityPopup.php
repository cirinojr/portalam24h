<?php

class Am24h_AccessibilityPopup
{
    private Am24h_ThemeOptionsRepository $options;
    private Am24h_AssetLocator $assets;

    public function __construct(Am24h_ThemeOptionsRepository $options, Am24h_AssetLocator $assets)
    {
        $this->options = $options;
        $this->assets = $assets;
    }

    public function register_hooks(): void
    {
        if (! $this->is_enabled()) {
            return;
        }

        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'), 35);
        add_action('wp_footer', array($this, 'render_popup'), 26);
    }

    public function enqueue_assets(): void
    {
        if (! $this->is_enabled()) {
            return;
        }

        $style_relative = 'assets/styles/Components/accessibility-popup.css';

        if ($this->assets->is_readable($style_relative)) {
            wp_enqueue_style(
                'am24h-accessibility-popup',
                $this->assets->url($style_relative),
                array(),
                $this->assets->version($style_relative),
                'all'
            );
        }

        $script_relative = 'assets/js/accessibility-popup.js';

        if (! $this->assets->is_readable($script_relative)) {
            return;
        }

        wp_enqueue_script(
            'am24h-accessibility-popup',
            $this->assets->url($script_relative),
            array(),
            $this->assets->version($script_relative),
            true
        );

        wp_script_add_data('am24h-accessibility-popup', 'strategy', 'defer');
    }

    public function render_popup(): void
    {
        if (! $this->is_enabled()) {
            return;
        }

        $settings = $this->sanitize_settings($this->options->get_accessibility_popup_settings());
        $position_class = 'am24h-accessibility-launcher--' . $settings['trigger_position'];
        $features = $this->parse_features($settings['features']);
        $enabled_tools = array_values(array_keys(array_filter($settings['tools'])));
        $dialog_id = 'am24h-accessibility-popup-dialog';
        ?>
        <button
            type="button"
            class="am24h-accessibility-launcher <?php echo esc_attr($position_class); ?>"
            data-accessibility-open
            aria-label="<?php echo esc_attr($settings['trigger_label']); ?>"
            aria-controls="<?php echo esc_attr($dialog_id); ?>"
            aria-expanded="false"
        >
            <span class="am24h-accessibility-launcher__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" focusable="false" role="img" aria-hidden="true">
                    <circle cx="12" cy="4" r="2"></circle>
                    <path d="M7.8 8.1a1 1 0 0 1 1.1-.8h6.2a1 1 0 1 1 0 2H13v3.8l4.2 6a1 1 0 0 1-1.6 1.2L12 15.3l-3.6 4.9a1 1 0 1 1-1.6-1.2l4.2-6V9.3H8.9a1 1 0 0 1-1.1-1.2Z"></path>
                </svg>
            </span>
            <span class="am24h-accessibility-launcher__label"><?php echo esc_html($settings['trigger_label']); ?></span>
        </button>

        <div class="am24h-accessibility-popup" data-accessibility-popup data-a11y-enabled-tools="<?php echo esc_attr(wp_json_encode($enabled_tools)); ?>" hidden>
            <div class="am24h-accessibility-popup__backdrop" data-accessibility-close></div>
            <section
                id="<?php echo esc_attr($dialog_id); ?>"
                class="am24h-accessibility-popup__dialog"
                role="dialog"
                aria-modal="true"
                aria-labelledby="am24h-accessibility-popup-title"
                aria-describedby="am24h-accessibility-popup-description"
                tabindex="-1"
            >
                <header class="am24h-accessibility-popup__header">
                    <h2 id="am24h-accessibility-popup-title"><?php echo esc_html($settings['title']); ?></h2>
                    <button type="button" class="am24h-accessibility-popup__close" data-accessibility-close aria-label="<?php echo esc_attr($settings['close_label']); ?>">
                        <?php echo esc_html($settings['close_label']); ?>
                    </button>
                </header>

                <p class="am24h-accessibility-popup__description" id="am24h-accessibility-popup-description"><?php echo esc_html($settings['description']); ?></p>

                <div class="am24h-accessibility-popup__controls" aria-label="<?php echo esc_attr__('Accessibility adjustments', 'am24h'); ?>">
                    <section class="am24h-accessibility-popup__group" aria-labelledby="am24h-accessibility-group-font">
                        <h3 id="am24h-accessibility-group-font"><?php esc_html_e('Font Controls', 'am24h'); ?></h3>
                        <div class="am24h-accessibility-popup__control-row" role="group" aria-label="<?php echo esc_attr__('Font Controls', 'am24h'); ?>">
                            <?php if ($settings['tools']['font_size']) : ?>
                                <?php $this->render_action_button('decrease-font', __('Decrease font size', 'am24h')); ?>
                                <?php $this->render_action_button('increase-font', __('Increase font size', 'am24h')); ?>
                                <?php $this->render_action_button('reset-font', __('Reset font size', 'am24h')); ?>
                            <?php endif; ?>

                            <?php if ($settings['tools']['line_height']) : ?>
                                <?php $this->render_action_button('toggle-line-height', __('Increase line height', 'am24h'), 'line-height'); ?>
                            <?php endif; ?>

                            <?php if ($settings['tools']['letter_spacing']) : ?>
                                <?php $this->render_action_button('toggle-letter-spacing', __('Increase letter spacing', 'am24h'), 'letter-spacing'); ?>
                            <?php endif; ?>

                            <?php if ($settings['tools']['readable_font']) : ?>
                                <?php $this->render_action_button('toggle-readable-font', __('Readable font', 'am24h'), 'readable-font'); ?>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="am24h-accessibility-popup__group" aria-labelledby="am24h-accessibility-group-navigation">
                        <h3 id="am24h-accessibility-group-navigation"><?php esc_html_e('Navigation', 'am24h'); ?></h3>
                        <div class="am24h-accessibility-popup__control-row" role="group" aria-label="<?php echo esc_attr__('Navigation', 'am24h'); ?>">
                            <?php if ($settings['tools']['reading_mode']) : ?>
                                <?php $this->render_action_button('toggle-reading-mode', __('Reading mode', 'am24h'), 'reading-mode'); ?>
                            <?php endif; ?>

                            <?php if ($settings['tools']['reading_guide']) : ?>
                                <?php $this->render_action_button('toggle-reading-guide', __('Reading guide', 'am24h'), 'reading-guide'); ?>
                            <?php endif; ?>

                            <?php if ($settings['tools']['reading_mask']) : ?>
                                <?php $this->render_action_button('toggle-reading-mask', __('Reading mask', 'am24h'), 'reading-mask'); ?>
                            <?php endif; ?>

                            <?php if ($settings['tools']['highlight_links']) : ?>
                                <?php $this->render_action_button('toggle-highlight-links', __('Highlight links', 'am24h'), 'highlight-links'); ?>
                            <?php endif; ?>

                            <?php if ($settings['tools']['highlight_headings']) : ?>
                                <?php $this->render_action_button('toggle-highlight-headings', __('Highlight headings', 'am24h'), 'highlight-headings'); ?>
                            <?php endif; ?>

                            <?php if ($settings['tools']['hide_images']) : ?>
                                <?php $this->render_action_button('toggle-hide-images', __('Hide images', 'am24h'), 'hide-images'); ?>
                            <?php endif; ?>

                            <?php if ($settings['tools']['pause_animations']) : ?>
                                <?php $this->render_action_button('toggle-pause-animations', __('Pause animations', 'am24h'), 'pause-animations'); ?>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="am24h-accessibility-popup__group" aria-labelledby="am24h-accessibility-group-color">
                        <h3 id="am24h-accessibility-group-color"><?php esc_html_e('Color Controls', 'am24h'); ?></h3>
                        <div class="am24h-accessibility-popup__control-row" role="group" aria-label="<?php echo esc_attr__('Color Controls', 'am24h'); ?>">
                            <?php if ($settings['tools']['high_contrast']) : ?>
                                <?php $this->render_action_button('toggle-high-contrast', __('High contrast', 'am24h'), 'high-contrast'); ?>
                            <?php endif; ?>

                            <?php if ($settings['tools']['reduced_saturation']) : ?>
                                <?php $this->render_action_button('toggle-reduced-saturation', __('Reduced saturation', 'am24h'), 'reduced-saturation'); ?>
                            <?php endif; ?>

                            <?php if ($settings['tools']['grayscale']) : ?>
                                <?php $this->render_action_button('toggle-grayscale', __('Grayscale', 'am24h'), 'grayscale'); ?>
                            <?php endif; ?>
                        </div>
                    </section>

                    <div class="am24h-accessibility-popup__control-row" role="group" aria-label="<?php echo esc_attr__('Reset controls', 'am24h'); ?>">
                        <button type="button" class="am24h-accessibility-popup__action am24h-accessibility-popup__action--reset" data-a11y-action="reset-all"><?php esc_html_e('Reset Accessibility Settings', 'am24h'); ?></button>
                    </div>
                </div>

                <?php if (! empty($features)) : ?>
                    <ul class="am24h-accessibility-popup__features">
                        <?php foreach ($features as $feature) : ?>
                            <li><?php echo esc_html($feature); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </div>
        <?php
    }

    private function is_enabled(): bool
    {
        return $this->options->get_bool('am24h_accessibility_popup_enabled');
    }

    /**
     * @param array{enabled: bool, title: string, description: string, trigger_label: string, close_label: string, trigger_position: string, tools: array<string, bool>, features: string} $settings
     * @return array{enabled: bool, title: string, description: string, trigger_label: string, close_label: string, trigger_position: string, tools: array<string, bool>, features: string}
     */
    private function sanitize_settings(array $settings): array
    {
        $title = sanitize_text_field($settings['title']);
        $description = sanitize_text_field($settings['description']);
        $trigger_label = sanitize_text_field($settings['trigger_label']);
        $close_label = sanitize_text_field($settings['close_label']);
        $features = (string) $settings['features'];

        $position = sanitize_key($settings['trigger_position']);

        if (! in_array($position, array('bottom-right', 'bottom-left', 'top-right', 'top-left'), true)) {
            $position = 'bottom-right';
        }

        return array(
            'enabled' => (bool) $settings['enabled'],
            'title' => $title !== '' ? $title : __('Accessibility Help', 'am24h'),
            'description' => $description !== '' ? $description : __('Choose tools that make reading and navigation more comfortable for you.', 'am24h'),
            'trigger_label' => $trigger_label !== '' ? $trigger_label : __('Accessibility', 'am24h'),
            'close_label' => $close_label !== '' ? $close_label : __('Close', 'am24h'),
            'trigger_position' => $position,
            'tools' => $this->sanitize_tools(isset($settings['tools']) && is_array($settings['tools']) ? $settings['tools'] : array()),
            'features' => $features,
        );
    }

    /**
     * @param array<string, bool> $tools
     * @return array<string, bool>
     */
    private function sanitize_tools(array $tools): array
    {
        $defaults = array(
            'font_size' => true,
            'line_height' => true,
            'letter_spacing' => true,
            'readable_font' => true,
            'reading_mode' => true,
            'reading_guide' => true,
            'reading_mask' => true,
            'highlight_links' => true,
            'highlight_headings' => true,
            'hide_images' => true,
            'pause_animations' => true,
            'high_contrast' => true,
            'reduced_saturation' => true,
            'grayscale' => true,
        );

        foreach ($defaults as $key => $default_value) {
            if (array_key_exists($key, $tools)) {
                $defaults[$key] = (bool) $tools[$key];
            } else {
                $defaults[$key] = $default_value;
            }
        }

        return $defaults;
    }

    private function render_action_button(string $action, string $label, string $toggle = ''): void
    {
        ?>
        <button
            type="button"
            class="am24h-accessibility-popup__action"
            data-a11y-action="<?php echo esc_attr($action); ?>"
            <?php if ($toggle !== '') : ?>data-a11y-toggle="<?php echo esc_attr($toggle); ?>" aria-pressed="false"<?php endif; ?>
        >
            <?php echo esc_html($label); ?>
        </button>
        <?php
    }

    /**
     * @return string[]
     */
    private function parse_features(string $features): array
    {
        $rows = explode("\n", str_replace("\r", '', $features));
        $output = array();

        foreach ($rows as $row) {
            $clean = trim(sanitize_text_field($row));

            if ($clean === '') {
                continue;
            }

            $output[] = $clean;

            if (count($output) >= 8) {
                break;
            }
        }

        return $output;
    }
}
