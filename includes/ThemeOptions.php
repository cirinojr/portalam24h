<?php

class ThemeOptions
{

	public function __construct()
	{
		add_action('after_setup_theme', [$this, 'setup']);
		add_action('wp_enqueue_scripts', [$this, 'remove_global_styles']);
		add_action('wp_head', [$this, 'themeHeadTags']);
		add_action('init',  [$this, 'register_my_menus']);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_script']);
		add_action('admin_menu', [$this, 'add_theme_menu']);
		add_action('admin_init', [$this, 'register_settings']);
		add_action('admin_notices', [$this, 'admin_notices']);
		add_action('wp_head', [$this, 'output_custom_colors']);

        // Ocultar título
        add_action('add_meta_boxes', [$this, 'register_hide_title_metabox']);
        add_action('save_post',      [$this, 'save_hide_title_meta']);
        add_filter('post_class', [$this, 'add_hide_title_post_class'], 10, 3);

		// Hooks para CSS crítico e limitação de excerpt
		add_action('wp_head', [$this, 'load_critical_css'], 1);
		add_action('wp_enqueue_scripts', [$this, 'remove_default_styles'], 100);
		add_action('after_setup_theme', [$this, 'load_textdomain']);
		add_filter('locale', [$this, 'apply_custom_language']);

		add_filter('get_the_excerpt', [$this, 'custom_excerpt_force_paragraph']);
		add_filter('the_content', [$this, 'custom_remove_first_paragraph_from_content']);
		add_filter('show_admin_bar', '__return_false');
		$this->remove_unnecessary();
		$this->setup();
	}


	public function setup(): void
	{

		add_theme_support('title-tag');
		add_theme_support('align-wide');
		add_theme_support('post-thumbnails');

		add_image_size('thumb-desktop', 248, 387, true);
		add_image_size('thumb-mobile', 149, 232, true);


		add_theme_support('custom-logo', array(
			'height'      => 100,
			'width'       => 100,
			'flex-height' => true,
			'flex-width'  => true,
			'header-text' => array('site-title', 'site-description'),
		));
	}


	public function themeHeadTags(): void
	{
		// $css = file_get_contents(get_template_directory() . '/dist/styles/Critical/index.css');
		// echo '<style type="text/css">' . $css . '</style>';
		// // best way to load 'asynchronous' CSS
		// echo '<link rel="preload" href="' . esc_url(get_template_directory_uri()) . '/dist/styles/theme.css" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';
		// echo '<noscript><link rel="stylesheet" href="' . esc_url(get_template_directory_uri()) . '/dist/styles/theme.css"></noscript>';
	}

	public function enqueue_script(): void
	{
		$theme_dir = get_template_directory();
		$theme_uri = get_template_directory_uri();

		$scripts = array(
			'skallar-theme-utm-passer'  => '/assets/js/utm-passer.js',
			'skallar-theme-search-bar' => '/base/scripts/search-bar.js',
		);

		foreach ($scripts as $handle => $relative_path) {
			$file_path = $theme_dir . $relative_path;
			$version  = file_exists($file_path) ? filemtime($file_path) : null;

			wp_enqueue_script(
				$handle,
				$theme_uri . $relative_path,
				array(),
				$version,
				true
			);
		}
	}

