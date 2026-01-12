<?php

$defaults = array(
    'mid_size'  => 2,
    'prev_text' => __('Anterior', 'skallar'),
    'next_text' => __('Próximo', 'skallar'),
    'type'      => 'array',
    'current'   => max(1, get_query_var('paged')),
    'total'     => $GLOBALS['wp_query']->max_num_pages,
);

$args = wp_parse_args($args, $defaults);
$links = paginate_links($args);
if (!$links) return;
?>

<nav class="skl-pagination" aria-label="<?php esc_attr_e('Navegação de posts', 'skallar'); ?>">
    <ul class="skl-pagination__list">
        <?php foreach ($links as $key => $link):
            $class = 'skl-pagination__item';
            if (strpos($link, 'current') !== false) {
                $class .= ' skl-pagination__item--active';
            } elseif (strpos($link, 'dots') !== false) {
                $class .= ' skl-pagination__item--dots';
            }
        ?>
            <li class="<?php echo esc_attr($class) ?>">
                <?php echo $link ?>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>