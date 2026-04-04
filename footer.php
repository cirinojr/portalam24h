<footer class="cc-footer">
    <div class="cc-container">
        <div class="cc-footer__container">
            <div class="cc-footer__content">
                <div class="cc-footer__logo">
                    <a href="<?php echo esc_url(home_url('/')) ?>">
                        <?php
                        if (has_custom_logo()) {
                            $custom_logo = get_custom_logo();
                            if (is_string($custom_logo) && $custom_logo !== '') {
                                echo wp_kses_post($custom_logo);
                            }
                        } else {
                            $logo = am24h_get_logo();
                            echo '<span class="cc-footer-logo-text">' . esc_html($logo['content']) . '</span>';
                        }
                        ?>
                    </a>
                </div>

                <nav class="cc-footer__nav">
                    <?php
                    if (has_nav_menu('bottom-menu')) {
                        wp_nav_menu(array(
                            'theme_location' => 'bottom-menu',
                            'menu_class' => 'cc-footer__menu',
                            'container' => false,
                            'fallback_cb' => false,
                            'walker' => new class extends Walker_Nav_Menu {
                                public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
                                    $output .= '<li class="cc-footer__menu-item">';
                                    $output .= '<a href="' . esc_url($item->url) . '" class="cc-footer__menu-link">';
                                    $output .= esc_html($item->title);
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

                            echo '<li class="cc-footer__menu-item"><a href="' . esc_url($page_url) . '" class="cc-footer__menu-link">' . esc_html($page->post_title) . '</a></li>';
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
