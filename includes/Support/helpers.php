<?php

function am24h_limit_excerpt(string $excerpt, int $limit = 150): string
{
    $excerpt = wp_strip_all_tags($excerpt);

    if (mb_strlen($excerpt) <= $limit) {
        return $excerpt;
    }

    $limited = mb_substr($excerpt, 0, $limit);
    $last_space = mb_strrpos($limited, ' ');

    if ($last_space !== false) {
        $limited = mb_substr($limited, 0, $last_space);
    }

    return $limited . '...';
}

/**
 * @return array{type: string, content: string}
 */
function am24h_get_logo(): array
{
    $logo_id = (int) get_theme_mod('custom_logo');

    if ($logo_id > 0) {
        $logo_url = wp_get_attachment_image_url($logo_id, 'full');

        if (is_string($logo_url) && $logo_url !== '') {
            return array(
                'type'    => 'image',
                'content' => $logo_url,
            );
        }
    }

    return array(
        'type'    => 'text',
        'content' => get_bloginfo('name'),
    );
}

function am24h_get_accessible_custom_logo_markup(string $home_label): string
{
    $custom_logo = get_custom_logo();

    if (! is_string($custom_logo) || $custom_logo === '') {
        return '';
    }

    if (strpos($custom_logo, 'class="custom-logo-link"') !== false && strpos($custom_logo, 'aria-label=') === false) {
        $custom_logo = str_replace(
            'class="custom-logo-link"',
            'class="custom-logo-link" aria-label="' . esc_attr($home_label) . '"',
            $custom_logo
        );
    }

    if (strpos($custom_logo, 'alt=""') !== false) {
        $custom_logo = preg_replace('/alt=""/', 'alt="' . esc_attr(get_bloginfo('name')) . '"', $custom_logo, 1);

        if (! is_string($custom_logo)) {
            return '';
        }
    }

    return $custom_logo;
}

function am24h_theme(): Am24h_Bootstrap
{
    return Am24h_Bootstrap::instance();
}

function am24h_sanitize_inline_css(string $css): string
{
    $css = str_replace("\0", '', $css);

    return str_ireplace('</style', '<\\/style', $css);
}

/**
 * @return array{enabled: bool, alignment: string, icon_source: string, size: string, order: string[], networks: array<string, bool>}
 */
function am24h_get_share_bar_settings(): array
{
    return am24h_theme()->options()->get_share_bar_settings();
}

/**
 * @return array<string, string>
 */
function am24h_get_share_network_labels(): array
{
    return Am24h_ThemeOptionsRepository::share_network_labels();
}

function am24h_should_render_share_bar(int $post_id): bool
{
    if (! is_singular('post') || $post_id <= 0) {
        return false;
    }

    $settings = am24h_get_share_bar_settings();

    if (! $settings['enabled']) {
        return false;
    }

    foreach ($settings['order'] as $network) {
        if (! empty($settings['networks'][$network])) {
            return true;
        }
    }

    return false;
}

/**
 * @return array<int, array{network: string, label: string, url: string, is_external: bool}>
 */
function am24h_get_share_bar_items(int $post_id): array
{
    $settings = am24h_get_share_bar_settings();

    if (! $settings['enabled']) {
        return array();
    }

    $permalink = get_permalink($post_id);

    if (! is_string($permalink) || $permalink === '') {
        return array();
    }

    $title = wp_strip_all_tags((string) get_the_title($post_id));
    $labels = am24h_get_share_network_labels();
    $encoded_permalink = rawurlencode($permalink);
    $encoded_title = rawurlencode($title);

    $share_urls = array(
        'whatsapp' => 'https://api.whatsapp.com/send?text=' . $encoded_title . '%20' . $encoded_permalink,
        'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $encoded_permalink,
        'x' => 'https://twitter.com/intent/tweet?url=' . $encoded_permalink . '&text=' . $encoded_title,
        'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $encoded_permalink,
        'telegram' => 'https://t.me/share/url?url=' . $encoded_permalink . '&text=' . $encoded_title,
        'copy' => $permalink,
        'reddit' => 'https://www.reddit.com/submit?url=' . $encoded_permalink . '&title=' . $encoded_title,
        'pinterest' => 'https://pinterest.com/pin/create/button/?url=' . $encoded_permalink . '&description=' . $encoded_title,
        'mastodon' => 'https://mastodon.social/share?text=' . $encoded_title . '%20' . $encoded_permalink,
        'threads' => 'https://www.threads.net/intent/post?text=' . $encoded_title . '%20' . $encoded_permalink,
        'email' => 'mailto:?subject=' . $encoded_title . '&body=' . $encoded_permalink,
        'instagram' => $permalink,
        'youtube' => $permalink,
        'tiktok' => $permalink,
    );

    if (! empty($settings['custom_url_template'])) {
        $custom_url = str_replace(
            array('{url}', '{title}'),
            array(rawurlencode($permalink), rawurlencode($title)),
            (string) $settings['custom_url_template']
        );
        $custom_url = esc_url_raw($custom_url);

        if ($custom_url !== '') {
            $share_urls['custom'] = $custom_url;
        }
    }

    $items = array();

    foreach ($settings['order'] as $network) {
        if (empty($settings['networks'][$network]) || ! isset($share_urls[$network], $labels[$network])) {
            continue;
        }

        $items[] = array(
            'network' => $network,
            'label' => $network === 'custom' ? (string) $settings['custom_label'] : (string) $labels[$network],
            'url' => (string) $share_urls[$network],
            'is_external' => ! in_array($network, array('copy', 'email'), true),
        );
    }

    return $items;
}

