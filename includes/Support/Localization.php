<?php

class Am24h_Localization
{
    private Am24h_ThemeOptionsRepository $options;

    public function __construct(Am24h_ThemeOptionsRepository $options)
    {
        $this->options = $options;
    }

    public function register_hooks(): void
    {
        add_action('after_setup_theme', array($this, 'load_textdomain'));
        add_filter('locale', array($this, 'apply_custom_language'));
    }

    public function load_textdomain(): void
    {
        load_theme_textdomain('am24h', get_template_directory() . '/languages');
    }

    public function apply_custom_language(string $locale): string
    {
        if (is_admin()) {
            return $locale;
        }

        $custom_language = $this->options->get_site_language();

        if ($custom_language === '' || $custom_language === $locale) {
            return $locale;
        }

        if ($custom_language === 'en_US') {
            return $custom_language;
        }

        if (! $this->has_theme_mo_file($custom_language)) {
            return $locale;
        }

        return $custom_language;
    }

    private function has_theme_mo_file(string $locale): bool
    {
        $file = trailingslashit(get_template_directory()) . 'languages/' . sanitize_text_field($locale) . '.mo';

        return is_readable($file);
    }
}
