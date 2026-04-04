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
        $position_class = 'am24h-accessibility-trigger--' . $settings['trigger_position'];
        $features = $this->parse_features($settings['features']);
        $dialog_id = 'am24h-accessibility-popup-dialog';
        ?>
        <button
            type="button"
            class="am24h-accessibility-trigger <?php echo esc_attr($position_class); ?>"
            data-accessibility-open
            aria-controls="<?php echo esc_attr($dialog_id); ?>"
            aria-expanded="false"
        >
            <?php echo esc_html($settings['trigger_label']); ?>
        </button>

        <div class="am24h-accessibility-popup" data-accessibility-popup hidden>
            <div class="am24h-accessibility-popup__backdrop" data-accessibility-close></div>
            <section
                id="<?php echo esc_attr($dialog_id); ?>"
                class="am24h-accessibility-popup__dialog"
                role="dialog"
                aria-modal="true"
                aria-labelledby="am24h-accessibility-popup-title"
                tabindex="-1"
            >
                <header class="am24h-accessibility-popup__header">
                    <h2 id="am24h-accessibility-popup-title"><?php echo esc_html($settings['title']); ?></h2>
                    <button type="button" class="am24h-accessibility-popup__close" data-accessibility-close>
                        <?php echo esc_html($settings['close_label']); ?>
                    </button>
                </header>

                <p class="am24h-accessibility-popup__description"><?php echo esc_html($settings['description']); ?></p>

                <div class="am24h-accessibility-popup__controls" aria-label="<?php echo esc_attr__('Accessibility adjustments', 'am24h'); ?>">
                    <div class="am24h-accessibility-popup__control-row" role="group" aria-label="<?php echo esc_attr__('Font size controls', 'am24h'); ?>">
                        <button type="button" class="am24h-accessibility-popup__action" data-a11y-action="decrease-font"><?php esc_html_e('A-', 'am24h'); ?></button>
                        <button type="button" class="am24h-accessibility-popup__action" data-a11y-action="increase-font"><?php esc_html_e('A+', 'am24h'); ?></button>
                        <button type="button" class="am24h-accessibility-popup__action" data-a11y-action="reset-font"><?php esc_html_e('Reset Font', 'am24h'); ?></button>
                    </div>

                    <div class="am24h-accessibility-popup__control-row" role="group" aria-label="<?php echo esc_attr__('Visual modes', 'am24h'); ?>">
                        <button type="button" class="am24h-accessibility-popup__action" data-a11y-action="toggle-contrast" data-a11y-toggle="contrast" aria-pressed="false"><?php esc_html_e('High Contrast', 'am24h'); ?></button>
                        <button type="button" class="am24h-accessibility-popup__action" data-a11y-action="toggle-reading-bg" data-a11y-toggle="reading-bg" aria-pressed="false"><?php esc_html_e('Reading Background', 'am24h'); ?></button>
                        <button type="button" class="am24h-accessibility-popup__action" data-a11y-action="toggle-highlight-links" data-a11y-toggle="highlight-links" aria-pressed="false"><?php esc_html_e('Highlight Links', 'am24h'); ?></button>
                    </div>

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
     * @param array{enabled: bool, title: string, description: string, trigger_label: string, close_label: string, trigger_position: string, features: string} $settings
     * @return array{enabled: bool, title: string, description: string, trigger_label: string, close_label: string, trigger_position: string, features: string}
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
            'description' => $description !== '' ? $description : __('Use keyboard navigation and skip links to move through the page quickly.', 'am24h'),
            'trigger_label' => $trigger_label !== '' ? $trigger_label : __('Accessibility', 'am24h'),
            'close_label' => $close_label !== '' ? $close_label : __('Close', 'am24h'),
            'trigger_position' => $position,
            'features' => $features,
        );
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
