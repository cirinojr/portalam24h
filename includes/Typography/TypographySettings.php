<?php

class Am24h_TypographySettings
{
    private const OPTION_KEY = 'am24h_typography_settings';

    /**
     * @return array{active_font_id: string, fallback_stack: string, preload_enabled: int}
     */
    public function get_all(): array
    {
        $raw = get_option(self::OPTION_KEY, array());

        if (! is_array($raw)) {
            $raw = array();
        }

        $active_font_id = isset($raw['active_font_id']) ? sanitize_key((string) $raw['active_font_id']) : '';
        $fallback_stack = isset($raw['fallback_stack']) ? $this->sanitize_fallback_stack((string) $raw['fallback_stack']) : 'Arial, system-ui, sans-serif';
        $preload_enabled = isset($raw['preload_enabled']) ? (int) ($raw['preload_enabled'] ? 1 : 0) : 1;

        if ($fallback_stack === '') {
            $fallback_stack = 'Arial, system-ui, sans-serif';
        }

        return array(
            'active_font_id'  => $active_font_id,
            'fallback_stack'  => $fallback_stack,
            'preload_enabled' => $preload_enabled,
        );
    }

    public function get_active_font_id(): string
    {
        $settings = $this->get_all();

        return $settings['active_font_id'];
    }

    public function set_active_font_id(string $font_id): void
    {
        $settings = $this->get_all();
        $settings['active_font_id'] = sanitize_key($font_id);

        update_option(self::OPTION_KEY, $settings);
    }

    public function clear_active_font_id(): void
    {
        $settings = $this->get_all();
        $settings['active_font_id'] = '';

        update_option(self::OPTION_KEY, $settings);
    }

    public function get_fallback_stack(): string
    {
        $settings = $this->get_all();

        return $settings['fallback_stack'];
    }

    public function update_fallback_stack(string $fallback_stack): void
    {
        $settings = $this->get_all();
        $settings['fallback_stack'] = $this->sanitize_fallback_stack($fallback_stack);

        if ($settings['fallback_stack'] === '') {
            $settings['fallback_stack'] = 'Arial, system-ui, sans-serif';
        }

        update_option(self::OPTION_KEY, $settings);
    }

    public function is_preload_enabled(): bool
    {
        $settings = $this->get_all();

        return $settings['preload_enabled'] === 1;
    }

    public function set_preload_enabled(bool $enabled): void
    {
        $settings = $this->get_all();
        $settings['preload_enabled'] = $enabled ? 1 : 0;

        update_option(self::OPTION_KEY, $settings);
    }

    private function sanitize_fallback_stack(string $fallback_stack): string
    {
        $fallback_stack = sanitize_text_field($fallback_stack);
        $fallback_stack = preg_replace('/[^a-zA-Z0-9\s,\-"\']/', '', $fallback_stack);
        $fallback_stack = trim((string) $fallback_stack);

        if ($fallback_stack === '') {
            return 'Arial, system-ui, sans-serif';
        }

        return substr($fallback_stack, 0, 180);
    }
}
