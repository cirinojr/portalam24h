<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header>
        <h1><?php echo esc_html(get_the_title()); ?></h1>
        <div class="meta">
            <?php
            printf(
                /* translators: 1: publish date, 2: author display name. */
                esc_html__('%1$s by %2$s', 'am24h'),
                esc_html(get_the_date('d/m/Y')),
                esc_html(get_the_author())
            );
            ?>
        </div>
    </header>

    <div class="entry-content">
        <?php the_content(); ?>
    </div>

    <footer class="entry-footer">
        <?php the_tags('<span class="tags">', esc_html__(', ', 'am24h'), '</span>'); ?>
    </footer>
</article>