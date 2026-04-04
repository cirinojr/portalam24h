<?php

class Am24h_ExcerptManager
{
    /** @var array<int, bool> */
    private array $manual_excerpt_posts = array();

    /** @var array<int, string> */
    private array $first_paragraph_by_post = array();

    public function register_hooks(): void
    {
        add_filter('get_the_excerpt', array($this, 'force_first_paragraph_excerpt'));
        add_filter('the_content', array($this, 'remove_first_paragraph_from_content'));
    }

    public function force_first_paragraph_excerpt(string $text = ''): string
    {
        global $post;

        if (! $post instanceof WP_Post) {
            return $text;
        }

        $post_id = (int) $post->ID;

        if (! empty($post->post_excerpt)) {
            $this->manual_excerpt_posts[$post_id] = true;
            unset($this->first_paragraph_by_post[$post_id]);

            return $post->post_excerpt;
        }

        $content = (string) $post->post_content;

        if (preg_match('/<p>(.*?)<\/p>/is', $content, $matches)) {
            $this->first_paragraph_by_post[$post_id] = $matches[0];
            unset($this->manual_excerpt_posts[$post_id]);

            return wp_strip_all_tags($matches[0]);
        }

        unset($this->manual_excerpt_posts[$post_id], $this->first_paragraph_by_post[$post_id]);

        return '';
    }

    public function remove_first_paragraph_from_content(string $content): string
    {
        global $post;

        if (! $post instanceof WP_Post) {
            return $content;
        }

        if (! is_singular('post') || ! is_main_query() || ! in_the_loop()) {
            return $content;
        }

        $post_id = (int) $post->ID;

        if (! empty($this->manual_excerpt_posts[$post_id])) {
            return $content;
        }

        $first_paragraph = isset($this->first_paragraph_by_post[$post_id])
            ? $this->first_paragraph_by_post[$post_id]
            : '';

        if (is_string($first_paragraph) && $first_paragraph !== '') {
            $content = str_replace($first_paragraph, '', $content);
        }

        return $content;
    }
}
