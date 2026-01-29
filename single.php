<?php

/**
 * The template for displaying single posts
 *
 * @package YourTheme
 */

$current_post_id = get_the_ID();
$categories = get_the_category($current_post_id);
$primary_category = !empty($categories) && $categories[0] instanceof WP_Term ? $categories[0] : null;

// Get 8 latest posts from the same category, excluding the current post
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

<main class="skl-single">
    <section class="skl-single__content">
        <div class="skl-container">
            <article id="post-<?php the_ID(); ?>" class="skl-single__post">
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                        <div class="skl-single__post-header">
                            <h1 class="skl-single__post-title">
                                <?php the_title(); ?>
                            </h1>

                            <span class="skl-single__post-metadata">
                                <?php
                                $published_time = get_the_time('U');
                                $modified_time = get_the_modified_time('U');
                                $current_time = current_time('timestamp');

                                echo esc_html(get_the_date('d/m/Y H\hi'));
                                if ($published_time !== $modified_time) {
                                    printf(
                                        ' | %s %s',
                                        esc_html__('Updated', 'skallar'),
                                        esc_html(human_time_diff($modified_time, $current_time))
                                    );
                                    echo ' ' . esc_html__('ago', 'skallar');
                                }
                                ?>
                            </span>
                            <?php do_action('after_post_meta'); ?>

                            <?php if (has_post_thumbnail()) : ?>
                                <?php $image_id = get_post_thumbnail_id(); ?>
                                <figure class="skl-single__post-thumbnail">
                                    <?php echo wp_get_attachment_image($image_id, 'medium', false, array('loading' => 'lazy', 'decoding' => 'async')); ?>

                                    <?php if (get_the_post_thumbnail_caption()) : ?>
                                        <figcaption>
                                            <?php echo wp_kses_post(get_the_post_thumbnail_caption()); ?>
                                        </figcaption>
                                    <?php endif; ?>
                                </figure>
                            <?php endif; ?>
                        </div>

                        <div class="skl-single__post-content">
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
        if (!is_wp_error($category_link)) {
            $see_more_url = $category_link;
        }
    }

    get_template_part('template-parts/section', 'posts', array(
        'posts' => $latest_posts,
        'title' => esc_html__('Notícias relacionadas', 'skallar'),
        'see_more_url' => $see_more_url,
    ));
    ?>
</main>

<?php get_footer(); ?>