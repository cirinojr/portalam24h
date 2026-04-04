<?php
$category = isset($args['category'][0]) && $args['category'][0] instanceof WP_Term ? $args['category'][0] : null;
$category_link = '';
$thumbnail_html = '';
$thumbnail_link_label = '';

if ($category) {
    $category_link_candidate = get_category_link($category->term_id);
    $category_link = is_wp_error($category_link_candidate) ? '' : $category_link_candidate;
}
$post_link = isset($args['link']) ? $args['link'] : '';

$post_id = isset($args['post_id']) ? (int) $args['post_id'] : (int) get_the_ID();
$thumb_id = $post_id ? get_post_thumbnail_id($post_id) : 0;

$post_title_text = isset($args['title']) ? wp_strip_all_tags((string) $args['title']) : '';
if ($post_title_text === '' && $post_id) {
    $post_title_text = wp_strip_all_tags((string) get_the_title($post_id));
}

$thumbnail_link_label = $post_title_text !== ''
    ? sprintf(esc_html__('Read article: %s', 'am24h'), $post_title_text)
    : esc_html__('Read article', 'am24h');

if ($thumb_id) {
    $image_size = 'news-card-thumb';
    $metadata = wp_get_attachment_metadata($thumb_id);

    // Fallback for older images without the new generated crop.
    if (!is_array($metadata) || empty($metadata['sizes']['news-card-thumb'])) {
        $image_size = 'medium';
    }

    $thumbnail_html = wp_get_attachment_image(
        $thumb_id,
        $image_size,
        false,
        array(
            'class' => 'attachment-post-thumbnail size-post-thumbnail wp-post-image',
            'loading' => 'lazy',
            'decoding' => 'async',
            'sizes' => '(max-width: 767px) 92px, (max-width: 1023px) 172px, (max-width: 1439px) 286px, 320px',
        )
    );
}
?>

<div class="cc-news-card">
    <div class="cc-news-card__content">
        <div class="cc-news-card__category-date">
            <?php if ($category) : ?>
                <a href="<?php echo esc_url($category_link); ?>" class="cc-category">
                    <?php echo esc_html($category->name); ?>
                </a>
            <?php endif; ?>

            <?php if ($category && !empty($args['date'])) : ?>
                <span class="cc-news-card__separator"></span>
            <?php endif; ?>

            <?php if (!empty($args['date'])) : ?>
                <span class="cc-news-card__date"><?php echo esc_html($args['date']); ?></span>
            <?php endif; ?>
        </div>

        <h3 class="cc-news-card__title">
            <a href="<?php echo esc_url($post_link); ?>"><?php echo isset($args['title']) ? esc_html($args['title']) : ''; ?></a>
        </h3>

        <p class="cc-news-card__excerpt">
            <a href="<?php echo esc_url($post_link); ?>"><?php echo isset($args['excerpt']) ? esc_html($args['excerpt']) : ''; ?></a>
        </p>
    </div>

    <div class="cc-news-card__thumbnail">
        <a href="<?php echo esc_url($post_link); ?>" aria-label="<?php echo esc_attr($thumbnail_link_label); ?>"><?php echo $thumbnail_html; ?></a>
    </div>
</div>
