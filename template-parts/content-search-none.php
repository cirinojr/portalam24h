<div class="search-page">
    <div class="cc-container">
        <div class="search-page__content">
            <h1 class="search-page__title">
                <?php
                printf(
                    esc_html__('Search results for: %s', 'am24h'),
                    '<span>' . esc_html(get_search_query()) . '</span>'
                );
                ?>
            </h1>
            <p class="search-page__description"><?php esc_html_e('Sorry, no results were found for your search terms. Please try different keywords.', 'am24h'); ?></p>
            <?php get_search_form(); ?>
        </div>
    </div>
</div>