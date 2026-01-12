<?php

// Busca as 5 últimas notícias
$latest_posts = new WP_Query(array(
    'post_type'      => 'post',
    'posts_per_page' => 5,
    'post_status'    => 'publish',
));

// Get all categories
$categories = get_categories(array(
    'orderby' => 'id',
    'order'   => 'DESC',
    'hide_empty' => true, // Only show categories with posts
));

// Get selected categories
$selected_categories = get_option('skallar_home_categories', array());

$categories_to_show;
if (empty($selected_categories)) {
    $categories_to_show = $categories;
} else {
    $categories_to_show = array_filter($categories, function ($category) use ($selected_categories) {
        return in_array($category->term_id, $selected_categories);
    });
}

get_header(); ?>

<main class="skl-home">
    <?php if ($latest_posts->have_posts()) : ?>
        <section class="recent-news-section">
            <div class="skl-container">
                <div class="recent-news-section__container">
                    <div class="recent-news-section__last-news">
                        <?php
                        if ($latest_posts->have_posts()) {
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
                        ?>
                    </div>

                    <div class="recent-news-section__news">
                        <?php
                        while ($latest_posts->have_posts()) {
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
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php foreach ($categories_to_show as $category) :
        $category_posts = new WP_Query(array(
            'post_type'      => 'post',
            'posts_per_page' => 8,
            'post_status'    => 'publish',
            'cat'            => $category->term_id,
        ));

        if ($category_posts->have_posts()) :
    ?>
            <hr />
            <?php get_template_part('template-parts/section', 'posts', array(
                'posts' => $category_posts,
                'title' => $category->name,
                'see_more_url' => get_category_link($category->term_id),
            )); ?>
        <?php endif; ?>
    <?php endforeach; ?>
</main>

<?php get_footer(); ?>