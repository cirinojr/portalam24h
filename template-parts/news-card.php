<?php
$category = isset($args['category'][0]) && $args['category'][0] instanceof WP_Term ? $args['category'][0] : null;
$category_link = '';

if ($category) {
    $category_link_candidate = get_category_link($category->term_id);
    $category_link = is_wp_error($category_link_candidate) ? '' : $category_link_candidate;
}
$post_link = isset($args['link']) ? $args['link'] : '';
?>

<div class="skl-news-card">
    <div class="skl-news-card__content">
        <div class="skl-news-card__category-date">
            <?php if ($category) : ?>
                <a href="<?php echo esc_url($category_link); ?>" class="skl-category">
                    <?php echo esc_html($category->name); ?>
                </a>
            <?php endif; ?>

            <?php if ($category && !empty($args['date'])) : ?>
                <span class="skl-news-card__separator"></span>
            <?php endif; ?>

            <?php if (!empty($args['date'])) : ?>
                <span class="skl-news-card__date"><?php echo esc_html($args['date']); ?></span>
            <?php endif; ?>
        </div>

        <h3 class="skl-news-card__title">
            <a href="<?php echo esc_url($post_link); ?>"><?php echo isset($args['title']) ? esc_html($args['title']) : ''; ?></a>
        </h3>

        <p class="skl-news-card__excerpt">
            <a href="<?php echo esc_url($post_link); ?>"><?php echo isset($args['excerpt']) ? esc_html($args['excerpt']) : ''; ?></a>
        </p>
    </div>

    <div class="skl-news-card__thumbnail">
        <a href="<?php echo esc_url($post_link); ?>"><?php echo isset($args['thumbnail']) ? wp_kses_post($args['thumbnail']) : ''; ?></a>
    </div>
</div>