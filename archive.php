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
} else if (is_tag()) {
    $title = single_tag_title('', false);
    $latest_posts = new WP_Query(array_merge($default_args, array(
        'tag__in'        => get_queried_object_id(),
    )));
} else if (is_tax()) {
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
} else if (is_author()) {
    $title = get_the_author_meta('display_name', get_query_var('author'));
    $latest_posts = new WP_Query(array_merge($default_args, array(
        'author' => get_query_var('author'),
    )));
} else if (is_day()) {
    $title = the_date();
    $latest_posts = new WP_Query(array_merge($default_args, array(
        'date_query'     => array(
            array(
                'year'  => get_query_var('year'),
                'month' => get_query_var('monthnum'),
                'day'   => get_query_var('day'),
            ),
        ),
    )));
} else if (is_month()) {
    $title = the_date('F Y');
    $latest_posts = new WP_Query(array_merge($default_args, array(
        'date_query'     => array(
            array(
                'year'  => get_query_var('year'),
                'month' => get_query_var('monthnum'),
            ),
        ),
    )));
} else if (is_year()) {
    $title = the_date('Y');
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

<main class="skl-archive">
    <div class="skl-archive__content skl-container">
        <div class="skl-archive__title">
            <span>Você está em</span>
            <h1>Últimas notícias de: <span><?php echo esc_html($title); ?></span></h1>
        </div>

        <?php if ($latest_posts->have_posts()) : ?>
            <section class="skl-archive__posts-list">
                <?php while ($latest_posts->have_posts()) {
                    $latest_posts->the_post();
                    get_template_part('template-parts/news-card', null, array(
                        'category' => get_the_category(),
                        'date' => get_the_date('d/m/Y H\hi'),
                        'title' => get_the_title(),
                        'excerpt' => skallar_limit_excerpt(get_the_excerpt()),
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