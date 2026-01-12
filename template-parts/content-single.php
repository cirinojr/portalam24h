<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header>
        <h1><?php the_title(); ?></h1>
        <div class="meta"><?php the_time('d/m/Y'); ?> por <?php the_author(); ?></div>
    </header>

    <div class="entry-content">
        <?php the_content(); ?>
    </div>

    <footer class="entry-footer">
        <?php the_tags('<span class="tags">', ', ', '</span>'); ?>
    </footer>
</article>