<?php

class Am24h_HideTitleMetabox
{
    public function register_hooks(): void
    {
        add_action('add_meta_boxes', array($this, 'register_metabox'));
        add_action('save_post', array($this, 'save_metabox'));
        add_filter('post_class', array($this, 'add_post_class'), 10, 3);
    }

    public function register_metabox(): void
    {
        $post_types = get_post_types(array('public' => true), 'names');

        foreach ($post_types as $post_type) {
            add_meta_box(
                'cc_hide_title_box',
                __('Title Options', 'am24h'),
                array($this, 'render_metabox'),
                $post_type,
                'side',
                'high'
            );
        }
    }

    public function render_metabox(WP_Post $post): void
    {
        wp_nonce_field('cc_hide_title_nonce', 'cc_hide_title_nonce_field');
        $value = get_post_meta($post->ID, '_cc_hide_title', true);
        ?>
        <p>
            <label>
                <input type="checkbox" name="cc_hide_title" value="1" <?php checked($value, '1'); ?> />
                <?php esc_html_e('Hide title for this content', 'am24h'); ?>
            </label>
        </p>
        <?php
    }

    public function save_metabox(int $post_id): void
    {
        if (! isset($_POST['cc_hide_title_nonce_field'])) {
            return;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST['cc_hide_title_nonce_field']));

        if (! wp_verify_nonce($nonce, 'cc_hide_title_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $post_type = get_post_type($post_id);
        $capability = $post_type === 'page' ? 'edit_page' : 'edit_post';

        if (! current_user_can($capability, $post_id)) {
            return;
        }

        if (isset($_POST['cc_hide_title']) && wp_unslash($_POST['cc_hide_title']) === '1') {
            update_post_meta($post_id, '_cc_hide_title', '1');

            return;
        }

        delete_post_meta($post_id, '_cc_hide_title');
    }

    /**
     * @param string[] $classes
     * @param string[] $class
     *
     * @return string[]
     */
    public function add_post_class(array $classes, array $class, int $post_id): array
    {
        if (get_post_meta($post_id, '_cc_hide_title', true) === '1') {
            $classes[] = 'cc-hide-title';
        }

        return $classes;
    }
}
