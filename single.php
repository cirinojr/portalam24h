<?php

/**
 * The template for displaying single posts
 *
 * @package PortalAM24h
 */

$current_post_id = get_the_ID();
$categories = get_the_category($current_post_id);
$primary_category = !empty($categories) && $categories[0] instanceof WP_Term ? $categories[0] : null;

// Pull related posts from the same primary category when available.
$related_args = array(
    'post_type'      => 'post',
    'posts_per_page' => 8,
    'post_status'    => 'publish',
    'post__not_in'   => array($current_post_id),
);

if ($primary_category) {
    $related_args['category__in'] = array($primary_category->term_id);
}

$latest_posts = new WP_Query($related_args);

get_header();
?>

<main class="cc-single">
    <section class="cc-single__content">
        <div class="cc-container">
            <article id="post-<?php the_ID(); ?>" class="cc-single__post">
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                        <div class="cc-single__post-header">
                            <h1 class="cc-single__post-title">
                                <?php echo esc_html(get_the_title()); ?>
                            </h1>

                            <span class="cc-single__post-metadata">
                                <?php
                                $published_time = get_the_time('U');
                                $modified_time = get_the_modified_time('U');
                                $current_time = current_time('timestamp');

                                echo esc_html(get_the_date('d/m/Y H\hi'));
                                if ($published_time !== $modified_time) {
                                    printf(
                                        ' | %s %s',
                                        esc_html__('Updated', 'am24h'),
                                        esc_html(human_time_diff($modified_time, $current_time))
                                    );
                                    echo ' ' . esc_html__('ago', 'am24h');
                                }
                                ?>
                            </span>
                            <?php do_action('after_post_meta'); ?>

                            <?php
                            $share_settings = am24h_get_share_bar_settings();
                            $share_items = am24h_should_render_share_bar(get_the_ID()) ? am24h_get_share_bar_items(get_the_ID()) : array();
                            $share_title = wp_strip_all_tags((string) get_the_title());
                            ?>
                            <?php if (! empty($share_items)) : ?>
                                <nav
                                    class="cc-share-bar cc-share-bar--<?php echo esc_attr($share_settings['alignment']); ?> cc-share-bar--<?php echo esc_attr($share_settings['size']); ?>"
                                    aria-label="<?php echo esc_attr__('Share this article', 'am24h'); ?>"
                                    data-copy-success="<?php echo esc_attr__('Link copied.', 'am24h'); ?>"
                                    data-copy-fallback="<?php echo esc_attr__('Unable to copy automatically. Press Ctrl+C to copy the link.', 'am24h'); ?>"
                                >
                                    <ul class="cc-share-bar__list">
                                        <?php foreach ($share_items as $item) : ?>
                                            <li>
                                                <?php if ($item['network'] === 'copy') : ?>
                                                    <button
                                                        type="button"
                                                        class="cc-share-bar__action"
                                                        data-share-copy
                                                        data-share-url="<?php echo esc_url($item['url']); ?>"
                                                        aria-label="<?php echo esc_attr(sprintf(__('Copy link for %s', 'am24h'), $share_title)); ?>"
                                                    >
                                                        <span class="cc-share-bar__icon"><?php echo am24h_get_share_icon_markup($item['network'], $share_settings['icon_source']); ?></span>
                                                    </button>
                                                <?php else : ?>
                                                    <a
                                                        class="cc-share-bar__action"
                                                        href="<?php echo esc_url($item['url']); ?>"
                                                        <?php if ($item['is_external']) : ?>target="_blank" rel="noopener noreferrer nofollow"<?php endif; ?>
                                                        aria-label="<?php echo esc_attr(sprintf(__('Share on %s', 'am24h'), $item['label'])); ?>"
                                                    >
                                                        <span class="cc-share-bar__icon"><?php echo am24h_get_share_icon_markup($item['network'], $share_settings['icon_source']); ?></span>
                                                    </a>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <span class="cc-share-bar__status" aria-live="polite" data-share-copy-status></span>
                                </nav>
                            <?php endif; ?>

                            <?php if (has_post_thumbnail()) : ?>
                                <?php
                                $image_id = get_post_thumbnail_id();
                                $single_image_size = 'single-featured';
                                $thumbnail_metadata = wp_get_attachment_metadata($image_id);

                                if (! is_array($thumbnail_metadata) || empty($thumbnail_metadata['sizes']['single-featured'])) {
                                    $single_image_size = 'large';
                                }
                                ?>
                                <figure class="cc-single__post-thumbnail">
                                    <?php echo wp_kses_post(wp_get_attachment_image($image_id, $single_image_size, false, array('loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async'))); ?>

                                    <?php if (get_the_post_thumbnail_caption()) : ?>
                                        <figcaption>
                                            <?php echo wp_kses_post(get_the_post_thumbnail_caption()); ?>
                                        </figcaption>
                                    <?php endif; ?>
                                </figure>
                            <?php endif; ?>
                        </div>

                        <div class="cc-single__post-content">
                            <?php the_content(); ?>
                        </div>
                <?php endwhile;
                endif; ?>
            </article>

            <aside></aside>
        </div>
    </section>

    <?php
    $see_more_url = '';
    if ($primary_category) {
        $category_link = get_category_link($primary_category->term_id);
        if (! is_wp_error($category_link)) {
            $see_more_url = $category_link;
        }
    }

    get_template_part('template-parts/section', 'posts', array(
        'posts' => $latest_posts,
        'title' => esc_html__('Notícias relacionadas', 'am24h'),
        'see_more_url' => $see_more_url,
    ));
    ?>
</main>

<?php get_footer(); ?>
