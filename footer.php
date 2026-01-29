<footer class="skl-footer">
    <div class="skl-container">
        <div class="skl-footer__container">
            <div class="skl-footer__content">
                <div class="skl-footer__logo">
                    <a href="<?php echo esc_url(home_url('/')) ?>">
                        <?php
                        $logo = ThemeOptions::get_logo();
                        if ($logo['type'] === 'text') {
                            echo '<span class="skl-footer-logo-text">' . esc_html($logo['content']) . '</span>';
                        } else {
                            echo '<img src="' . esc_url($logo['content']) . '" alt="' . esc_attr(get_bloginfo('name')) . '" class="skl-footer-logo-image" loading="lazy" decoding="async" />';
                        }
                        ?>
                    </a>
                </div>

                <nav class="skl-footer__nav">
                    <?php
                    // Debug: verificar se existe menu
                    if (has_nav_menu('bottom-menu')) {
                        // Menu existe e está atribuído
                        wp_nav_menu(array(
                            'theme_location' => 'bottom-menu',
                            'menu_class' => 'skl-footer__menu',
                            'container' => false,
                            'fallback_cb' => false,
                            'walker' => new class extends Walker_Nav_Menu {
                                public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
                                    $output .= '<li class="skl-footer__menu-item">';
                                    $output .= '<a href="' . esc_url($item->url) . '" class="skl-footer__menu-link">';
                                    $output .= esc_html($item->title);
                                    $output .= '</a>';
                                    $output .= '</li>';
                                }
                            }
                        ));
                    } else {
                        // Fallback: menu padrão
                        echo '<ul class="skl-footer__menu">';
                        echo '<li class="skl-footer__menu-item"><a href="' . esc_url(home_url('/')) . '" class="skl-footer__menu-link">' . __('Home', 'skallar') . '</a></li>';
                        echo '<li class="skl-footer__menu-item"><a href="' . esc_url(home_url('/about')) . '" class="skl-footer__menu-link">' . __('About', 'skallar') . '</a></li>';
                        echo '<li class="skl-footer__menu-item"><a href="' . esc_url(home_url('/contact')) . '" class="skl-footer__menu-link">' . __('Contact', 'skallar') . '</a></li>';
                        echo '<li class="skl-footer__menu-item"><a href="' . esc_url(home_url('/privacy-policy')) . '" class="skl-footer__menu-link">' . __('Privacy Policy', 'skallar') . '</a></li>';
                        echo '</ul>';
                    }
                    ?>
                </nav>

                <div class="skl-footer__social">
                    <!-- Aqui podem ser adicionados links de redes sociais no futuro -->
                </div>
            </div>

            <div class="skl-footer__bottom">
                <span class="skl-footer__copyright">® 2025 Company. Todos os direitos reservados</span>
            </div>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>

</html>