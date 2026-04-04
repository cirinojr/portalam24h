<?php

class Am24h_ThirdPartyScripts
{
    private const PARTYTOWN_SCRIPT_RELATIVE = 'assets/vendor/partytown/partytown.js';

    private Am24h_ThemeOptionsRepository $options;
    private Am24h_AssetLocator $assets;

    /**
     * @var array<string, bool>|null
     */
    private ?array $main_thread_urls = null;

    public function __construct(Am24h_ThemeOptionsRepository $options, Am24h_AssetLocator $assets)
    {
        $this->options = $options;
        $this->assets = $assets;
    }

    public function register_hooks(): void
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_main_thread_scripts'), 30);
        add_action('wp_head', array($this, 'render_worker_scripts'), 30);

        if (is_admin()) {
            add_action('admin_notices', array($this, 'maybe_render_worker_assets_notice'));
        }
    }

    public function enqueue_main_thread_scripts(): void
    {
        $seen = array();

        foreach ($this->enabled_main_thread_scripts() as $script) {
            $url_key = $this->normalized_url_key($script['url']);

            if ($url_key === '' || isset($seen[$url_key])) {
                continue;
            }

            $seen[$url_key] = true;
            $this->enqueue_main_script($script['url'], $script['strategy'], $script['inline']);
        }

        if ($this->partytown_is_available()) {
            return;
        }

        foreach ($this->enabled_worker_scripts() as $script) {
            $url_key = $this->normalized_url_key($script['url']);

            if ($url_key === '' || isset($seen[$url_key])) {
                continue;
            }

            $seen[$url_key] = true;
            $this->enqueue_main_script($script['url'], 'async', $script['inline']);
        }
    }

    public function render_worker_scripts(): void
    {
        if (is_admin() || ! $this->partytown_is_available()) {
            return;
        }

        $worker_scripts = $this->enabled_worker_scripts();

        if ($worker_scripts === array()) {
            return;
        }

        $excluded = $this->enabled_main_thread_url_keys();
        $forward = array();
        $urls_seen = array();
        $filtered_scripts = array();

        foreach ($worker_scripts as $script) {
            $url_key = $this->normalized_url_key($script['url']);

            if ($url_key === '' || isset($excluded[$url_key]) || isset($urls_seen[$url_key])) {
                continue;
            }

            $urls_seen[$url_key] = true;
            $filtered_scripts[] = $script;

            if (! empty($script['forward']) && is_array($script['forward'])) {
                foreach ($script['forward'] as $key) {
                    if (! in_array($key, $forward, true)) {
                        $forward[] = $key;
                    }
                }
            }
        }

        if ($urls_seen === array()) {
            return;
        }

        $config = array(
            'lib' => trailingslashit($this->assets->url('assets/vendor/partytown')),
        );

        if ($forward !== array()) {
            $config['forward'] = $forward;
        }

        echo wp_get_inline_script_tag('partytown = ' . wp_json_encode($config, JSON_UNESCAPED_SLASHES) . ';');
        echo wp_get_script_tag(
            array(
                'src' => esc_url($this->assets->url(self::PARTYTOWN_SCRIPT_RELATIVE)),
                'defer' => true,
            )
        );

        foreach ($filtered_scripts as $script) {
            echo wp_get_script_tag(
                array(
                    'type' => 'text/partytown',
                    'src' => esc_url($script['url']),
                )
            );

            if ($script['inline'] !== '') {
                echo wp_get_inline_script_tag(
                    $script['inline'],
                    array(
                        'type' => 'text/partytown',
                    )
                );
            }
        }
    }

    public function maybe_render_worker_assets_notice(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $screen = get_current_screen();

        if (! $screen || strpos((string) $screen->id, 'am24h-third-party-scripts') === false) {
            return;
        }

        if ($this->partytown_is_available() || $this->enabled_worker_scripts() === array()) {
            return;
        }

        echo '<div class="notice notice-warning"><p>';
        echo esc_html__('Worker-friendly scripts are configured, but Partytown assets were not found at assets/vendor/partytown/. Those scripts are currently loaded on the main thread with async fallback.', 'am24h');
        echo '</p></div>';
    }

    /**
     * @return array<int, array{label: string, url: string, inline: string, strategy: string, enabled: bool}>
     */
    private function enabled_main_thread_scripts(): array
    {
        $enabled = array();

        foreach ($this->options->get_third_party_main_thread_scripts() as $script) {
            if (empty($script['enabled']) || empty($script['url'])) {
                continue;
            }

            $enabled[] = $script;
        }

        return $enabled;
    }

    /**
     * @return array<int, array{label: string, url: string, inline: string, forward: array<int, string>, enabled: bool}>
     */
    private function enabled_worker_scripts(): array
    {
        $enabled = array();

        foreach ($this->options->get_third_party_worker_scripts() as $script) {
            if (empty($script['enabled']) || empty($script['url'])) {
                continue;
            }

            $enabled[] = $script;
        }

        return $enabled;
    }

    /**
     * @return array<string, bool>
     */
    private function enabled_main_thread_url_keys(): array
    {
        if ($this->main_thread_urls !== null) {
            return $this->main_thread_urls;
        }

        $this->main_thread_urls = array();

        foreach ($this->enabled_main_thread_scripts() as $script) {
            $url_key = $this->normalized_url_key($script['url']);

            if ($url_key !== '') {
                $this->main_thread_urls[$url_key] = true;
            }
        }

        return $this->main_thread_urls;
    }

    private function enqueue_main_script(string $url, string $strategy, string $inline): void
    {
        $handle = 'am24h-third-party-' . substr(md5($url), 0, 12);

        if (wp_script_is($handle, 'enqueued')) {
            return;
        }

        wp_enqueue_script($handle, esc_url_raw($url), array(), null, true);

        // Inline initializers often depend on globals from the external script.
        // Skip async/defer in this case to preserve deterministic execution order.
        if ($inline === '' && in_array($strategy, array('async', 'defer'), true)) {
            wp_script_add_data($handle, 'strategy', $strategy);
        }

        if ($inline !== '') {
            wp_add_inline_script($handle, $inline, 'after');
        }
    }

    private function partytown_is_available(): bool
    {
        return $this->assets->is_readable(self::PARTYTOWN_SCRIPT_RELATIVE);
    }

    private function normalized_url_key(string $url): string
    {
        return strtolower(trim($url));
    }
}