function am24h_get_share_icon_markup(string $network, string $source = 'inline'): string
{
    $network = sanitize_key($network);
    $source = sanitize_key($source);
    $allowed_networks = array('whatsapp', 'facebook', 'x', 'linkedin', 'telegram', 'copy', 'reddit', 'pinterest', 'mastodon', 'threads', 'email', 'instagram', 'youtube', 'tiktok', 'custom');

    if (! in_array($network, $allowed_networks, true)) {
        return '';
    }

    if ($source === 'local') {
        $uploaded_url = am24h_get_uploaded_share_icon_url($network);

        if ($uploaded_url !== '') {
            return sprintf(
                '<img src="%1$s" width="20" height="20" alt="" aria-hidden="true" />',
                esc_url($uploaded_url)
            );
        }

        $relative = 'assets/images/icons/share/' . $network . '.svg';
        $path = trailingslashit(get_template_directory()) . $relative;

        if (is_readable($path)) {
            return sprintf(
                '<img src="%1$s" width="20" height="20" alt="" aria-hidden="true" />',
                esc_url(trailingslashit(get_template_directory_uri()) . $relative)
            );
        }
    }

    $svg_map = array(
        'whatsapp' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true"><path d="M12 3a9 9 0 0 0-7.8 13.5L3 21l4.7-1.2A9 9 0 1 0 12 3Zm0 16.3a7.3 7.3 0 0 1-3.7-1l-.3-.2-2.8.7.7-2.7-.2-.3A7.3 7.3 0 1 1 12 19.3Zm4.1-5.6c-.2-.1-1.3-.7-1.5-.7-.2-.1-.3-.1-.5.1l-.4.5c-.1.2-.3.2-.5.1a6 6 0 0 1-2.9-2.5c-.1-.2 0-.3.1-.4l.3-.4.2-.3a.5.5 0 0 0 0-.4l-.7-1.6c-.1-.2-.3-.2-.4-.2h-.4a.8.8 0 0 0-.6.3c-.2.2-.8.7-.8 1.8s.9 2 1 2.2a8.4 8.4 0 0 0 3.3 2.9c2 .8 2 .5 2.4.5.4 0 1.3-.5 1.4-1.1.2-.5.2-1 .1-1.1-.1 0-.3-.1-.5-.2Z" fill="currentColor"/></svg>',
        'facebook' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true"><path d="M13.7 21v-8h2.6l.4-3h-3V8.1c0-.9.3-1.5 1.6-1.5H17V4a18 18 0 0 0-2.5-.1c-2.5 0-4.1 1.5-4.1 4.3V10H8v3h2.4v8h3.3Z" fill="currentColor"/></svg>',
        'x' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true"><path d="M18.9 4H21l-4.6 5.2L22 20h-4.4l-3.4-4.8L10 20H7.9l4.9-5.7L2 4h4.5L9.6 8.5 13.5 4h2.1Zm-1.5 14.3h1.2L5.8 5.6H4.5l12.9 12.7Z" fill="currentColor"/></svg>',
        'linkedin' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true"><path d="M6.2 8.5a1.9 1.9 0 1 1 0-3.8 1.9 1.9 0 0 1 0 3.8ZM4.6 20V10h3.2v10H4.6Zm5.3 0V10H13v1.4h.1c.4-.8 1.5-1.7 3.1-1.7 3.3 0 3.9 2.2 3.9 5V20H17v-4.6c0-1.1 0-2.5-1.5-2.5s-1.8 1.2-1.8 2.4V20H9.9Z" fill="currentColor"/></svg>',
        'telegram' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true"><path d="M20.6 4.5 3.7 11c-1.2.5-1.2 1.2-.2 1.5l4.4 1.4 1.7 5.2c.2.6.1.8.8.8.5 0 .8-.2 1.1-.5l2.1-2 4.4 3.2c.8.4 1.4.2 1.6-.8L22 6c.3-1.2-.4-1.8-1.4-1.5Zm-3.1 3.1-6.4 5.8-.2 2.1-.9-2.8 8.4-5.1c.4-.3.8.2.4.4Z" fill="currentColor"/></svg>',
        'copy' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true"><path d="M8 7a3 3 0 0 1 3-3h6a3 3 0 0 1 0 6h-2V8h2a1 1 0 1 0 0-2h-6a1 1 0 0 0-1 1v2H8V7Zm-1 4h2v2H7a1 1 0 1 0 0 2h6a1 1 0 0 0 1-1v-2h2v2a3 3 0 0 1-3 3H7a3 3 0 0 1 0-6Zm2 1h6v2H9v-2Z" fill="currentColor"/></svg>',
        'reddit' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true"><path d="M12 7.8c2.8 0 5 1.5 5 3.3 0 1.8-2.2 3.3-5 3.3s-5-1.5-5-3.3c0-1.8 2.2-3.3 5-3.3Zm3.2-1.9a1.2 1.2 0 1 0 0 2.4 1.2 1.2 0 0 0 0-2.4ZM8.8 9.5a.8.8 0 1 0 0 1.6.8.8 0 0 0 0-1.6Zm6.4 0a.8.8 0 1 0 0 1.6.8.8 0 0 0 0-1.6Zm-4.6 2.3a2 2 0 0 0 2.8 0 .5.5 0 1 1 .7.7 3 3 0 0 1-4.2 0 .5.5 0 0 1 .7-.7Zm7.9-1.6a1.5 1.5 0 0 0-2.2-1.3 6.4 6.4 0 0 0-3.6-1.2l.6-2 1.7.4a1.8 1.8 0 1 0 .2-1l-2.1-.5a.5.5 0 0 0-.6.3l-.8 2.6a6.5 6.5 0 0 0-3.7 1.2 1.5 1.5 0 1 0-1 2.8c0 2.4 2.7 4.4 6 4.4 3.3 0 6-2 6-4.4a1.5 1.5 0 0 0 .5-1.1Z" fill="currentColor"/></svg>',
        'pinterest' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true"><path d="M12.1 3a9 9 0 0 0-3.3 17.4l1.1-4.2c-.3-.6-.5-1.5-.5-2.4 0-2 1.2-3.6 2.6-3.6 1.2 0 1.8.9 1.8 2 0 1.2-.8 3-1.2 4.6-.3 1.3.7 2.3 2 2.3 2.4 0 4.3-2.5 4.3-6.1 0-3.2-2.3-5.4-5.6-5.4-3.8 0-6.1 2.9-6.1 5.9 0 1.2.4 2.4 1 3.1.1.1.1.2.1.4l-.4 1.5c-.1.2-.2.3-.4.2-1.4-.6-2.3-2.4-2.3-4 0-3.2 2.3-6.1 6.8-6.1 3.5 0 6.2 2.5 6.2 5.9 0 3.5-2.2 6.4-5.2 6.4-1 0-2-.5-2.3-1.1l-.6 2.2c-.2.8-.7 1.8-1 2.4a9 9 0 1 0 2.4-17.7Z" fill="currentColor"/></svg>',
        'mastodon' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true"><path d="M18.9 7.5c0-4.2-2.8-5.4-2.8-5.4C14.7 1.5 12.3 1.3 12 1.3h-.1c-.3 0-2.7.2-4.1.8 0 0-2.8 1.2-2.8 5.4 0 1-.1 2.2 0 3.5.2 4.4.8 8.7 4.9 9.8 1.9.5 3.5.6 4.8.5 2.4-.1 3.8-.8 3.8-.8l-.1-1.7s-1.7.5-3.6.5c-1.8 0-3.8-.5-4.1-2.4a4.6 4.6 0 0 1 0-.8s1.8.4 4 .5c1.3 0 2.5-.1 3.7-.2 2.3-.3 4.2-1.8 4.4-3.2.4-2.2.3-5.4.3-5.4Zm-2.4 5.5h-1.9V8.5c0-1-.4-1.6-1.3-1.6-1 0-1.4.7-1.4 2v2.5h-1.8V8.9c0-1.3-.4-2-1.4-2-.9 0-1.3.6-1.3 1.6V13H5.5V8.4c0-1 .3-1.8.8-2.4.6-.6 1.3-.9 2.3-.9 1.2 0 2 .5 2.5 1.4l.5.9.5-.9c.5-.9 1.3-1.4 2.5-1.4 1 0 1.8.3 2.3.9.5.6.8 1.4.8 2.4V13Z" fill="currentColor"/></svg>',
        'threads' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true"><path d="M16.5 11.3a4.9 4.9 0 0 0-.4-2c-.8-1.8-2.5-2.7-4.9-2.7-2.2 0-4 .9-5 2.5l1.9 1.3c.7-1.1 1.7-1.6 3.1-1.6 1.5 0 2.5.5 3 1.5.2.4.3.9.3 1.4a20 20 0 0 0-3.3.2c-2.4.4-3.8 1.7-3.8 3.6 0 2.1 1.7 3.5 4.2 3.5 2 0 3.4-.8 4.1-2.2.5-1 .8-2.3.8-3.8.8.4 1.2 1 1.2 1.8 0 1.8-1.7 3.2-4.6 3.2-3.8 0-6.3-2.4-6.3-6.1 0-3.8 2.5-6.2 6.2-6.2 3.6 0 6 2.2 6.1 5.7h2.2C20.2 7.5 17 4.6 12.7 4.6c-5 0-8.4 3.3-8.4 8.3 0 4.8 3.4 8.2 8.5 8.2 4.3 0 7-2.1 7-5.2 0-2.3-1.4-3.9-3.3-4.6Zm-2.3 3.2c0 1.8-.9 2.9-2.5 2.9-1.2 0-2-.6-2-1.5 0-.8.6-1.5 2-1.8.8-.2 1.6-.2 2.5-.2v.6Z" fill="currentColor"/></svg>',
        'email' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true"><path d="M4 6h16a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm0 2v.2l8 4.8 8-4.8V8H4Zm16 8V10.5l-7.5 4.5a1 1 0 0 1-1 0L4 10.5V16h16Z" fill="currentColor"/></svg>',
        'instagram' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true"><path d="M7.8 3h8.4A4.8 4.8 0 0 1 21 7.8v8.4a4.8 4.8 0 0 1-4.8 4.8H7.8A4.8 4.8 0 0 1 3 16.2V7.8A4.8 4.8 0 0 1 7.8 3Zm0 1.7A3.1 3.1 0 0 0 4.7 7.8v8.4a3.1 3.1 0 0 0 3.1 3.1h8.4a3.1 3.1 0 0 0 3.1-3.1V7.8a3.1 3.1 0 0 0-3.1-3.1H7.8Zm8.8 1.3a1.1 1.1 0 1 1 0 2.2 1.1 1.1 0 0 1 0-2.2ZM12 7.6A4.4 4.4 0 1 1 7.6 12 4.4 4.4 0 0 1 12 7.6Zm0 1.7A2.7 2.7 0 1 0 14.7 12 2.7 2.7 0 0 0 12 9.3Z" fill="currentColor"/></svg>',
        'youtube' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true"><path d="M21.6 7.2a2.8 2.8 0 0 0-2-2C17.8 4.7 12 4.7 12 4.7s-5.8 0-7.6.5a2.8 2.8 0 0 0-2 2A29 29 0 0 0 2 12a29 29 0 0 0 .4 4.8 2.8 2.8 0 0 0 2 2c1.8.5 7.6.5 7.6.5s5.8 0 7.6-.5a2.8 2.8 0 0 0 2-2A29 29 0 0 0 22 12a29 29 0 0 0-.4-4.8ZM10 15.5v-7l6 3.5-6 3.5Z" fill="currentColor"/></svg>',
        'tiktok' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true"><path d="M15.1 3h2.7a4.7 4.7 0 0 0 3.3 3.3v2.8a7.3 7.3 0 0 1-3.3-.8V14a6 6 0 1 1-6-6h.3V11a3.2 3.2 0 1 0 2.9 3.1V3Z" fill="currentColor"/></svg>',
        'custom' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true"><path d="M12 3 3 7.5v9L12 21l9-4.5v-9L12 3Zm0 2 6.6 3.3L12 11.6 5.4 8.3 12 5Zm-7 5 6 3v6.7l-6-3V10Zm8 9.7V13l6-3v6.7l-6 3Z" fill="currentColor"/></svg>',
    );

    $allowed_svg = array(
        'svg' => array(
            'viewBox' => true,
            'viewbox' => true,
            'xmlns' => true,
            'role' => true,
            'aria-hidden' => true,
        ),
        'path' => array(
            'd' => true,
            'fill' => true,
        ),
    );

    return isset($svg_map[$network]) ? wp_kses($svg_map[$network], $allowed_svg) : '';
}

