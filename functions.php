<?php

/**
 * Instancia a classe ThemeOptions
 * Todas as funcionalidades do tema estão organizadas na classe ThemeOptions
 */
require_once get_template_directory() . '/includes/ThemeOptions.php';

// Instanciar a classe ThemeOptions como variável global
global $theme_options;
$theme_options = new ThemeOptions();

/**
 * Função helper global para limitar excerpt (wrapper para o método da classe)
 */
function skallar_limit_excerpt($excerpt, $limit = 150)
{
    return ThemeOptions::limit_excerpt($excerpt, $limit);
}
