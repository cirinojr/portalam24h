<?php

class Am24h_LanguageCatalog
{
    /**
     * Supported locale codes.
     *
     * @return string[]
     */
    public static function codes(): array
    {
        return array(
            'pt_BR',
            'en_US',
            'es_ES',
            'fr_FR',
            'de_DE',
            'ko_KR',
            'da_DK',
            'ar',
            'nl_NL',
            'hu_HU',
            'hi_IN',
            'id_ID',
            'it_IT',
            'ja',
            'nb_NO',
            'pl_PL',
            'pt_PT',
            'sv_SE',
            'tr_TR',
            'fil',
            'fi',
            'th',
            'el',
            'cs_CZ',
            'lb_LU',
            'ro_RO',
            'sr_RS',
            'bg_BG',
        );
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return array(
            'pt_BR' => __('Portuguese (Brazil)', 'am24h'),
            'en_US' => __('English (United States)', 'am24h'),
            'es_ES' => __('Spanish (Spain)', 'am24h'),
            'fr_FR' => __('French (France)', 'am24h'),
            'de_DE' => __('German (Germany)', 'am24h'),
            'ko_KR' => __('Korean (South Korea)', 'am24h'),
            'da_DK' => __('Danish (Denmark)', 'am24h'),
            'ar'    => __('Arabic', 'am24h'),
            'nl_NL' => __('Dutch (Netherlands)', 'am24h'),
            'hu_HU' => __('Hungarian (Hungary)', 'am24h'),
            'hi_IN' => __('Hindi (India)', 'am24h'),
            'id_ID' => __('Indonesian (Indonesia)', 'am24h'),
            'it_IT' => __('Italian (Italy)', 'am24h'),
            'ja'    => __('Japanese (Japan)', 'am24h'),
            'nb_NO' => __('Norwegian (Norway)', 'am24h'),
            'pl_PL' => __('Polish (Poland)', 'am24h'),
            'pt_PT' => __('Portuguese (Portugal)', 'am24h'),
            'sv_SE' => __('Swedish (Sweden)', 'am24h'),
            'tr_TR' => __('Turkish (Turkey)', 'am24h'),
            'fil'   => __('Filipino (Philippines)', 'am24h'),
            'fi'    => __('Finnish (Finland)', 'am24h'),
            'th'    => __('Thai (Thailand)', 'am24h'),
            'el'    => __('Greek (Greece)', 'am24h'),
            'cs_CZ' => __('Czech (Czech Republic)', 'am24h'),
            'lb_LU' => __('Luxembourgish (Luxembourg)', 'am24h'),
            'ro_RO' => __('Romanian (Romania)', 'am24h'),
            'sr_RS' => __('Serbian (Serbia)', 'am24h'),
            'bg_BG' => __('Bulgarian (Bulgaria)', 'am24h'),
        );
    }

    public static function label(string $code): string
    {
        $labels = self::labels();

        return isset($labels[$code]) ? $labels[$code] : $code;
    }
}
