<?php
get_header();



if (have_posts()) :
    while (have_posts()) : the_post(); ?>
       <main class="cc-single">
    <section class="cc-single__content">
        <div class="cc-container">
            <article id="post-<?php the_ID(); ?>" class="cc-single__post">
            <h1><?php the_title(); ?></h1>
            <div><?php the_content(); ?></div>
            </article>
        </div>
    </section>
    </main>
    <?php endwhile;
endif;

get_footer();