function am24h_share_icon_library_catalog(): array
{
    return array(
        'simple-icons' => __('Simple Icons', 'am24h'),
        'bootstrap-icons' => __('Bootstrap Icons', 'am24h'),
        'custom-source' => __('Custom source', 'am24h'),
    );
}

function am24h_share_icon_library_map(string $library = 'simple-icons'): array
{
    if ($library === 'bootstrap-icons') {
        return array(
            'whatsapp' => 'whatsapp',
            'facebook' => 'facebook',
            'x' => 'twitter-x',
            'linkedin' => 'linkedin',
            'telegram' => 'telegram',
            'reddit' => 'reddit',
            'pinterest' => 'pinterest',
            'threads' => 'threads',
            'email' => 'envelope',
            'instagram' => 'instagram',
            'youtube' => 'youtube',
            'tiktok' => 'tiktok',
            'copy' => 'link-45deg',
        );
    }

    return array(
        'whatsapp' => 'whatsapp',
        'facebook' => 'facebook',
        'x' => 'x',
        'linkedin' => 'linkedin',
        'telegram' => 'telegram',
        'reddit' => 'reddit',
        'pinterest' => 'pinterest',
        'mastodon' => 'mastodon',
        'threads' => 'threads',
        'instagram' => 'instagram',
        'youtube' => 'youtube',
        'tiktok' => 'tiktok',
    );
}

