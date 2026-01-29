<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <header class="skl-header">
        <div class="skl-header__top ">
            <div class="skl-container">
                <div class="skl-header__top-container">
                    <div class="skl-header__left">
                        <button class="skl-btn skl-btn--secondary skl-btn--icon skl-mobile-menu-toggle">
                            <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.66667 12C2.47778 12 2.31956 11.936 2.192 11.808C2.064 11.6804 2 11.5222 2 11.3333C2 11.1444 2.064 10.9862 2.192 10.8587C2.31956 10.7307 2.47778 10.6667 2.66667 10.6667H13.3333C13.5222 10.6667 13.6804 10.7307 13.808 10.8587C13.936 10.9862 14 11.1444 14 11.3333C14 11.5222 13.936 11.6804 13.808 11.808C13.6804 11.936 13.5222 12 13.3333 12H2.66667ZM2.66667 8.66667C2.47778 8.66667 2.31956 8.60267 2.192 8.47467C2.064 8.34711 2 8.18889 2 8C2 7.81111 2.064 7.65267 2.192 7.52467C2.31956 7.39711 2.47778 7.33333 2.66667 7.33333H13.3333C13.5222 7.33333 13.6804 7.39711 13.808 7.52467C13.936 7.65267 14 7.81111 14 8C14 8.18889 13.936 8.34711 13.808 8.47467C13.6804 8.60267 13.5222 8.66667 13.3333 8.66667H2.66667ZM2.66667 5.33333C2.47778 5.33333 2.31956 5.26956 2.192 5.142C2.064 5.014 2 4.85556 2 4.66667C2 4.47778 2.064 4.31933 2.192 4.19133C2.31956 4.06378 2.47778 4 2.66667 4H13.3333C13.5222 4 13.6804 4.06378 13.808 4.19133C13.936 4.31933 14 4.47778 14 4.66667C14 4.85556 13.936 5.014 13.808 5.142C13.6804 5.26956 13.5222 5.33333 13.3333 5.33333H2.66667Z" fill="currentColor" />
                            </svg>
                        </button>

                        <div class="skl-header__logo">
                            <a href="<?php echo esc_url(home_url('/')) ?>">
                                <?php
                                $logo = ThemeOptions::get_logo();
                                if ($logo['type'] === 'text') {
                                    echo '<span class="skl-logo-text">' . esc_html($logo['content']) . '</span>';
                                } else {
                                    echo '<img src="' . esc_url($logo['content']) . '" alt="' . esc_attr(get_bloginfo('name')) . '" class="skl-logo-image" loading="lazy" decoding="async" />';
                                }
                                ?>
                            </a>
                        </div>
                    </div>

                    <?php get_search_form(); ?>
                </div>
            </div>
        </div>

        <div class="skl-header__bottom">
            <div class="skl-container">
                <div class="skl-header__bottom-container">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'header-menu',
                        'menu_class' => 'skl-header__bottom-menu',
                        'container' => false,
                        'fallback_cb' => false, // Não mostrar fallback se não houver menu
                        'walker' => new class extends Walker_Nav_Menu {
                            public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
                            {
                                $classes = empty($item->classes) ? array() : (array) $item->classes;
                                $classes[] = 'menu-item-' . $item->ID;
                                $classes = apply_filters('nav_menu_css_class', array_filter($classes), $item, $args, $depth);
                                $li_classes = implode(' ', array_map('sanitize_html_class', $classes));
                                $li_attributes = $li_classes ? ' class="' . esc_attr($li_classes) . '"' : '';

                                $atts = array();
                                $atts['title'] = !empty($item->attr_title) ? $item->attr_title : '';
                                $atts['target'] = !empty($item->target) ? $item->target : '';
                                $atts['rel'] = !empty($item->xfn) ? $item->xfn : '';
                                $atts['href'] = !empty($item->url) ? $item->url : '';
                                $atts['class'] = 'skl-btn skl-btn--small skl-btn--secondary';

                                $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args, $depth);

                                $attributes = '';
                                foreach ($atts as $attr => $value) {
                                    if (empty($value)) {
                                        continue;
                                    }

                                    $value = ('href' === $attr) ? esc_url($value) : esc_attr($value);
                                    $attributes .= ' ' . $attr . '="' . $value . '"';
                                }

                                $title = apply_filters('the_title', $item->title, $item->ID);
                                $title = esc_html($title);

                                $item_output  = isset($args->before) ? $args->before : '';
                                $item_output .= '<a' . $attributes . '>';
                                $item_output .= (isset($args->link_before) ? $args->link_before : '') . $title . (isset($args->link_after) ? $args->link_after : '');
                                $item_output .= '</a>';
                                $item_output .= isset($args->after) ? $args->after : '';

                                $indent = $depth ? str_repeat("\t", $depth) : '';
                                $output .= "\n{$indent}<li{$li_attributes}>{$item_output}";
                            }

                            public function end_el(&$output, $item, $depth = 0, $args = null)
                            {
                                $output .= "</li>\n";
                            }
                        }
                    ));
                    ?>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="skl-mobile-menu">
            <div class="skl-mobile-menu__overlay"></div>
            <div class="skl-mobile-menu__content">
                <div class="skl-mobile-menu__header">
                    <div class="skl-mobile-menu__search">
                        <?php get_search_form(); ?>
                    </div>

                    <button class="skl-btn skl-btn--secondary skl-btn--icon skl-mobile-menu-close">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>

                <nav class="skl-mobile-menu__nav">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'header-menu',
                        'menu_class' => 'skl-mobile-menu__list',
                        'container' => false,
                        'fallback_cb' => false,
                        'walker' => new class extends Walker_Nav_Menu {
                            public function start_lvl(&$output, $depth = 0, $args = null)
                            {
                                $output .= '<ul class="skl-mobile-menu__list">';
                            }

                            public function end_lvl(&$output, $depth = 0, $args = null)
                            {
                                $output .= '</ul>';
                            }

                            public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
                            {
                                $output .= '<li class="skl-mobile-menu__item">';
                                $output .= '<a href="' . esc_url($item->url) . '" class="skl-mobile-menu__link">';
                                $title = apply_filters('the_title', $item->title, $item->ID);
                                $output .= esc_html($title);
                                $output .= '</a>';
                            }

                            public function end_el(&$output, $item, $depth = 0, $args = null)
                            {
                                $output .= '</li>';
                            }
                        }
                    ));
                    ?>
                </nav>
            </div>
        </div>
    </header>

    <?php if (ThemeOptions::show_stock_quotes()) : ?>
        <div class="skl-stock-exchange-quotes">
            <ul>
                <li class="quotes">
                    <span class="quotes__ticket">PETR4 </span>
                    <span class="quotes__price">R$ 12,34</span>
                    <span
                        class="quotes__variation quotes__variation--positive">+0,12%</span>
                </li>

                <li class="quotes">
                    <span class="quotes__ticket">PETR4 </span>
                    <span class="quotes__price">R$ 12,34</span>
                    <span
                        class="quotes__variation quotes__variation--negative">-0,12%</span>
                </li>

                <li class="quotes">
                    <span class="quotes__ticket">PETR4 </span>
                    <span class="quotes__price">R$ 12,34</span>
                    <span
                        class="quotes__variation quotes__variation--positive">+0,12%</span>
                </li>

                <li class="quotes">
                    <span class="quotes__ticket">PETR4 </span>
                    <span class="quotes__price">R$ 12,34</span>
                    <span
                        class="quotes__variation quotes__variation--negative">-0,12%</span>
                </li>

                <li class="quotes">
                    <span class="quotes__ticket">PETR4 </span>
                    <span class="quotes__price">R$ 12,34</span>
                    <span
                        class="quotes__variation quotes__variation--positive">+0,12%</span>
                </li>

                <li class="quotes">
                    <span class="quotes__ticket">PETR4 </span>
                    <span class="quotes__price">R$ 12,34</span>
                    <span
                        class="quotes__variation quotes__variation--negative">-0,12%</span>
                </li>

                <li class="quotes">
                    <span class="quotes__ticket">PETR4 </span>
                    <span class="quotes__price">R$ 12,34</span>
                    <span
                        class="quotes__variation quotes__variation--positive">+0,12%</span>
                </li>

                <li class="quotes">
                    <span class="quotes__ticket">PETR4 </span>
                    <span class="quotes__price">R$ 12,34</span>
                    <span
                        class="quotes__variation quotes__variation--negative">-0,12%</span>
                </li>

                <li class="quotes">
                    <span class="quotes__ticket">PETR4 </span>
                    <span class="quotes__price">R$ 12,34</span>
                    <span
                        class="quotes__variation quotes__variation--positive">+0,12%</span>
                </li>

                <li class="quotes">
                    <span class="quotes__ticket">PETR4 </span>
                    <span class="quotes__price">R$ 12,34</span>
                    <span
                        class="quotes__variation quotes__variation--negative">-0,12%</span>
                </li>
            </ul>
        </div>
    <?php endif; ?>