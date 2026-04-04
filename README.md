# Portal AM24h: High-Performance Editorial WordPress Theme

## Overview
Portal AM24h is a custom WordPress theme for editorial and news publishing. It is built with a performance-conscious frontend, a modular PHP architecture, and a maintainability-first code organization for long-term project ownership.

## Key Characteristics
- Modular codebase split by responsibility (Core, Admin, Performance, Typography, Content, Support).
- Minimal frontend footprint with controlled asset output.
- Layered CSS delivery with clear separation of critical and non-critical styling.
- Self-hosted font workflow with local files only (WOFF2 preferred).
- Predictable first render based on stable fallback typography.
- Optional Gutenberg cleanup strategy with on-demand core block style loading.
- Deterministic hook registration and no monolithic theme controller.

## Architecture
The theme boots through a single entrypoint in `functions.php`, which loads `includes/Core/Bootstrap.php`. Bootstrap wires focused modules, and each module registers only its own hooks.

Responsibilities are intentionally separated: setup and assets in Core, settings in Admin, rendering behavior in Performance and Front, font management in Typography, and shared utilities in Support. This keeps classes small, testable, and readable.

## Performance Strategy
- Critical CSS is inlined for the initial render path.
- The main stylesheet is loaded separately through standard enqueue flow.
- Font preload is optional and limited to a primary local WOFF2 file.
- `font-display: swap` is used for custom font rendering behavior.
- Custom fonts are not embedded into critical CSS.
- Initial render uses a stable fallback stack (`Arial, system-ui, sans-serif`).
- Custom font styles are applied after load as a non-critical enhancement layer.

Preload is treated as an optimization layer, not a guaranteed improvement. Its value depends on page structure, connection profile, and real measurements.

## Typography Strategy
- Fonts are installed locally from approved sources and served from uploads.
- Runtime font conversion is intentionally not part of the theme.
- WOFF2 is the preferred production format.
- WOFF can be used as a secondary fallback when necessary.
- TTF and OTF are not recommended for this theme's production delivery strategy.
- Font files are stored under uploads, not inside the theme directory.
- Fallback system fonts are used during initial render to preserve stability.

## Gutenberg Strategy
The theme does not blindly remove core block styles by default. It supports WordPress-native on-demand loading for core block assets to reduce unnecessary CSS on pages that do not render specific blocks.

An advanced full-disable cleanup option is available for tightly controlled environments, but it should only be used when block styling is fully covered by the theme.

## Cookie Consent Banner
- The theme includes an optional LGPD/GDPR-style cookie consent banner.
- It is disabled by default.
- It is configurable from the theme options panel (message, labels, policy link, position, and style variant).
- It supports multiple positions (top/bottom full width and floating bottom layouts).
- It is a lightweight consent notice with local state persistence, not a full legal compliance platform.

## Accessibility Popup
- The theme includes an optional accessibility adjustments popup.
- It is disabled by default.
- It is implemented with PHP, vanilla JavaScript, and CSS only (no external libraries).
- It provides lightweight visual controls (font size, contrast, reading background, and link highlighting) with user preference persistence.
- It is not a substitute for accessible design, semantic markup, or proper content structure.

## Security and Stability
- Local asset hosting avoids runtime dependency on third-party font CDNs.
- Font writes are restricted to the WordPress uploads directory.
- Input is sanitized on write; dynamic output is escaped on render.
- File handling uses explicit validation for extension, MIME type, and source URL.
- Architecture is intentionally restrained to reduce failure surface.

## Performance References
- Critical rendering path and critical CSS: https://web.dev/learn/performance/understanding-the-critical-path
- Preload: https://web.dev/articles/preload-critical-assets
- Web font optimization: https://web.dev/articles/font-best-practices
- Render-blocking resources: https://web.dev/articles/render-blocking-resources
- Largest Contentful Paint (LCP): https://web.dev/articles/optimize-lcp

## Installation
1. Copy the theme directory to `wp-content/themes/`.
2. Activate the theme in WordPress Admin.
3. Configure theme options (including typography) under the AM24h admin pages.

## Notes
This is a custom theme built for controlled editorial environments. It is maintained as a project-specific codebase, not as a generic marketplace product.