function am24h_build_share_icon_library_url(string $library, string $icon_slug): string
{
    $library = sanitize_key($library);
    $icon_slug = sanitize_file_name($icon_slug);

    if ($icon_slug === '') {
        return '';
    }

    if ($library === 'bootstrap-icons') {
        return 'https://cdn.jsdelivr.net/npm/bootstrap-icons@latest/icons/' . $icon_slug . '.svg';
    }

    if ($library === 'simple-icons') {
        return 'https://cdn.jsdelivr.net/npm/simple-icons@latest/icons/' . $icon_slug . '.svg';
    }

    return '';
}

function am24h_is_allowed_share_icon_download_url(string $url): bool
{
    $url = esc_url_raw($url);

    if ($url === '') {
        return false;
    }

    $parts = wp_parse_url($url);

    if (! is_array($parts) || empty($parts['host']) || empty($parts['scheme']) || empty($parts['path'])) {
        return false;
    }

    if (strtolower((string) $parts['scheme']) !== 'https' || strtolower((string) $parts['host']) !== 'cdn.jsdelivr.net') {
        return false;
    }

    $path = strtolower((string) $parts['path']);

    return strpos($path, '/npm/simple-icons@') === 0 || strpos($path, '/npm/bootstrap-icons@') === 0;
}

