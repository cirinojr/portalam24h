<?php

class Am24h_Cleanup
{
    private Am24h_ThemeOptionsRepository $options;

    public function __construct(Am24h_ThemeOptionsRepository $options)
    {
        $this->options = $options;
    }

    public function register_hooks(): void
    {
        add_action('init', array($this, 'remove_unnecessary_head_output'));
        add_action('wp_enqueue_scripts', array($this, 'maybe_remove_block_styles'));
        add_filter('should_load_separate_core_block_assets', array($this, 'maybe_load_core_block_styles_on_demand'));
        add_filter('should_load_block_assets_on_demand', array($this, 'maybe_load_core_block_styles_on_demand'));
        add_filter('show_admin_bar', array($this, 'maybe_hide_admin_bar'));
    }

    public function maybe_load_core_block_styles_on_demand(bool $enabled): bool
    {
        if (is_admin()) {
            return $enabled;
        }

        $load_on_demand = $this->options->get_bool('am24h_cleanup_block_styles_on_demand');
        if (defined('AM24H_LOAD_CORE_BLOCK_STYLES_ON_DEMAND')) {
            $load_on_demand = (bool) AM24H_LOAD_CORE_BLOCK_STYLES_ON_DEMAND;
        }

        $load_on_demand = apply_filters('am24h_load_core_block_styles_on_demand', $load_on_demand);

        return $load_on_demand ? true : $enabled;
    }

    public function remove_unnecessary_head_output(): void
    {
        if ($this->options->get_bool('am24h_cleanup_emojis')) {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('admin_print_styles', 'print_emoji_styles');
        }

        if ($this->options->get_bool('am24h_cleanup_rsd')) {
            remove_action('wp_head', 'rsd_link');
        }

        if ($this->options->get_bool('am24h_cleanup_generator')) {
            remove_action('wp_head', 'wp_generator');
        }

        if ($this->options->get_bool('am24h_cleanup_feed_links')) {
            remove_action('wp_head', 'feed_links', 2);
            remove_action('wp_head', 'feed_links_extra', 3);
        }

        if ($this->options->get_bool('am24h_cleanup_wlwmanifest')) {
            remove_action('wp_head', 'wlwmanifest_link');
        }

        if ($this->options->get_bool('am24h_cleanup_prev_next_links')) {
            remove_action('wp_head', 'index_rel_link');
            remove_action('wp_head', 'start_post_rel_link', 10, 0);
            remove_action('wp_head', 'parent_post_rel_link', 10, 0);
            remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
            remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
        }

        if ($this->options->get_bool('am24h_cleanup_shortlink')) {
            remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
        }

        if ($this->options->get_bool('am24h_cleanup_rest_links')) {
            remove_action('wp_head', 'rest_output_link_wp_head');
            remove_action('template_redirect', 'rest_output_link_header', 11);
        }

        if ($this->options->get_bool('am24h_cleanup_oembed_links')) {
            remove_action('wp_head', 'wp_oembed_add_discovery_links');
        }

        if ($this->options->get_bool('am24h_cleanup_multilingualpress_hreflang')) {
            add_filter('multilingualpress.hreflang_type', '__return_false');
        }
    }

    public function maybe_hide_admin_bar(bool $show): bool
    {
        if ($this->options->get_bool('am24h_cleanup_admin_bar')) {
            return false;
        }

        return $show;
    }

    public function maybe_remove_block_styles(): void
    {
        if (is_admin()) {
            return;
        }

        $disable_block_styles = $this->options->get_bool('am24h_cleanup_block_styles');
        if (defined('AM24H_DISABLE_BLOCK_STYLES')) {
            $disable_block_styles = (bool) AM24H_DISABLE_BLOCK_STYLES;
        }

        $disable_block_styles = apply_filters('am24h_disable_block_styles', $disable_block_styles);

        if (! $disable_block_styles) {
            return;
        }

        wp_dequeue_style('global-styles');
        wp_dequeue_style('classic-theme-styles');
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('wc-blocks-style');
    }
}
