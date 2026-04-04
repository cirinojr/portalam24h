<?php

class Am24h_CookieConsentBanner
{
    private const CONSENT_COOKIE_NAME = 'am24h_cookie_consent';
    private const CONSENT_COOKIE_MAX_AGE = 31536000;

    private Am24h_ThemeOptionsRepository $options;
    private Am24h_AssetLocator $assets;

    public function __construct(Am24h_ThemeOptionsRepository $options, Am24h_AssetLocator $assets)
    {
        $this->options = $options;
        $this->assets = $assets;
    }

    public function register_hooks(): void
    {
        if (! $this->is_feature_enabled()) {
            return;
        }

        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'), 35);
        add_action('wp_footer', array($this, 'render_banner'), 25);
    }

    public function enqueue_assets(): void
    {
        if (! $this->should_render_banner()) {
            return;
        }

        $style_relative = 'assets/styles/Components/cookie-consent.css';

        if ($this->assets->is_readable($style_relative)) {
            wp_enqueue_style(
                'am24h-cookie-consent',
                $this->assets->url($style_relative),
                array(),
                $this->assets->version($style_relative),
                'all'
            );
        }

        $script_relative = 'assets/js/cookie-consent.js';

        if (! $this->assets->is_readable($script_relative)) {
            return;
        }

        wp_enqueue_script(
            'am24h-cookie-consent',
            $this->assets->url($script_relative),
            array(),
            $this->assets->version($script_relative),
            true
        );

        wp_script_add_data('am24h-cookie-consent', 'strategy', 'defer');
    }

    public function render_banner(): void
    {
        if (! $this->should_render_banner()) {
            return;
        }

        $settings = $this->sanitize_settings($this->options->get_cookie_consent_settings());
        $position_class = 'am24h-cookie-consent--' . $settings['position'];
        $variant_class = 'am24h-cookie-consent--' . $settings['variant'];
        $is_choice_mode = $settings['mode'] === 'choice';
        ?>
        <aside
            id="am24h-cookie-consent"
            class="am24h-cookie-consent <?php echo esc_attr($position_class); ?> <?php echo esc_attr($variant_class); ?>"
            role="region"
            aria-label="<?php echo esc_attr__('Cookie consent notice', 'am24h'); ?>"
            data-cookie-name="<?php echo esc_attr(self::CONSENT_COOKIE_NAME); ?>"
            data-cookie-max-age="<?php echo esc_attr((string) self::CONSENT_COOKIE_MAX_AGE); ?>"
            data-mode="<?php echo esc_attr($settings['mode']); ?>"
        >
            <div class="am24h-cookie-consent__content">
                <p class="am24h-cookie-consent__message"><?php echo esc_html($settings['message']); ?></p>
                <?php if ($settings['policy_url'] !== '' && $settings['policy_label'] !== '') : ?>
                    <p class="am24h-cookie-consent__policy">
                        <a href="<?php echo esc_url($settings['policy_url']); ?>"><?php echo esc_html($settings['policy_label']); ?></a>
                    </p>
                <?php endif; ?>
            </div>

            <div class="am24h-cookie-consent__actions">
                <button type="button" class="am24h-cookie-consent__btn am24h-cookie-consent__btn--primary" data-consent-action="accepted">
                    <?php echo esc_html($settings['accept_label']); ?>
                </button>
                <button type="button" class="am24h-cookie-consent__btn am24h-cookie-consent__btn--secondary" data-consent-action="<?php echo esc_attr($is_choice_mode ? 'rejected' : 'closed'); ?>">
                    <?php echo esc_html($settings['reject_label']); ?>
                </button>
            </div>
        </aside>
        <?php
    }

    private function should_render_banner(): bool
    {
        return $this->is_feature_enabled() && ! $this->has_stored_consent_choice();
    }

    private function is_feature_enabled(): bool
    {
        return $this->options->get_bool('am24h_cookie_consent_enabled');
    }

    private function has_stored_consent_choice(): bool
    {
        if (! isset($_COOKIE[self::CONSENT_COOKIE_NAME])) {
            return false;
        }

        $choice = sanitize_key(wp_unslash($_COOKIE[self::CONSENT_COOKIE_NAME]));

        return in_array($choice, array('accepted', 'rejected', 'closed'), true);
    }

    /**
     * @param array{enabled: bool, message: string, accept_label: string, reject_label: string, policy_url: string, policy_label: string, position: string, variant: string, mode: string} $settings
     * @return array{enabled: bool, message: string, accept_label: string, reject_label: string, policy_url: string, policy_label: string, position: string, variant: string, mode: string}
     */
    private function sanitize_settings(array $settings): array
    {
        $message = sanitize_text_field($settings['message']);
        $accept_label = sanitize_text_field($settings['accept_label']);
        $reject_label = sanitize_text_field($settings['reject_label']);
        $policy_url = esc_url_raw($settings['policy_url']);
        $policy_label = sanitize_text_field($settings['policy_label']);

        $position = sanitize_key($settings['position']);
        $allowed_positions = array('bottom-full', 'top-full', 'bottom-left', 'bottom-right', 'bottom-center');

        if (! in_array($position, $allowed_positions, true)) {
            $position = 'bottom-full';
        }

        $variant = sanitize_key($settings['variant']);

        if (! in_array($variant, array('light', 'dark'), true)) {
            $variant = 'light';
        }

        $mode = sanitize_key($settings['mode']);

        if (! in_array($mode, array('choice', 'informational'), true)) {
            $mode = 'choice';
        }

        return array(
            'enabled' => (bool) $settings['enabled'],
            'message' => $message !== '' ? $message : __('We use cookies to improve site functionality and measure audience usage.', 'am24h'),
            'accept_label' => $accept_label !== '' ? $accept_label : __('Accept', 'am24h'),
            'reject_label' => $reject_label !== '' ? $reject_label : ($mode === 'choice' ? __('Reject', 'am24h') : __('Close', 'am24h')),
            'policy_url' => $policy_url,
            'policy_label' => $policy_label !== '' ? $policy_label : __('Privacy Policy', 'am24h'),
            'position' => $position,
            'variant' => $variant,
            'mode' => $mode,
        );
    }
}