function am24h_get_uploaded_share_icon_url(string $network): string
{
    $network = sanitize_key($network);
    $location = am24h_get_share_icons_upload_location();

    if (is_wp_error($location)) {
        return '';
    }

    $file = trailingslashit($location['base_dir']) . $network . '.svg';

    if (! is_readable($file)) {
        return '';
    }

    return trailingslashit($location['base_url']) . $network . '.svg';
}

/**
 * @return array{base_dir: string, base_url: string}|WP_Error
 */
function am24h_get_share_icons_upload_location()
{
    $uploads = wp_upload_dir();

    if (! empty($uploads['error'])) {
        return new WP_Error('am24h_share_icons_uploads_error', __('Unable to resolve uploads directory for share icons.', 'am24h'));
    }

    $base_dir = trailingslashit((string) $uploads['basedir']) . 'am24h/share-icons';
    $base_url = trailingslashit((string) $uploads['baseurl']) . 'am24h/share-icons';

    if (! wp_mkdir_p($base_dir)) {
        return new WP_Error('am24h_share_icons_dir_create_error', __('Unable to create share icons directory inside uploads.', 'am24h'));
    }

    return array(
        'base_dir' => $base_dir,
        'base_url' => $base_url,
    );
}

/**
 * @return string|WP_Error
 */
