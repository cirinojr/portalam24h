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

function am24h_theme(): Am24h_Bootstrap
{
    return Am24h_Bootstrap::instance();
}

function am24h_sanitize_inline_css(string $css): string
{
    $css = str_replace("\0", '', $css);

    return str_ireplace('</style', '<\\/style', $css);
}
