<?php

class Am24h_ColorUtils
{
    public static function darken(string $color, int $percent): string
    {
        $normalized = ltrim($color, '#');

        if (strlen($normalized) === 3) {
            $normalized = str_repeat(substr($normalized, 0, 1), 2)
                . str_repeat(substr($normalized, 1, 1), 2)
                . str_repeat(substr($normalized, 2, 1), 2);
        }

        if (! preg_match('/^[a-fA-F0-9]{6}$/', $normalized)) {
            return '#000000';
        }

        $r = hexdec(substr($normalized, 0, 2));
        $g = hexdec(substr($normalized, 2, 2));
        $b = hexdec(substr($normalized, 4, 2));

        $r = (int) max(0, min(255, $r - ($r * $percent / 100)));
        $g = (int) max(0, min(255, $g - ($g * $percent / 100)));
        $b = (int) max(0, min(255, $b - ($b * $percent / 100)));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
