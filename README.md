# Skallar Digital Theme
Skallar Digital is a WordPress theme engineered for editorial and news-driven sites that need configurable branding, language flexibility, and responsive navigation.
The codebase emphasizes reusable template parts, critical CSS delivery, and admin tooling so newsroom teams can iterate quickly without custom plugins.

## Key Features
- Theme options for logo mode, stock quote ticker, and home category curation via the Skallar Theme menu with General, Colors, and Language tabs ([includes/ThemeOptions.php](includes/ThemeOptions.php)).
- Inline critical CSS plus async-preloaded main stylesheet to reduce render-blocking payload ([includes/ThemeOptions.php](includes/ThemeOptions.php), [assets/styles/Critical/critical.min.css](assets/styles/Critical/critical.min.css), [base/styles/style.css](base/styles/style.css)).
- Responsive header with custom navigation walker, search overlay, and mobile drawer governed by vanilla JS ([header.php](header.php), [base/scripts/search-bar.js](base/scripts/search-bar.js)).
- Optional block and global style dequeuing behind the SKALLAR_DISABLE_BLOCK_STYLES constant and filter for lean front-end payloads ([includes/ThemeOptions.php](includes/ThemeOptions.php)).
- Structured card and pagination components reused across archive, search, and home sections ([template-parts/news-card.php](template-parts/news-card.php), [template-parts/pagination.php](template-parts/pagination.php), [template-parts/section-posts.php](template-parts/section-posts.php)).
- Meta box to hide titles per post type, custom excerpt trimming, and headline-first single layout helpers ([includes/ThemeOptions.php](includes/ThemeOptions.php), [single.php](single.php)).
- UTM propagation utility that keeps campaign tags across internal navigation without third-party dependencies ([assets/js/utm-passer.js](assets/js/utm-passer.js)).

## Performance Philosophy
- Scripts are enqueued with file modification timestamps for cache busting while keeping browser caches warm ([includes/ThemeOptions.php](includes/ThemeOptions.php)).
- Critical CSS is inlined early, the primary stylesheet is preloaded and converted to a stylesheet post-load, and the fallback noscript tag preserves coverage ([includes/ThemeOptions.php](includes/ThemeOptions.php)).
- Default theme styles and legacy head tags are optionally removed to avoid duplicate CSS and unnecessary markup ([includes/ThemeOptions.php](includes/ThemeOptions.php)).
- Global WP styles are dequeued only when SKALLAR_DISABLE_BLOCK_STYLES or the skallar_disable_block_styles filter is enabled, allowing precise payload control ([includes/ThemeOptions.php](includes/ThemeOptions.php)).

### How to verify performance
- Run Lighthouse in Chrome DevTools on key templates and record First Contentful Paint, Largest Contentful Paint, and Total Blocking Time.
- Schedule WebPageTest runs against archive and single URLs to validate repeat-visit caching with the versioned assets.
- Monitor Core Web Vitals through Search Console or CrUX to ensure real-user metrics stay within recommended thresholds.

## Tech Stack / Requirements
- Tested on WordPress 6.4 and newer; validate in your target hosting stack if you require older versions.
- Requires PHP 7.4+; confirm compatibility when deploying to managed hosts.
- Relies solely on core WordPress APIs and vanilla JavaScript—no bundler or front-end framework is required.

## Installation
1. Clone or download this repository into wp-content/themes/skl-theme or zip the folder and upload it via Appearance → Themes → Add New.
2. Activate “Skallar Digital” from the WordPress Themes screen.
3. Assign the Header Menu and Footer Menu under Appearance → Menus to surface navigation.

## Configuration / Theme Options
- Navigate to Skallar Theme in the admin sidebar to open the General, Colors, and Language panels ([includes/ThemeOptions.php](includes/ThemeOptions.php)).
- General: toggle text or image logo, set logo URL or label, and enable the stock quotes banner shown in the header.
- Categories: select multiple categories to feature on the home layout; selections are sanitized against existing taxonomy terms.
- Colors: override primary, secondary, text, background, success, and danger palette entries, with a one-click reset to defaults.
- Language: switch the front-end and back-end locale, quick-switch among common locales, and reset to Portuguese (Brazil) if needed.
- Define SKALLAR_DISABLE_BLOCK_STYLES in wp-config.php or hook into the skallar_disable_block_styles filter to remove block and classic styles when the project does not rely on them.
- Each public post type receives a “Ocultar título” meta box so editors can suppress the rendered title when the design requires custom hero treatments.

## Development
- Templates: archive, single, page, and search templates reside at the project root ([archive.php](archive.php), [single.php](single.php), [search.php](search.php), [page.php](page.php)).
- Components: shared markup lives under template-parts, including cards, pagination, and sections ([template-parts](template-parts)).
- Theme logic and admin integrations stay inside includes/ThemeOptions.php; bootstrap is handled by functions.php ([functions.php](functions.php), [includes/ThemeOptions.php](includes/ThemeOptions.php)).
- Front-end assets are kept unminified for easy overrides in assets/ and base/ directories; no build step is required.
- Critical CSS should be updated in assets/styles/Critical/critical.min.css to keep above-the-fold render tight.

## Security & Best Practices
- Settings use WordPress option APIs with sanitize callbacks for checkboxes, colors, URLs, and taxonomy IDs ([includes/ThemeOptions.php](includes/ThemeOptions.php)).
- Front-end templates escape dynamic output with esc_html, esc_url, and wp_kses_post, and pagination relies on paginate_links to avoid manual link construction ([template-parts/news-card.php](template-parts/news-card.php), [template-parts/pagination.php](template-parts/pagination.php)).
- Nonces protect meta box saves, and capability checks gate write access to options screens ([includes/ThemeOptions.php](includes/ThemeOptions.php)).

## Accessibility & i18n
- Text domain skallar is loaded on after_setup_theme, and translation files for 25+ locales are supplied under languages/ along with the skallar.pot template ([includes/ThemeOptions.php](includes/ThemeOptions.php), [languages](languages)).
- Templates provide semantic landmarks and accessible pagination aria labels, while the search form uses role="search" to aid assistive technology ([template-parts/pagination.php](template-parts/pagination.php), [searchform.php](searchform.php)).
- Mobile navigation toggles rely on standard buttons with keyboard handlers and Escape key support implemented in the search-bar.js controller ([base/scripts/search-bar.js](base/scripts/search-bar.js)).

## Used in Production
- portalam24h.com (verify deployment before publishing)
- Update this list with verified deployments.

## License + Credits
- No LICENSE file is present. Add the appropriate license text for your organization before distributing the theme.
- Based on design assets shipped in the base/ directory; ensure you have rights to redistribute bundled fonts and imagery before release.
