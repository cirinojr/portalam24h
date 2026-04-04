<?php

$defaults = array(
    'mid_size'  => 2,
    'prev_text' => esc_html__('Previous', 'am24h'),
    'next_text' => esc_html__('Next', 'am24h'),
    'type'      => 'array',
    'current'   => max(1, get_query_var('paged')),
    'total'     => $GLOBALS['wp_query']->max_num_pages,
);

$args = wp_parse_args($args, $defaults);
$links = paginate_links($args);
if (!$links) return;
?>

<nav class="cc-pagination" aria-label="<?php esc_attr_e('Posts navigation', 'am24h'); ?>">
    <ul class="cc-pagination__list">
        <?php foreach ($links as $key => $link):
            $class = 'cc-pagination__item';
            if (strpos($link, 'current') !== false) {
                $class .= ' cc-pagination__item--active';
            } elseif (strpos($link, 'dots') !== false) {
                $class .= ' cc-pagination__item--dots';
            }
        ?>
            <li class="<?php echo esc_attr($class) ?>">
                <?php echo wp_kses_post($link); ?>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>