<?php get_header(); ?>

<main class="skl-search">
    <div class="skl-container">
        <div class="skl-search__title">
            <h1>
                <?php
                $search_query = get_search_query();
                echo esc_html__('Resultados da pesquisa por:', 'skallar') . ' <span>' . esc_html($search_query) . '</span>';
                ?>
            </h1>

            <?php if (!have_posts()) : ?>
                <p class="skl-search__description">
                    <?php esc_html_e('Desculpe, mas nada foi encontrado com seus termos de busca. Por favor, tente novamente com termos diferentes.', 'skallar'); ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="skl-search__content">
            <?php if (have_posts()) : ?>
                <div class="result-list">
                    <?php while (have_posts()) : the_post();
                        get_template_part('template-parts/news-card', null, array(
                            'category' => get_the_category(),
                            'date' => get_the_date('d/m/Y H\hi'),
                            'title' => get_the_title(),
                            'excerpt' => skallar_limit_excerpt(get_the_excerpt()),
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