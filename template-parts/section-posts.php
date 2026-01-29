<?php
$title = isset($args['title']) ? $args['title'] : '';
$posts = isset($args['posts']) ? $args['posts'] : null;
$see_more_url = isset($args['see_more_url']) ? $args['see_more_url'] : '';
?>

<section class="skl-section-posts">
    <div class="skl-container">
        <div class="skl-section-posts__container">
            <?php if ($title) : ?>
                <h2><?php echo esc_html($title); ?></h2>
            <?php endif; ?>

            <?php if ($posts instanceof WP_Query && $posts->have_posts()) : ?>
                <div class="skl-section-posts__posts">
                    <?php
                    while ($posts->have_posts()) {
                        $posts->the_post();
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
            <?php endif; ?>

            <?php if ($see_more_url) : ?>
                <a href="<?php echo esc_url($see_more_url); ?>" class="skl-btn skl-btn--text">
                    <?php echo esc_html__('Veja mais', 'skallar'); ?>
                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 16 16"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M6.46654 11.5333C6.34431 11.4111 6.2832 11.2555 6.2832 11.0667C6.2832 10.8778 6.34431 10.7222 6.46654 10.6L9.06654 7.99999L6.46654 5.39999C6.34431 5.27777 6.2832 5.12221 6.2832 4.93333C6.2832 4.74444 6.34431 4.58888 6.46654 4.46666C6.58876 4.34444 6.74431 4.28333 6.9332 4.28333C7.12209 4.28333 7.27765 4.34444 7.39987 4.46666L10.4665 7.53333C10.5332 7.59999 10.5805 7.67221 10.6085 7.74999C10.6361 7.82777 10.6499 7.9111 10.6499 7.99999C10.6499 8.08888 10.6361 8.17221 10.6085 8.24999C10.5805 8.32777 10.5332 8.39999 10.4665 8.46666L7.39987 11.5333C7.27765 11.6555 7.12209 11.7167 6.9332 11.7167C6.74431 11.7167 6.58876 11.6555 6.46654 11.5333V11.5333Z"
                            fill="currentColor" />
                    </svg>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>