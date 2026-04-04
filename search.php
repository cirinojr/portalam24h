<?php get_header(); ?>

<main class="cc-search">
    <div class="cc-container">
        <div class="cc-search__title">
            <h1>
                <?php
                $search_query = get_search_query();
                printf(
                    esc_html__('Search results for: %s', 'am24h'),
                    '<span>' . esc_html($search_query) . '</span>'
                );
                ?>
            </h1>

            <?php if (!have_posts()) : ?>
                <p class="cc-search__description">
                    <?php esc_html_e('Sorry, no results were found for your search terms. Please try different keywords.', 'am24h'); ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="cc-search__content">
            <?php if (have_posts()) : ?>
                <div class="result-list">
                    <?php while (have_posts()) : the_post();
                        get_template_part('template-parts/news-card', null, array(
                            'post_id' => get_the_ID(),
                            'category' => get_the_category(),
                            'date' => get_the_date('d/m/Y H\hi'),
                            'title' => get_the_title(),
                            'excerpt' => am24h_limit_excerpt(get_the_excerpt()),
                            'thumbnail' => get_the_post_thumbnail(),
                            'link' => get_the_permalink(),
                        ));
                    endwhile; ?>
                </div>

                <?php get_template_part('template-parts/pagination'); ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>