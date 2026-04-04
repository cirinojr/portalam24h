<?php

class Am24h_FontRegistry
{
    private const OPTION_KEY = 'am24h_font_registry';

    private Am24h_TypographySettings $settings;
    private Am24h_FontStorageManager $storage;
    private Am24h_FontValidator $validator;

    public function __construct(
        Am24h_TypographySettings $settings,
        Am24h_FontStorageManager $storage,
        Am24h_FontValidator $validator
    ) {
        $this->settings = $settings;
        $this->storage = $storage;
        $this->validator = $validator;
    }

    /**
     * @return array<string, array{id: string, family: string, slug: string, variants: array<int, array{weight: int, style: string, format?: string, file: string, url: string}>, preload_file: string, created_at: int}>
     */
    public function all(): array
    {
        $registry = get_option(self::OPTION_KEY, array());

        if (! is_array($registry) || ! isset($registry['fonts']) || ! is_array($registry['fonts'])) {
            return array();
        }

        return $registry['fonts'];
    }

    public function upsert(array $font_data): void
    {
        $fonts = $this->all();
        $slug = $this->validator->family_slug((string) ($font_data['family'] ?? ''));

        if ($slug === '') {
            return;
        }

        $fonts[$slug] = array(
            'id'         => $slug,
            'family'     => $this->validator->sanitize_family_name((string) $font_data['family']),
            'slug'       => $slug,
            'variants'   => isset($font_data['variants']) && is_array($font_data['variants']) ? $font_data['variants'] : array(),
            'preload_file' => sanitize_file_name((string) ($font_data['preload_file'] ?? '')),
            'created_at' => isset($font_data['created_at']) ? (int) $font_data['created_at'] : time(),
        );

        update_option(self::OPTION_KEY, array('fonts' => $fonts));
    }

    public function activate(string $font_id): bool
    {
        $font_id = sanitize_key($font_id);
        $fonts = $this->all();

        if (! isset($fonts[$font_id])) {
            return false;
        }

        $this->settings->set_active_font_id($font_id);

        return true;
    }

    public function remove(string $font_id): bool
    {
        $font_id = sanitize_key($font_id);
        $fonts = $this->all();

        if (! isset($fonts[$font_id])) {
            return false;
        }

        $font = $fonts[$font_id];
        unset($fonts[$font_id]);
        update_option(self::OPTION_KEY, array('fonts' => $fonts));

        if ($this->settings->get_active_font_id() === $font_id) {
            $this->settings->clear_active_font_id();
        }

        if (isset($font['slug'])) {
            $this->storage->remove_family((string) $font['slug']);
        }

        return true;
    }

    /**
     * @return array{id: string, family: string, slug: string, variants: array<int, array{weight: int, style: string, format?: string, file: string, url: string}>, preload_file: string, created_at: int}|null
     */
    public function active(): ?array
    {
        $active_id = $this->settings->get_active_font_id();

        if ($active_id === '') {
            return null;
        }

        $fonts = $this->all();

        return isset($fonts[$active_id]) ? $fonts[$active_id] : null;
    }
}
