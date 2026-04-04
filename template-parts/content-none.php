<section class="no-results not-found">
    <header class="page-header">
        <h1 class="page-title"><?php esc_html_e( 'Nada encontrado', 'am24h' ); ?></h1>
    </header>

    <div class="page-content">
        <?php if ( is_search() ) : ?>
            <p><?php esc_html_e( 'Nenhum resultado para sua busca. Tente novamente.', 'am24h' ); ?></p>
        <?php elseif ( is_home() ) : ?>
            <p><?php esc_html_e( 'Ainda não há posts publicados.', 'am24h' ); ?></p>
        <?php elseif ( is_archive() ) : ?>
            <p><?php esc_html_e( 'Nenhum conteúdo encontrado neste arquivo.', 'am24h' ); ?></p>
        <?php else : ?>
            <p><?php esc_html_e( 'Não encontramos o que você procurava. Tente uma busca:', 'am24h' ); ?></p>
        <?php endif; ?>

        <?php get_search_form(); ?>
    </div>
</section>
