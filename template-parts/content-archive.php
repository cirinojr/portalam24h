<article id="post-<?php the_ID(); ?>">
    <a href="<?php the_permalink(); ?>">
        <?php if ( has_post_thumbnail() ): ?>
            <div class="thumb"><?php the_post_thumbnail('medium'); ?></div>
        <?php endif; ?>
        <h2><?php the_title(); ?></h2>
    </a>
    <div class="excerpt"><?php the_excerpt(); ?></div>
</article>
