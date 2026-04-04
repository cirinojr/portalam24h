<?php

class Am24h_FeaturedImagePriority
{
    public function register_hooks(): void
    {
        add_filter('wp_get_attachment_image_attributes', array($this, 'prioritize_single_featured_image'), 20, 3);
    }

    /**
     * Prioritize featured image loading on single posts to reduce LCP delays.
     *
     * @param array<string, string> $attributes
     * @param WP_Post               $attachment
     * @param mixed                 $size
     *
     * @return array<string, string>
     */
    public function prioritize_single_featured_image(array $attributes, WP_Post $attachment, $size): array
    {
        if (is_admin() || ! is_singular('post')) {
            return $attributes;
        }

        $post_id = get_queried_object_id();

        if (! $post_id) {
            return $attributes;
        }

        $thumbnail_id = get_post_thumbnail_id($post_id);

        if (! $thumbnail_id || (int) $attachment->ID !== (int) $thumbnail_id) {
            return $attributes;
        }

        $attributes['loading'] = 'eager';
        $attributes['fetchpriority'] = 'high';
        $attributes['decoding'] = 'async';

        return $attributes;
    }
}