function am24h_store_share_icon_svg(string $network, string $svg_source)
{
    $network = sanitize_key($network);

    if ($network === '') {
        return new WP_Error('am24h_share_icons_invalid_network', __('Invalid network key for icon storage.', 'am24h'));
    }

    $clean_svg = am24h_sanitize_share_svg_markup($svg_source);

    if ($clean_svg === '') {
        return new WP_Error('am24h_share_icons_invalid_svg', __('Provided SVG source is empty or invalid.', 'am24h'));
    }

    $location = am24h_get_share_icons_upload_location();

    if (is_wp_error($location)) {
        return $location;
    }

    global $wp_filesystem;

    if (! function_exists('WP_Filesystem')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    if (! WP_Filesystem() || ! isset($wp_filesystem)) {
        return new WP_Error('am24h_share_icons_fs_failed', __('Unable to initialize filesystem for icon storage.', 'am24h'));
    }

    $target = trailingslashit($location['base_dir']) . $network . '.svg';
    $target = wp_normalize_path($target);
    $base = trailingslashit(wp_normalize_path($location['base_dir']));

    if (strpos($target, $base) !== 0) {
        return new WP_Error('am24h_share_icons_invalid_path', __('Invalid target path for share icon.', 'am24h'));
    }

    if ($wp_filesystem->put_contents($target, $clean_svg, FS_CHMOD_FILE) === false) {
        return new WP_Error('am24h_share_icons_write_failed', __('Unable to save share icon SVG file.', 'am24h'));
    }

    return trailingslashit($location['base_url']) . $network . '.svg';
}

/**
 * @return string|WP_Error
 */
function am24h_download_share_icon_from_library(string $network, string $library = 'simple-icons')
{
    $network = sanitize_key($network);
    $library = sanitize_key($library);

    if (! isset(am24h_share_icon_library_catalog()[$library]) || $library === 'custom-source') {
        return new WP_Error('am24h_share_icons_invalid_library', __('Unsupported icon library selected.', 'am24h'));
    }

    $map = am24h_share_icon_library_map($library);

    if (! isset($map[$network])) {
        return new WP_Error('am24h_share_icons_not_available', __('This network is not available in the selected icon library.', 'am24h'));
    }

    $icon_slug = sanitize_key($map[$network]);
    $url = am24h_build_share_icon_library_url($library, $icon_slug);

    if (! am24h_is_allowed_share_icon_download_url($url)) {
        return new WP_Error('am24h_share_icons_invalid_url', __('Blocked icon download URL.', 'am24h'));
    }

    $response = wp_safe_remote_get(
        $url,
        array(
            'timeout' => 20,
            'reject_unsafe_urls' => true,
            'limit_response_size' => 131072,
        )
    );

    if (is_wp_error($response)) {
        return new WP_Error('am24h_share_icons_download_failed', __('Unable to download SVG icon from selected library.', 'am24h'));
    }

    if ((int) wp_remote_retrieve_response_code($response) !== 200) {
        return new WP_Error('am24h_share_icons_download_http_error', __('Icon library request returned a non-200 status.', 'am24h'));
    }

    $body = wp_remote_retrieve_body($response);

    if (! is_string($body) || trim($body) === '') {
        return new WP_Error('am24h_share_icons_empty_svg', __('Downloaded icon SVG is empty.', 'am24h'));
    }

    return am24h_store_share_icon_svg($network, $body);
}

function am24h_sanitize_share_svg_markup(string $svg): string
{
    $svg = trim(str_replace("\0", '', $svg));

    if ($svg === '' || stripos($svg, '<svg') === false) {
        return '';
    }

    $allowed = array(
        'svg' => array(
            'xmlns' => true,
            'viewBox' => true,
            'viewbox' => true,
            'width' => true,
            'height' => true,
            'fill' => true,
            'stroke' => true,
            'aria-hidden' => true,
            'role' => true,
        ),
        'path' => array('d' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true),
        'circle' => array('cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true),
        'rect' => array('x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true),
        'g' => array('fill' => true, 'stroke' => true, 'stroke-width' => true, 'transform' => true),
        'line' => array('x1' => true, 'x2' => true, 'y1' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true),
        'polyline' => array('points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true),
        'polygon' => array('points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true),
    );

    return wp_kses($svg, $allowed);
}
