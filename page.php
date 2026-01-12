<?php
get_header();



if (have_posts()) :
    while (have_posts()) : the_post(); ?>
       <main class="skl-single">
    <section class="skl-single__content">
        <div class="skl-container">
            <article id="post-<?php the_ID(); ?>" class="skl-single__post">
            <h1><?php the_title(); ?></h1>
            <div><?php the_content(); ?></div>
            </article>
        </div>
    </section>
    </main>
    <?php endwhile;
endif;

get_footer();