	public function remove_unnecessary(): void
	{

		// REMOVE WP EMOJI
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('wp_print_styles', 'print_emoji_styles');

		remove_action('admin_print_scripts', 'print_emoji_detection_script');
		remove_action('admin_print_styles', 'print_emoji_styles');

		// remove all tags from header
		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'wp_generator');
		remove_action('wp_head', 'feed_links', 2);
		remove_action('wp_head', 'index_rel_link');
		remove_action('wp_head', 'wlwmanifest_link');
		remove_action('wp_head', 'feed_links_extra', 3);
		remove_action('wp_head', 'start_post_rel_link', 10, 0);
		remove_action('wp_head', 'parent_post_rel_link', 10, 0);
		remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
		remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
		remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
		remove_action('wp_head', 'rest_output_link_wp_head');
		remove_action('wp_head', 'wp_oembed_add_discovery_links');
		remove_action('template_redirect', 'rest_output_link_header', 11);
		// language
		add_filter('multilingualpress.hreflang_type', '__return_false');
	}


	function register_my_menus()
	{
		register_nav_menus(
			array(
				'header-menu' => __('Header Menu (Main Navigation)', 'skallar'),
				'bottom-menu' => __('Footer Menu (Footer Navigation)', 'skallar')
			)
		);
	}


	/**
	 * Optionally disable block/global styles when explicitly requested.
	 * Disabling saves payload but can break block-based layouts, so keep off by default.
	 */
	public function remove_global_styles(): void
	{
		if (is_admin()) {
			return;
		}

		$disable_block_styles = defined('SKALLAR_DISABLE_BLOCK_STYLES')
			? SKALLAR_DISABLE_BLOCK_STYLES
			: false;

		$disable_block_styles = apply_filters('skallar_disable_block_styles', $disable_block_styles);

		if (!$disable_block_styles) {
			return;
		}

		wp_dequeue_style('global-styles');
		wp_dequeue_style('classic-theme-styles');
		wp_dequeue_style('wp-block-library');
		wp_dequeue_style('wp-block-library-theme');
		wp_dequeue_style('wc-blocks-style');
		wp_dequeue_style('dashicons-css');
	}





	public function createNonce()
	{

		$ajax_nonce = wp_create_nonce('nonce_name');

		wp_localize_script(
			'embed-gutenberg-scripts',
			'ajax_object',
			array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => $ajax_nonce)
		);
	}

	/**
	 * Adiciona menu no admin do WordPress
	 */
	public function add_theme_menu()
	{
		// Menu principal do tema
		add_menu_page(
			__('Theme Settings', 'skallar'),
			__('Skallar Theme', 'skallar'),
			'manage_options',
			'skallar-theme',
			[$this, 'general_admin_page'],
			'dashicons-admin-appearance',
			30
		);

		// Submenu para configurações gerais
		add_submenu_page(
			'skallar-theme',
			__('General Settings', 'skallar'),
			__('General Settings', 'skallar'),
			'manage_options',
			'skallar-theme',
			[$this, 'general_admin_page']
		);

		// Submenu para configurações de cores
		add_submenu_page(
			'skallar-theme',
			__('Color Settings', 'skallar'),
			__('Colors', 'skallar'),
			'manage_options',
			'skallar-colors',
			[$this, 'colors_admin_page']
		);

		// Submenu para configurações de idioma
		add_submenu_page(
			'skallar-theme',
			__('Language Settings', 'skallar'),
			__('Language', 'skallar'),
			'manage_options',
			'skallar-language',
			[$this, 'language_admin_page']
		);
	}

	/**
	 * Registra as configurações
	 */
	public function register_settings()
	{
		register_setting('skallar_theme_settings', 'show_stock_quotes', [$this, 'sanitize_checkbox']);
		register_setting('skallar_theme_settings', 'skallar_logo_url', [$this, 'sanitize_url']);
		register_setting('skallar_theme_settings', 'skallar_logo_text', 'sanitize_text_field');
		register_setting('skallar_theme_settings', 'skallar_use_text_logo', [$this, 'sanitize_checkbox']);
		// Configurações das categorias exibidas na Home
		register_setting('skallar_theme_settings', 'skallar_home_categories', [$this, 'sanitize_home_categories']);

		// Configurações de cores
		register_setting('skallar_color_settings', 'skallar_primary_color', [$this, 'sanitize_color']);
		register_setting('skallar_color_settings', 'skallar_secondary_color', [$this, 'sanitize_color']);
		register_setting('skallar_color_settings', 'skallar_text_color', [$this, 'sanitize_color']);
		register_setting('skallar_color_settings', 'skallar_background_color', [$this, 'sanitize_color']);
		register_setting('skallar_color_settings', 'skallar_success_color', [$this, 'sanitize_color']);
		register_setting('skallar_color_settings', 'skallar_danger_color', [$this, 'sanitize_color']);

		// Configurações de idioma
		register_setting('skallar_language_settings', 'skallar_site_language', [$this, 'sanitize_language']);
	}



	public function custom_excerpt_force_paragraph($text = '') {
        global $post;

        // Se o post tiver resumo manual, retorna esse conteúdo
        if (!empty($post->post_excerpt)) {
            // Armazena um flag para indicar que é resumo manual
            set_transient('has_manual_excerpt_' . $post->ID, true, MINUTE_IN_SECONDS);
            return $post->post_excerpt;
        }

        // Conteúdo cru, sem filtros
        $content = $post->post_content;

        // Pega o primeiro parágrafo do conteúdo original
        if (preg_match('/<p>(.*?)<\/p>/is', $content, $matches)) {
            // Salva o trecho para remover depois
            set_transient('first_paragraph_' . $post->ID, $matches[0], MINUTE_IN_SECONDS);
            delete_transient('has_manual_excerpt_' . $post->ID); // Garante que a flag seja limpa
            return wp_strip_all_tags($matches[0]);
        }

        return '';
    }


	public function custom_remove_first_paragraph_from_content($content) {
        global $post;

        if (!is_singular('post') || !is_main_query() || !in_the_loop()) {
            return $content;
        }

        // Se for resumo manual, não remove nada
        if (get_transient('has_manual_excerpt_' . $post->ID)) {
            return $content;
        }

        // Recupera o parágrafo salvo
        $first_paragraph = get_transient('first_paragraph_' . $post->ID);

        if ($first_paragraph) {
            $content = str_replace($first_paragraph, '', $content);
        }

        return $content;
    }



	/**
	 * Página de administração
	 */
	/**
	 * Página de administração para configurações gerais
	 */
	public function general_admin_page()
	{
?>
		<div class="wrap">
			<h1><?php esc_html_e('General Theme Settings', 'skallar'); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields('skallar_theme_settings'); ?>
				<?php do_settings_sections('skallar_theme_settings'); ?>

				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e('Logo Settings', 'skallar'); ?></th>
						<td>
							<fieldset>
								<label for="skallar_use_text_logo">
									<input name="skallar_use_text_logo" type="checkbox" id="skallar_use_text_logo" value="1" <?php checked(1, get_option('skallar_use_text_logo', 0)); ?> />
									<?php esc_html_e('Use text logo instead of image', 'skallar'); ?>
								</label>
								<br><br>

								<div id="logo_image_section" style="<?php echo get_option('skallar_use_text_logo', 0) ? 'display:none;' : ''; ?>">
									<label for="skallar_logo_url"><?php esc_html_e('Logo Image URL:', 'skallar'); ?></label><br>
									<input type="url" name="skallar_logo_url" id="skallar_logo_url" value="<?php echo esc_attr(get_option('skallar_logo_url', get_template_directory_uri() . '/base/images/Logo.png')); ?>" class="regular-text" />
									<br>
									<p class="description">
										<?php esc_html_e('Enter the URL for your logo image. Leave empty to use the default logo.', 'skallar'); ?>
									</p>
									<?php
									$current_logo = get_option('skallar_logo_url', get_template_directory_uri() . '/base/images/Logo.png');
									if ($current_logo): ?>
										<br>
										<strong><?php esc_html_e('Current Logo Preview:', 'skallar'); ?></strong><br>
										<img src="<?php echo esc_url($current_logo); ?>" alt="<?php esc_attr_e('Current Logo', 'skallar'); ?>" style="max-width: 200px; max-height: 100px; margin-top: 10px;" />
									<?php endif; ?>
								</div>

								<div id="logo_text_section" style="<?php echo get_option('skallar_use_text_logo', 0) ? '' : 'display:none;'; ?>">
									<label for="skallar_logo_text"><?php esc_html_e('Logo Text:', 'skallar'); ?></label><br>
									<input type="text" name="skallar_logo_text" id="skallar_logo_text" value="<?php echo esc_attr(get_option('skallar_logo_text', get_bloginfo('name'))); ?>" class="regular-text" />
									<br>
									<p class="description">
										<?php esc_html_e('Enter the text to display as your logo. Leave empty to use the site name.', 'skallar'); ?>
									</p>
								</div>

								<script>
									document.getElementById('skallar_use_text_logo').addEventListener('change', function() {
										const imageSection = document.getElementById('logo_image_section');
										const textSection = document.getElementById('logo_text_section');

										if (this.checked) {
											imageSection.style.display = 'none';
											textSection.style.display = 'block';
										} else {
											imageSection.style.display = 'block';
											textSection.style.display = 'none';
										}
									});
								</script>
							</fieldset>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e('Display Elements', 'skallar'); ?></th>
						<td>
							<fieldset>
								<label for="show_stock_quotes">
									<input name="show_stock_quotes" type="checkbox" id="show_stock_quotes" value="1" <?php checked(1, get_option('show_stock_quotes', 1)); ?> />
									<?php esc_html_e('Show stock exchange quotes on homepage', 'skallar'); ?>
								</label>
								<p class="description">
									<?php esc_html_e('Check this option to display the stock exchange quotes section (skl-stock-exchange-quotes) on the homepage.', 'skallar'); ?>
								</p>
							</fieldset>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e('Home Page Categories', 'skallar'); ?></th>
						<td>
							<fieldset>
								<select name="skallar_home_categories[]" id="skallar_home_categories" multiple="multiple" style="min-width: 300px; min-height: 150px;">
									<?php
									$selected_categories = (array) get_option('skallar_home_categories', array());
									$categories = get_categories(array(
										'orderby' => 'name',
										'order'   => 'ASC',
										'hide_empty' => false
									));

									foreach ($categories as $category) :
										$selected = in_array($category->term_id, $selected_categories) ? 'selected="selected"' : ''; ?>
										<option value="<?php echo esc_attr($category->term_id); ?>" <?php echo $selected; ?>>
											<?php echo esc_html($category->name); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p class="description">
									<?php esc_html_e('Select the categories to show in home page. Hold Ctrl/Cmd to select multiple categories.', 'skallar'); ?>
								</p>
							</fieldset>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>

			<hr>

			<h2><?php esc_html_e('Information', 'skallar'); ?></h2>
			<p><strong><?php esc_html_e('Theme version:', 'skallar'); ?></strong> 1.0.0</p>
			<p><strong><?php esc_html_e('Developed by:', 'skallar'); ?></strong> <?php esc_html_e('Your Company', 'skallar'); ?></p>

			<hr>

			<h2><?php esc_html_e('Navigation Menus', 'skallar'); ?></h2>
			<p><?php esc_html_e('This theme supports two navigation menus:', 'skallar'); ?></p>
			<ul>
				<li><strong><?php esc_html_e('Header Menu (Main Navigation):', 'skallar'); ?></strong> <?php esc_html_e('Displayed in the header area with button styling', 'skallar'); ?></li>
				<li><strong><?php esc_html_e('Footer Menu (Footer Navigation):', 'skallar'); ?></strong> <?php esc_html_e('Displayed in the footer area with logo and links', 'skallar'); ?></li>
			</ul>
			<p>
				<?php esc_html_e('To configure these menus, go to', 'skallar'); ?>
				<a href="<?php echo admin_url('nav-menus.php'); ?>" target="_blank">
					<?php esc_html_e('Appearance → Menus', 'skallar'); ?>
				</a>
			</p>
		</div>
	<?php
	}

	/**
	 * Página de administração para configurações de cores
	 */
	public function colors_admin_page()
	{
	?>
		<div class="wrap">
			<h1><?php esc_html_e('Color Settings', 'skallar'); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields('skallar_color_settings'); ?>
				<?php do_settings_sections('skallar_color_settings'); ?>

				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e('Primary Color', 'skallar'); ?></th>
						<td>
							<input type="color" name="skallar_primary_color" value="<?php echo esc_attr(get_option('skallar_primary_color', '#fd5e04')); ?>" />
							<p class="description">
								<?php esc_html_e('Used for buttons, links, and accent elements. Default: #fd5e04', 'skallar'); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e('Secondary Color', 'skallar'); ?></th>
						<td>
							<input type="color" name="skallar_secondary_color" value="<?php echo esc_attr(get_option('skallar_secondary_color', '#fef2e6')); ?>" />
							<p class="description">
								<?php esc_html_e('Used for secondary elements and light backgrounds. Default: #fef2e6', 'skallar'); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e('Text Color', 'skallar'); ?></th>
						<td>
							<input type="color" name="skallar_text_color" value="<?php echo esc_attr(get_option('skallar_text_color', '#1d2433')); ?>" />
							<p class="description">
								<?php esc_html_e('Main text color used throughout the site. Default: #1d2433', 'skallar'); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e('Background Color', 'skallar'); ?></th>
						<td>
							<input type="color" name="skallar_background_color" value="<?php echo esc_attr(get_option('skallar_background_color', '#f8f9fc')); ?>" />
							<p class="description">
								<?php esc_html_e('Main background color of the site. Default: #f8f9fc', 'skallar'); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e('Success Color', 'skallar'); ?></th>
						<td>
							<input type="color" name="skallar_success_color" value="<?php echo esc_attr(get_option('skallar_success_color', '#08875d')); ?>" />
							<p class="description">
								<?php esc_html_e('Used for success messages and positive indicators. Default: #08875d', 'skallar'); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e('Danger Color', 'skallar'); ?></th>
						<td>
							<input type="color" name="skallar_danger_color" value="<?php echo esc_attr(get_option('skallar_danger_color', '#e02d3c')); ?>" />
							<p class="description">
								<?php esc_html_e('Used for error messages and warning indicators. Default: #e02d3c', 'skallar'); ?>
							</p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>

			<hr>

			<h2><?php esc_html_e('Reset to Defaults', 'skallar'); ?></h2>
			<p><?php esc_html_e('Click the button below to reset all colors to their default values.', 'skallar'); ?></p>
			<form method="post" action="">
				<?php wp_nonce_field('skallar_reset_colors'); ?>
				<input type="hidden" name="action" value="reset_colors" />
				<input type="submit" class="button button-secondary" value="<?php esc_attr_e('Reset Colors', 'skallar'); ?>"
					onclick="return confirm('<?php esc_attr_e('Are you sure you want to reset all colors to default values?', 'skallar'); ?>');" />
			</form>

			<?php
			// Processar reset de cores
			if (isset($_POST['action']) && $_POST['action'] === 'reset_colors' && wp_verify_nonce($_POST['_wpnonce'], 'skallar_reset_colors')) {
				delete_option('skallar_primary_color');
				delete_option('skallar_secondary_color');
				delete_option('skallar_text_color');
				delete_option('skallar_background_color');
				delete_option('skallar_success_color');
				delete_option('skallar_danger_color');
				echo '<div class="notice notice-success"><p>' . esc_html__('Colors have been reset to default values.', 'skallar') . '</p></div>';
			}
			?>
		</div>
	<?php
	}

	/**
	 * Página de administração para configurações de idioma
	 */
	public function language_admin_page()
	{
		// Processar reset do idioma
		if (isset($_POST['action']) && $_POST['action'] === 'reset_language' && wp_verify_nonce($_POST['_wpnonce'], 'skallar_reset_language')) {
			delete_option('skallar_site_language');
			delete_option('WPLANG');
			echo '<div class="notice notice-success"><p>' .
				esc_html__('Language settings have been reset to default (Portuguese - Brazil).', 'skallar') .
				'</p></div>';
		}

		// Processar mudança de idioma
		if (isset($_POST['action']) && $_POST['action'] === 'change_language' && wp_verify_nonce($_POST['_wpnonce'], 'skallar_change_language')) {
			$new_language = sanitize_text_field($_POST['skallar_site_language']);
			update_option('skallar_site_language', $new_language);

			// Atualizar a opção WPLANG do WordPress
			update_option('WPLANG', $new_language);

			echo '<div class="notice notice-success"><p>' .
				sprintf(esc_html__('Language changed to %s. The site will display in the new language.', 'skallar'), $this->get_language_name($new_language)) .
				'</p></div>';
		}

		$current_language = get_option('skallar_site_language', get_locale());

	?>
		<div class="wrap">
			<h1><?php esc_html_e('Language Settings', 'skallar'); ?></h1>

			<div class="notice notice-info">
				<p>
					<strong><?php esc_html_e('Current Language:', 'skallar'); ?></strong>
					<?php echo esc_html($this->get_language_name($current_language)); ?>
					(<code><?php echo esc_html($current_language); ?></code>)
				</p>
			</div>

			<form method="post" action="options.php">
				<?php settings_fields('skallar_language_settings'); ?>
				<?php do_settings_sections('skallar_language_settings'); ?>

				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e('Site Language', 'skallar'); ?></th>
						<td>
							<select name="skallar_site_language" id="skallar_site_language">
								<option value="pt_BR" <?php selected($current_language, 'pt_BR'); ?>>
									🇧🇷 <?php esc_html_e('Portuguese (Brazil)', 'skallar'); ?>
								</option>
								<option value="en_US" <?php selected($current_language, 'en_US'); ?>>
									🇺🇸 <?php esc_html_e('English (United States)', 'skallar'); ?>
								</option>
								<option value="es_ES" <?php selected($current_language, 'es_ES'); ?>>
									🇪🇸 <?php esc_html_e('Spanish (Spain)', 'skallar'); ?>
								</option>
								<option value="fr_FR" <?php selected($current_language, 'fr_FR'); ?>>
									🇫🇷 <?php esc_html_e('French (France)', 'skallar'); ?>
								</option>
								<option value="de_DE" <?php selected($current_language, 'de_DE'); ?>>
									🇩🇪 <?php esc_html_e('German (Germany)', 'skallar'); ?>
								</option>
								<option value="ko_KR" <?php selected($current_language, 'ko_KR'); ?>>
									🇰🇷 <?php esc_html_e('Korean (South Korea)', 'skallar'); ?>
								</option>
								<option value="da_DK" <?php selected($current_language, 'da_DK'); ?>>
									🇩🇰 <?php esc_html_e('Danish (Denmark)', 'skallar'); ?>
								</option>
								<option value="ar" <?php selected($current_language, 'ar'); ?>>
									🇸🇦 <?php esc_html_e('Arabic', 'skallar'); ?>
								</option>
								<option value="nl_NL" <?php selected($current_language, 'nl_NL'); ?>>
									🇳🇱 <?php esc_html_e('Dutch (Netherlands)', 'skallar'); ?>
								</option>
								<option value="hu_HU" <?php selected($current_language, 'hu_HU'); ?>>
									🇭🇺 <?php esc_html_e('Hungarian (Hungary)', 'skallar'); ?>
								</option>
								<option value="hi_IN" <?php selected($current_language, 'hi_IN'); ?>>
									🇮🇳 <?php esc_html_e('Hindi (India)', 'skallar'); ?>
								</option>
								<option value="id_ID" <?php selected($current_language, 'id_ID'); ?>>
									🇮🇩 <?php esc_html_e('Indonesian (Indonesia)', 'skallar'); ?>
								</option>
								<option value="it_IT" <?php selected($current_language, 'it_IT'); ?>>
									🇮🇹 <?php esc_html_e('Italian (Italy)', 'skallar'); ?>
								</option>
								<option value="ja" <?php selected($current_language, 'ja'); ?>>
									🇯🇵 <?php esc_html_e('Japanese (Japan)', 'skallar'); ?>
								</option>
								<option value="nb_NO" <?php selected($current_language, 'nb_NO'); ?>>
									🇳🇴 <?php esc_html_e('Norwegian (Norway)', 'skallar'); ?>
								</option>
								<option value="pl_PL" <?php selected($current_language, 'pl_PL'); ?>>
									🇵🇱 <?php esc_html_e('Polish (Poland)', 'skallar'); ?>
								</option>
								<option value="pt_PT" <?php selected($current_language, 'pt_PT'); ?>>
									🇵🇹 <?php esc_html_e('Portuguese (Portugal)', 'skallar'); ?>
								</option>
								<option value="sv_SE" <?php selected($current_language, 'sv_SE'); ?>>
									🇸🇪 <?php esc_html_e('Swedish (Sweden)', 'skallar'); ?>
								</option>
								<option value="tr_TR" <?php selected($current_language, 'tr_TR'); ?>>
									🇹🇷 <?php esc_html_e('Turkish (Turkey)', 'skallar'); ?>
								</option>
								<option value="fil" <?php selected($current_language, 'fil'); ?>>
									🇵🇭 <?php esc_html_e('Filipino (Philippines)', 'skallar'); ?>
								</option>
								<option value="fi" <?php selected($current_language, 'fi'); ?>>
									🇫🇮 <?php esc_html_e('Finnish (Finland)', 'skallar'); ?>
								</option>
								<option value="th" <?php selected($current_language, 'th'); ?>>
									🇹🇭 <?php esc_html_e('Thai (Thailand)', 'skallar'); ?>
								</option>
								<option value="el" <?php selected($current_language, 'el'); ?>>
									🇬🇷 <?php esc_html_e('Greek (Greece)', 'skallar'); ?>
								</option>
								<option value="cs_CZ" <?php selected($current_language, 'cs_CZ'); ?>>
									🇨🇿 <?php esc_html_e('Czech (Czech Republic)', 'skallar'); ?>
								</option>
								<option value="lb_LU" <?php selected($current_language, 'lb_LU'); ?>>
									🇱🇺 <?php esc_html_e('Luxembourgish (Luxembourg)', 'skallar'); ?>
								</option>
								<option value="ro_RO" <?php selected($current_language, 'ro_RO'); ?>>
									🇷🇴 <?php esc_html_e('Romanian (Romania)', 'skallar'); ?>
								</option>
								<option value="sr_RS" <?php selected($current_language, 'sr_RS'); ?>>
									🇷🇸 <?php esc_html_e('Serbian (Serbia)', 'skallar'); ?>
								</option>
								<option value="bg_BG" <?php selected($current_language, 'bg_BG'); ?>>
									🇧🇬 <?php esc_html_e('Bulgarian (Bulgaria)', 'skallar'); ?>
								</option>
							</select>
							<p class="description">
								<?php esc_html_e('Select the language for your site. This will change the language of the theme interface and content.', 'skallar'); ?>
							</p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>

			<hr>

			<h2><?php esc_html_e('Quick Language Switch', 'skallar'); ?></h2>
			<p><?php esc_html_e('Click on a language below to change it immediately:', 'skallar'); ?></p>

			<div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
				<form method="post" action="" style="display: inline;">
					<?php wp_nonce_field('skallar_change_language'); ?>
					<input type="hidden" name="action" value="change_language" />
					<input type="hidden" name="skallar_site_language" value="pt_BR" />
					<button type="submit" class="button <?php echo ($current_language === 'pt_BR') ? 'button-primary' : 'button-secondary'; ?>" style="min-width: 150px;">
						🇧🇷 <?php esc_html_e('Portuguese', 'skallar'); ?>
						<?php if ($current_language === 'pt_BR') echo ' (' . esc_html__('Current', 'skallar') . ')'; ?>
					</button>
				</form>

				<form method="post" action="" style="display: inline;">
					<?php wp_nonce_field('skallar_change_language'); ?>
					<input type="hidden" name="action" value="change_language" />
					<input type="hidden" name="skallar_site_language" value="en_US" />
					<button type="submit" class="button <?php echo ($current_language === 'en_US') ? 'button-primary' : 'button-secondary'; ?>" style="min-width: 150px;">
						🇺🇸 <?php esc_html_e('English', 'skallar'); ?>
						<?php if ($current_language === 'en_US') echo ' (' . esc_html__('Current', 'skallar') . ')'; ?>
					</button>
				</form>

				<form method="post" action="" style="display: inline;">
					<?php wp_nonce_field('skallar_change_language'); ?>
					<input type="hidden" name="action" value="change_language" />
					<input type="hidden" name="skallar_site_language" value="es_ES" />
					<button type="submit" class="button <?php echo ($current_language === 'es_ES') ? 'button-primary' : 'button-secondary'; ?>" style="min-width: 150px;">
						🇪🇸 <?php esc_html_e('Spanish', 'skallar'); ?>
						<?php if ($current_language === 'es_ES') echo ' (' . esc_html__('Current', 'skallar') . ')'; ?>
					</button>
				</form>

				<form method="post" action="" style="display: inline;">
					<?php wp_nonce_field('skallar_change_language'); ?>
					<input type="hidden" name="action" value="change_language" />
					<input type="hidden" name="skallar_site_language" value="fr_FR" />
					<button type="submit" class="button <?php echo ($current_language === 'fr_FR') ? 'button-primary' : 'button-secondary'; ?>" style="min-width: 150px;">
						🇫🇷 <?php esc_html_e('French', 'skallar'); ?>
						<?php if ($current_language === 'fr_FR') echo ' (' . esc_html__('Current', 'skallar') . ')'; ?>
					</button>
				</form>
			</div>

			<hr>

			<h2><?php esc_html_e('Reset Language Settings', 'skallar'); ?></h2>
			<p><?php esc_html_e('If you are experiencing issues with the language settings, you can reset them to default values.', 'skallar'); ?></p>
			<form method="post" action="">
				<?php wp_nonce_field('skallar_reset_language'); ?>
				<input type="hidden" name="action" value="reset_language" />
				<input type="submit" class="button button-secondary" value="<?php esc_attr_e('Reset to Default Language', 'skallar'); ?>"
					onclick="return confirm('<?php esc_attr_e('Are you sure you want to reset the language to Portuguese (Brazil)?', 'skallar'); ?>');" />
			</form>

			<hr>

			<h2><?php esc_html_e('Language Information', 'skallar'); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e('Current Language:', 'skallar'); ?></th>
					<td><strong><?php echo esc_html($this->get_language_name($current_language)); ?></strong></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e('Language Code:', 'skallar'); ?></th>
					<td><code><?php echo esc_html($current_language); ?></code></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e('Available Languages:', 'skallar'); ?></th>
					<td>
						<?php esc_html_e('Portuguese (Brazil), English (United States), Spanish (Spain), French (France), German (Germany), Korean (South Korea), Danish (Denmark), Arabic, Dutch (Netherlands), Hungarian (Hungary), Hindi (India), Indonesian (Indonesia), Italian (Italy), Japanese (Japan), Norwegian (Norway), Polish (Poland), Portuguese (Portugal), Swedish (Sweden), Turkish (Turkey), Filipino (Philippines), Finnish (Finland), Thai (Thailand), Greek (Greece), Czech (Czech Republic), Luxembourgish (Luxembourg), Romanian (Romania), Serbian (Serbia), Bulgarian (Bulgaria)', 'skallar'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e('Translation Files:', 'skallar'); ?></th>
					<td>
						<?php
						$languages = [
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
							'bg_BG'
						];
						foreach ($languages as $lang) {
							$file_path = get_template_directory() . '/languages/' . $lang . '.po';
							$status = file_exists($file_path) ?
								'<span style="color: green;">✓ ' . esc_html__('Available', 'skallar') . '</span>' :
								'<span style="color: red;">✗ ' . esc_html__('Missing', 'skallar') . '</span>';
							echo '<strong>' . esc_html($lang) . ':</strong> ' . $status . '<br>';
						}
						?>
					</td>
				</tr>
			</table>

			<hr>

			<h2><?php esc_html_e('Instructions', 'skallar'); ?></h2>
			<div style="background: #f1f1f1; padding: 15px; border-radius: 4px;">
				<ul>
					<li><?php esc_html_e('Use the dropdown above to select your preferred language', 'skallar'); ?></li>
					<li><?php esc_html_e('Or use the quick buttons for instant language switching', 'skallar'); ?></li>
					<li><?php esc_html_e('The change affects both the admin panel and front-end of your site', 'skallar'); ?></li>
					<li><?php esc_html_e('All theme text will be displayed in the selected language', 'skallar'); ?></li>
					<li><?php esc_html_e('Use the reset button if you encounter any language-related issues', 'skallar'); ?></li>
				</ul>
			</div>
		</div>
<?php
	}

	/**
	 * Retorna o nome do idioma
	 */
	private function get_language_name($language_code)
	{
		$languages = [
			'pt_BR' => __('Portuguese (Brazil)', 'skallar'),
			'en_US' => __('English (United States)', 'skallar'),
			'es_ES' => __('Spanish (Spain)', 'skallar'),
			'fr_FR' => __('French (France)', 'skallar'),
			'de_DE' => __('German (Germany)', 'skallar'),
			'ko_KR' => __('Korean (South Korea)', 'skallar'),
			'da_DK' => __('Danish (Denmark)', 'skallar'),
			'ar' => __('Arabic', 'skallar'),
			'nl_NL' => __('Dutch (Netherlands)', 'skallar'),
			'hu_HU' => __('Hungarian (Hungary)', 'skallar'),
			'hi_IN' => __('Hindi (India)', 'skallar'),
			'id_ID' => __('Indonesian (Indonesia)', 'skallar'),
			'it_IT' => __('Italian (Italy)', 'skallar'),
			'ja' => __('Japanese (Japan)', 'skallar'),
			'nb_NO' => __('Norwegian (Norway)', 'skallar'),
			'pl_PL' => __('Polish (Poland)', 'skallar'),
			'pt_PT' => __('Portuguese (Portugal)', 'skallar'),
			'sv_SE' => __('Swedish (Sweden)', 'skallar'),
			'tr_TR' => __('Turkish (Turkey)', 'skallar'),
			'fil' => __('Filipino (Philippines)', 'skallar'),
			'fi' => __('Finnish (Finland)', 'skallar'),
			'th' => __('Thai (Thailand)', 'skallar'),
			'el' => __('Greek (Greece)', 'skallar'),
			'cs_CZ' => __('Czech (Czech Republic)', 'skallar'),
			'lb_LU' => __('Luxembourgish (Luxembourg)', 'skallar'),
			'ro_RO' => __('Romanian (Romania)', 'skallar'),
			'sr_RS' => __('Serbian (Serbia)', 'skallar'),
			'bg_BG' => __('Bulgarian (Bulgaria)', 'skallar'),
		];

		return isset($languages[$language_code]) ? $languages[$language_code] : $language_code;
	}

	/**
	 * Sanitiza campos de checkbox
	 */
	public function sanitize_checkbox($input)
	{
		return $input == 1 ? 1 : 0;
	}

	/**
	 * Sanitiza campos de cor
	 */
	public function sanitize_color($input)
	{
		// Verifica se é uma cor válida em formato hexadecimal
		if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $input)) {
			return $input;
		}
		return '';
	}

	/**
	 * Sanitiza configurações de idioma
	 */
	public function sanitize_language($input)
	{
		$allowed_languages = [
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
			'bg_BG'
		];
		return in_array($input, $allowed_languages) ? $input : 'pt_BR';
	}

	/**
	 * Sanitiza campos de URL
	 */
	public function sanitize_url($input)
	{
		return esc_url_raw($input);
	}

	public function sanitize_home_categories($input)
	{
		if (!is_array($input)) {
			return array();
		}
		$valid_ids = get_terms([
			'taxonomy' => 'category',
			'fields' => 'ids',
			'hide_empty' => false
		]);

		return array_values(array_intersect(array_map('absint', $input), $valid_ids));
	}

	/**
	 * Carrega o textdomain do tema para internacionalização
	 */
	public function load_textdomain()
	{
		// Verifica se há uma configuração de idioma personalizada
		$custom_language = get_option('skallar_site_language', '');

		if (!empty($custom_language)) {
			// Aplica o idioma personalizado
			add_filter('locale', function ($locale) use ($custom_language) {
				return $custom_language;
			});
		}

		load_theme_textdomain('skallar', get_template_directory() . '/languages');
	}

	/**
	 * Aplicar idioma personalizado em todo o site
	 */
	public function apply_custom_language($locale)
	{
		$custom_language = get_option('skallar_site_language', '');
		return !empty($custom_language) ? $custom_language : $locale;
	}

	/**
	 * Limita o excerpt a 150 caracteres ou mantém o texto original se for menor
	 * @param string $excerpt O texto do excerpt
	 * @param int $limit Limite de caracteres (padrão: 150)
	 * @return string O excerpt limitado
	 */
	public static function limit_excerpt($excerpt, $limit = 150)
	{
		// Remove tags HTML se houver
		$excerpt = strip_tags($excerpt);

		// Se o texto for menor ou igual ao limite, retorna como está
		if (strlen($excerpt) <= $limit) {
			return $excerpt;
		}

		// Limita ao número de caracteres e encontra o último espaço
		$limited = substr($excerpt, 0, $limit);
		$last_space = strrpos($limited, ' ');

		// Se encontrou um espaço, corta na palavra completa
		if ($last_space !== false) {
			$limited = substr($limited, 0, $last_space);
		}

		return $limited . '...';
	}

	/**
	 * Carrega CSS crítico inline e CSS principal de forma assíncrona para melhor performance
	 */
	public function load_critical_css()
	{
		// Carrega CSS crítico inline
		$critical_css_path = get_template_directory() . '/assets/styles/Critical/critical.min.css';

		if (file_exists($critical_css_path)) {
			echo '<style id="critical-css">' . file_get_contents($critical_css_path) . '</style>';
		}

		// Carrega CSS principal de forma assíncrona
		$main_css_url = get_template_directory_uri() . '/base/styles/style.css';
		echo '<link rel="preload" href="' . esc_url($main_css_url) . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';
		echo '<noscript><link rel="stylesheet" href="' . esc_url($main_css_url) . '"></noscript>';
	}

	/**
	 * Remove o CSS principal do carregamento normal se estamos usando CSS crítico
	 */
	public function remove_default_styles()
	{
		// Remove o CSS padrão se existir
		wp_dequeue_style('theme-style');
		wp_deregister_style('theme-style');
	}

	/**
	 * Gera CSS customizado baseado nas cores escolhidas e logo
	 */
	public function output_custom_colors()
	{
		$primary_color = get_option('skallar_primary_color', '#fd5e04');
		$secondary_color = get_option('skallar_secondary_color', '#fef2e6');
		$text_color = get_option('skallar_text_color', '#1d2433');
		$background_color = get_option('skallar_background_color', '#f8f9fc');
		$success_color = get_option('skallar_success_color', '#08875d');
		$danger_color = get_option('skallar_danger_color', '#e02d3c');
		$use_text_logo = get_option('skallar_use_text_logo', 0);

		// Só gera CSS se pelo menos uma cor foi customizada ou se usa logo de texto
		if (
			$primary_color !== '#fd5e04' || $secondary_color !== '#fef2e6' || $text_color !== '#1d2433' ||
			$background_color !== '#f8f9fc' || $success_color !== '#08875d' || $danger_color !== '#e02d3c' || $use_text_logo
		) {

			echo '<style type="text/css">';
			echo ':root {';

			if ($primary_color !== '#fd5e04') {
				echo '--skl-color-primary-600: ' . esc_attr($primary_color) . ';';
				// Gera variações mais escuras e claras baseadas na cor principal
				echo '--skl-color-primary-700: ' . esc_attr($this->darken_color($primary_color, 20)) . ';';
				echo '--skl-color-primary-800: ' . esc_attr($this->darken_color($primary_color, 40)) . ';';
			}

			if ($secondary_color !== '#fef2e6') {
				echo '--skl-color-primary-100: ' . esc_attr($secondary_color) . ';';
			}

			if ($text_color !== '#1d2433') {
				echo '--skl-color-black: ' . esc_attr($text_color) . ';';
			}

			if ($background_color !== '#f8f9fc') {
				echo '--skl-color-neutral-100: ' . esc_attr($background_color) . ';';
			}

			if ($success_color !== '#08875d') {
				echo '--skl-color-success-700: ' . esc_attr($success_color) . ';';
			}

			if ($danger_color !== '#e02d3c') {
				echo '--skl-color-danger-700: ' . esc_attr($danger_color) . ';';
			}

			echo '}';

			// CSS para logo de texto
			if ($use_text_logo) {
				echo '.skl-logo-text {';
				echo 'font-size: 1.5rem;';
				echo 'font-weight: bold;';
				echo 'color: var(--skl-color-primary-600, ' . esc_attr($primary_color) . ');';
				echo 'text-decoration: none;';
				echo 'font-family: inherit;';
				echo '}';

				echo '.skl-logo-text:hover {';
				echo 'color: var(--skl-color-primary-700, ' . esc_attr($this->darken_color($primary_color, 20)) . ');';
				echo '}';
			}

			// CSS para logo de imagem
			echo '.skl-logo-image {';
			echo 'max-height: 60px;';
			echo 'width: auto;';
			echo 'display: block;';
			echo '}';

			// CSS para menu do header bottom - alinhamento inline para desktop
			echo '@media (min-width: 768px) {';
			echo '.skl-header__bottom-container {';
			echo 'display: flex;';
			echo 'justify-content: center;';
			echo 'align-items: center;';
			echo 'gap: 1rem;';
			echo 'flex-wrap: nowrap;';
			echo '}';
			echo '.skl-header__bottom-menu {';
			echo 'display: flex;';
			echo 'align-items: center;';
			echo 'gap: 1rem;';
			echo 'list-style: none;';
			echo 'margin: 0;';
			echo 'padding: 0;';
			echo '}';
			echo '.skl-header__bottom-menu li {';
			echo 'margin: 0;';
			echo 'padding: 0;';
			echo '}';
			echo '}';

			// CSS para limitar excerpt do news-card
			echo '.skl-news-card__excerpt {';
			echo 'display: -webkit-box;';
			echo '-webkit-line-clamp: 2;';
			echo '-webkit-box-orient: vertical;';
			echo 'overflow: hidden;';
			echo 'text-overflow: ellipsis;';
			echo 'max-height: 3em;'; /* aproximadamente 2 linhas */
			echo 'line-height: 1.5;';
			echo '}';

			echo '</style>';
		} else {
			// Mesmo se as cores não foram customizadas, precisamos do CSS do menu
			echo '<style type="text/css">';

			// CSS para menu do header bottom - alinhamento inline para desktop
			echo '@media (min-width: 768px) {';
			echo '.skl-header__bottom-container {';
			echo 'display: flex;';
			echo 'justify-content: center;';
			echo 'align-items: center;';
			echo 'gap: 1rem;';
			echo 'flex-wrap: nowrap;';
			echo '}';
			echo '.skl-header__bottom-menu {';
			echo 'display: flex;';
			echo 'align-items: center;';
			echo 'gap: 1rem;';
			echo 'list-style: none;';
			echo 'margin: 0;';
			echo 'padding: 0;';
			echo '}';
			echo '.skl-header__bottom-menu li {';
			echo 'margin: 0;';
			echo 'padding: 0;';
			echo '}';
			echo '}';

			echo '</style>';
		}
	}

	/**
	 * Função auxiliar para escurecer uma cor
	 */
	private function darken_color($color, $percent)
	{
		$color = str_replace('#', '', $color);

		if (strlen($color) == 3) {
			$color = str_repeat(substr($color, 0, 1), 2) . str_repeat(substr($color, 1, 1), 2) . str_repeat(substr($color, 2, 1), 2);
		}

		$r = hexdec(substr($color, 0, 2));
		$g = hexdec(substr($color, 2, 2));
		$b = hexdec(substr($color, 4, 2));

		$r = max(0, min(255, $r - ($r * $percent / 100)));
		$g = max(0, min(255, $g - ($g * $percent / 100)));
		$b = max(0, min(255, $b - ($b * $percent / 100)));

		return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
	}

	/**
	 * Verifica se as cotações da bolsa devem ser exibidas
	 */
	public static function show_stock_quotes()
	{
		return get_option('show_stock_quotes', 1) == 1;
	}

	/**
	 * Debug: Adiciona aviso no admin se o menu não aparecer
	 */
	public function admin_notices()
	{
		$screen = get_current_screen();
		if ($screen && $screen->base === 'plugins') {
			echo '<div class="notice notice-info"><p><strong>' . esc_html__('Skallar Theme:', 'skallar') . '</strong> ' . 
			     sprintf(
			         esc_html__('Settings available at %s in the sidebar menu.', 'skallar'),
			         '<a href="' . admin_url('admin.php?page=skallar-theme') . '">' . esc_html__('Skallar Theme', 'skallar') . '</a>'
			     ) . '</p></div>';
		}
	}

	/**
	 * Função para obter o logo configurado
	 */
	public static function get_logo()
	{
		$use_text_logo = get_option('skallar_use_text_logo', 0);

		if ($use_text_logo) {
			// Usar logo de texto
			$logo_text = get_option('skallar_logo_text', get_bloginfo('name'));
			return [
				'type' => 'text',
				'content' => $logo_text
			];
		} else {
			// Usar logo de imagem
			$logo_url = get_option('skallar_logo_url', get_template_directory_uri() . '/base/images/Logo.png');
			return [
				'type' => 'image',
				'content' => $logo_url
			];
		}
    }

    /**
     * Registra o meta box "Ocultar título" para todos os post types públicos
     */
    public function register_hide_title_metabox() {
        $post_types = get_post_types(['public' => true], 'names');

        foreach ($post_types as $post_type) {
            add_meta_box(
                'skl_hide_title_box',
                __('Opções de Título', 'skallar'),
                [$this, 'render_hide_title_metabox'],
                $post_type,
                'side',
                'high'
            );
        }
    }

    /**
     * Render do meta box
     */
    public function render_hide_title_metabox($post) {
        wp_nonce_field('skl_hide_title_nonce', 'skl_hide_title_nonce_field');
        $value = get_post_meta($post->ID, '_skl_hide_title', true);
        ?>
        <p>
            <label>
                <input type="checkbox" name="skl_hide_title" value="1" <?php checked($value, '1'); ?> />
                <?php esc_html_e('Ocultar título deste conteúdo', 'skallar'); ?>
            </label>
        </p>
        <?php
    }
    
    /**
     * Salva o meta quando o post é atualizado
     */
    public function save_hide_title_meta($post_id) {
        // Segurança básica
        if (!isset($_POST['skl_hide_title_nonce_field']) ||
            !wp_verify_nonce($_POST['skl_hide_title_nonce_field'], 'skl_hide_title_nonce')) {
            return;
        }

        // Permissão
        $post_type = get_post_type($post_id);
        $cap = ($post_type === 'page') ? 'edit_page' : 'edit_post';
        if (!current_user_can($cap, $post_id)) return;

        // Salva/Remove
        if (isset($_POST['skl_hide_title']) && $_POST['skl_hide_title'] === '1') {
            update_post_meta($post_id, '_skl_hide_title', '1');
        } else {
            delete_post_meta($post_id, '_skl_hide_title');
        }
    }

    /**
     * Adiciona a classe "skl-hide-title" na <article> se o título estiver oculto
     */
    public function add_hide_title_post_class($classes, $class, $post_id) {
        if (get_post_meta($post_id, '_skl_hide_title', true) === '1') {
            $classes[] = 'skl-hide-title';
        }
        return $classes;
    }
}
