<?php

$default_args = array(
    'post_type'      => 'post',
    'posts_per_page' => 9,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'         => max(1, get_query_var('paged')),
);

if (is_category()) {
    $title = single_cat_title('', false);
    $latest_posts = new WP_Query(array_merge($default_args, array(
        'category__in'   => get_queried_object_id(),
    )));
} elseif (is_tag()) {
    $title = single_tag_title('', false);
    $latest_posts = new WP_Query(array_merge($default_args, array(
        'tag__in'        => get_queried_object_id(),
    )));
} elseif (is_tax()) {
    $title = single_term_title('', false);
    $latest_posts = new WP_Query(array_merge($default_args, array(
        'tax_query' => array(
            array(
                'taxonomy' => get_queried_object()->taxonomy,
                'field'    => 'term_id',
                'terms'    => get_queried_object_id(),
            )
        )
    )));
} elseif (is_author()) {
    $title = get_the_author_meta('display_name', get_query_var('author'));
    $latest_posts = new WP_Query(array_merge($default_args, array(
        'author' => get_query_var('author'),
    )));
} elseif (is_day()) {
    $timestamp = mktime(0, 0, 0, (int) get_query_var('monthnum'), (int) get_query_var('day'), (int) get_query_var('year'));
    $title = wp_date(get_option('date_format'), $timestamp);
    $latest_posts = new WP_Query(array_merge($default_args, array(
        'date_query'     => array(
            array(
                'year'  => get_query_var('year'),
                'month' => get_query_var('monthnum'),
                'day'   => get_query_var('day'),
            ),
        ),
    )));
} elseif (is_month()) {
    $timestamp = mktime(0, 0, 0, (int) get_query_var('monthnum'), 1, (int) get_query_var('year'));
    $title = wp_date('F Y', $timestamp);
    $latest_posts = new WP_Query(array_merge($default_args, array(
        'date_query'     => array(
            array(
                'year'  => get_query_var('year'),
                'month' => get_query_var('monthnum'),
            ),
        ),
    )));
} elseif (is_year()) {
    $title = (string) get_query_var('year');
    $latest_posts = new WP_Query(array_merge($default_args, array(
        'date_query'     => array(
            array(
                'year' => get_query_var('year'),
            ),
        ),
    )));
} else {
    $title = get_the_title();
    $latest_posts = new WP_Query($default_args);
}

get_header(); ?>

<main class="cc-archive">
    <div class="cc-archive__content cc-container">
        <div class="cc-archive__title">
            <span><?php esc_html_e('You are in', 'am24h'); ?></span>
            <h1>
                <?php
                printf(
                    esc_html__('Latest news from: %s', 'am24h'),
                    '<span>' . esc_html($title) . '</span>'
                );
                ?>
            </h1>
        </div>

        <?php if ($latest_posts->have_posts()) : ?>
            <section class="cc-archive__posts-list">
                <?php while ($latest_posts->have_posts()) {
                    $latest_posts->the_post();
                    get_template_part('template-parts/news-card', null, array(
                        'post_id' => get_the_ID(),
                        'category' => get_the_category(),
                        'date' => get_the_date('d/m/Y H\hi'),
                        'title' => get_the_title(),
                        'excerpt' => am24h_limit_excerpt(get_the_excerpt()),
                        'thumbnail' => get_the_post_thumbnail(),
                        'link' => get_the_permalink(),
                    ));
                }
                wp_reset_postdata();
                ?>
            </section>

            <?php get_template_part('template-parts/pagination'); ?>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>