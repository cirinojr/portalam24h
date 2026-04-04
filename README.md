# Portal AM24h

High-performance editorial WordPress theme with modular PHP architecture, critical CSS delivery, local font management, and template-driven rendering.

## Why This Project Matters
This repository demonstrates practical senior-level theme engineering skills: performance-driven rendering decisions, modular backend architecture, security-first option handling, localization workflow, and maintainable WordPress integration without frontend framework lock-in.

## Overview
Portal AM24h is built for editorial/news workloads where predictable performance and long-term maintainability are priorities.

The theme avoids broad frontend dependencies and keeps runtime behavior explicit:
- Bootstrapped through focused modules instead of one monolithic `functions.php` implementation.
- Layered stylesheet strategy with early critical CSS and deferred non-critical layers.
- Local font pipeline (validation, download, registry, CSS generation) to avoid external font CDNs.
- Modular template parts for archive/home/single composition.

## Key Technical Decisions
1. Module-based bootstrapping
The theme is initialized in `functions.php` through `Am24h_Bootstrap`, which wires classes by domain and registers hooks per module.

2. Performance-first style loading
Critical CSS (`assets/styles/Critical/critical.min.css`) is inlined in `wp_head`, while the main stylesheet is enqueued separately.

3. Local assets and vanilla JS
Frontend interactions (search bar, cookie consent, accessibility popup) are implemented with small vanilla JS files and no jQuery dependency.

4. Typed, focused PHP classes
Domain-specific classes in `includes/` keep responsibilities narrow and reduce coupling.

5. Configurable cleanup behavior
WordPress head output and block-style loading are controlled with options and filters rather than hardcoded assumptions.

## Architecture
Runtime flow:
1. `functions.php` loads `includes/Core/Bootstrap.php`.
2. `Am24h_Bootstrap` instantiates module classes and shared services.
3. Each module registers its own WordPress hooks through `register_hooks()`.

Core module groups:
- `Core`: setup, assets, bootstrap lifecycle.
- `Performance`: critical CSS, head stylesheet orchestration, optional WordPress cleanup.
- `Typography`: local font validation/storage/download/registry and generated `@font-face` output.
- `Admin`: settings registration, sanitization, admin UI.
- `Front`: optional cookie consent and accessibility popup rendering.
- `Content`: content behavior helpers (excerpt and featured image logic).
- `Support`: utility services and shared helpers.

## Directory Structure
```text
.
|- assets/
|  |- font/
|  |- images/
|  |- js/
|  `- styles/
|- includes/
|  |- Admin/
|  |- Content/
|  |- Core/
|  |- Front/
|  |- Performance/
|  |- Support/
|  `- Typography/
|- languages/
|- template-parts/
|- functions.php
|- style.css
`- ...WordPress template files
```

Repository tree explanation:
- `assets/`: static frontend resources (CSS, JS, images, theme-bundled font assets).
- `includes/`: PHP source organized by feature area and runtime responsibility.
- `template-parts/`: reusable rendering blocks used by templates (`index.php`, `single.php`, `archive.php`, etc.).
- `languages/`: translation catalog files (`.pot` and localized `.po` files).

## Performance Strategy
- Inline critical CSS early (`wp_head` priority 1) with a hard byte limit guard.
- Enqueue non-critical stylesheet separately to keep initial render path explicit.
- Remove version query parameters from script/style URLs.
- Keep JavaScript small and deferred when possible.
- Use local WOFF2 preload only when configured and valid.
- Keep block styles on-demand by default; optional aggressive dequeue remains opt-in.

Notes:
- No benchmark claims are included in this repository; validate with your own field/lab measurements.
- Preload and cleanup settings are context-dependent and should be tested per site.

## Accessibility Considerations
- Optional accessibility popup with keyboard-operable controls and persisted user preferences.
- ARIA labels, `aria-pressed`, focus management, and Escape-to-close behavior in popup interactions.
- Preference controls avoid replacing semantic markup responsibilities.
- Theme architecture supports accessible defaults but does not replace content-level accessibility work.

## Security And Sanitization Approach
- Admin options are sanitized through dedicated methods in `includes/Admin/SettingsSanitizer.php`.
- Theme output uses WordPress escaping helpers (`esc_html`, `esc_attr`, `esc_url`).
- Font ingestion validates extension/MIME/source before storage.
- Cookie and option reads are normalized/sanitized before use.
- Local font hosting reduces third-party runtime dependencies.

## Internationalization
- Text domain: `am24h`.
- Theme translations loaded from `languages/` via `load_theme_textdomain`.
- Locale switching can be controlled through theme options with catalog allowlisting.
- Source catalog (`am24h.pot`) and language `.po` files are included in-repo.

## Development Workflow
Requirements:
- PHP compatible with current WordPress runtime.
- Composer for developer tooling.

Commands:
```bash
composer install
composer run lint:phpcs
composer run lint:phpcs-full
composer run lint:phpcbf
```

Workflow recommendations:
1. Keep new logic in focused modules under `includes/`.
2. Prefer template-part composition over large template files.
3. Keep critical CSS small and intentionally scoped.
4. Validate sanitization and escaping for every new option/setting.
5. Run PHPCS checks before opening pull requests.

## Installation
1. Place the theme in `wp-content/themes/portal-am24h`.
2. Activate it from WordPress admin.
3. Configure theme options under the AM24h settings pages.
4. If using local font features, upload/activate fonts through the provided admin controls.

## License
Recommended for WordPress distribution: `GPL-2.0-or-later`.

This repository now includes a GPL-compatible `LICENSE` file. If you bundle additional third-party assets, verify they are GPL-compatible before distribution.

## Suggested Future Improvements
1. Add automated tests for option sanitization and font pipeline behavior.
2. Add CI workflow for PHPCS and PHP compatibility checks.
3. Add static analysis (for example PHPStan/Psalm) with a baseline policy.
4. Add scripted translation build flow (`.po` -> `.mo`) for release packaging.
5. Add documented browser support matrix and accessibility test checklist.
