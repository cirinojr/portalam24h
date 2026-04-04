<footer class="cc-footer">
    <div class="cc-container">
        <div class="cc-footer__container">
            <div class="cc-footer__content">
                <div class="cc-footer__logo">
                    <?php
                    $home_label = sprintf(
                        /* translators: %s: Site name */
                        __('Go to %s homepage', 'am24h'),
                        get_bloginfo('name')
                    );

                    if (has_custom_logo()) {
                        $custom_logo = get_custom_logo();
                        if (is_string($custom_logo) && $custom_logo !== '') {
                            $custom_logo = str_replace('class="custom-logo-link"', 'class="custom-logo-link" aria-label="' . esc_attr($home_label) . '"', $custom_logo);
                            $custom_logo = preg_replace('/alt=""/', 'alt="' . esc_attr(get_bloginfo('name')) . '"', $custom_logo, 1);
                            echo wp_kses_post($custom_logo);
                        }
                    } else {
                        $logo = am24h_get_logo();
                        $logo_text = isset($logo['content']) && is_string($logo['content']) && trim($logo['content']) !== '' ? $logo['content'] : get_bloginfo('name');
                        echo '<a href="' . esc_url(home_url('/')) . '" aria-label="' . esc_attr($home_label) . '"><span class="cc-footer-logo-text">' . esc_html($logo_text) . '</span></a>';
                    }
                    ?>
                </div>

                <nav class="cc-footer__nav">
                    <?php
                    $resolve_footer_link_label = static function (string $raw_label, string $url): string {
                        $label = trim(wp_strip_all_tags($raw_label));

                        if ($label !== '') {
                            return $label;
                        }

                        $path = wp_parse_url($url, PHP_URL_PATH);

                        if (is_string($path) && $path !== '') {
                            $path = trim($path, '/');

                            if ($path !== '') {
                                return sprintf(
                                    /* translators: %s: URL path slug for fallback menu label. */
                                    __('Page %s', 'am24h'),
                                    str_replace('-', ' ', $path)
                                );
                            }
                        }

                        return __('Menu link', 'am24h');
                    };

                    if (has_nav_menu('bottom-menu')) {
                        wp_nav_menu(array(
                            'theme_location' => 'bottom-menu',
                            'menu_class' => 'cc-footer__menu',
                            'container' => false,
                            'fallback_cb' => false,
                            'walker' => new class($resolve_footer_link_label) extends Walker_Nav_Menu {
                                /** @var callable */
                                private $resolveLabel;

                                public function __construct(callable $resolve_label)
                                {
                                    $this->resolveLabel = $resolve_label;
                                }

                                public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
                                    $item_url = is_string($item->url) ? $item->url : '';
                                    $label_source = (string) $item->title;

                                    if (trim($label_source) === '') {
                                        $label_source = (string) $item->attr_title;
                                    }

                                    $label = call_user_func($this->resolveLabel, $label_source, $item_url);

                                    $output .= '<li class="cc-footer__menu-item">';
                                    $output .= '<a href="' . esc_url($item_url) . '" class="cc-footer__menu-link" aria-label="' . esc_attr($label) . '">';
                                    $output .= esc_html($label);
                                    $output .= '</a>';
                                    $output .= '</li>';
                                }
                            }
                        ));
                    } else {
                        $fallback_pages = get_pages(array(
                            'post_status' => 'publish',
                            'sort_column' => 'menu_order,post_title',
                            'parent' => 0,
                            'number' => 4,
                        ));
                        $privacy_url = get_privacy_policy_url();

                        echo '<ul class="cc-footer__menu">';
                        echo '<li class="cc-footer__menu-item"><a href="' . esc_url(home_url('/')) . '" class="cc-footer__menu-link">' . esc_html__('Home', 'am24h') . '</a></li>';

                        foreach ($fallback_pages as $page) {
                            if (! isset($page->ID, $page->post_title)) {
                                continue;
                            }

                            $page_url = get_permalink((int) $page->ID);

                            if (! is_string($page_url) || $page_url === '') {
                                continue;
                            }

                            $page_label = $resolve_footer_link_label((string) $page->post_title, $page_url);
                            echo '<li class="cc-footer__menu-item"><a href="' . esc_url($page_url) . '" class="cc-footer__menu-link" aria-label="' . esc_attr($page_label) . '">' . esc_html($page_label) . '</a></li>';
                        }

                        if (is_string($privacy_url) && $privacy_url !== '') {
                            echo '<li class="cc-footer__menu-item"><a href="' . esc_url($privacy_url) . '" class="cc-footer__menu-link">' . esc_html__('Privacy Policy', 'am24h') . '</a></li>';
                        }

                        echo '</ul>';
                    }
                    ?>
                </nav>

                <div class="cc-footer__social">
                </div>
            </div>

            <div class="cc-footer__bottom">
                <span class="cc-footer__copyright">
                    <?php
                    printf(
                        esc_html__('Copyright %1$s %2$s. All rights reserved.', 'am24h'),
                        esc_html(gmdate('Y')),
                        esc_html(get_bloginfo('name'))
                    );
                    ?>
                </span>
            </div>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>

</html>
