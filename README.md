# Portal AM24h

Portal AM24h is a custom, performance-oriented WordPress theme for editorial/news workloads.

It is intentionally dependency-light, built around WordPress-native APIs, and organized as small domain modules instead of a large all-in-one theme file.

## Project Profile

This repository is designed to read like a senior-level engineering theme project:

- Modular PHP architecture with clear ownership by domain.
- Conservative performance decisions that are measurable and testable.
- Admin tooling with explicit sanitization and capability checks.
- Third-party script loading designed around risk separation (worker-friendly vs main-thread).
- Vanilla JavaScript interactions with no jQuery or frontend framework dependency.

## Core Features

- Modular bootstrap in [includes/Core/Bootstrap.php](includes/Core/Bootstrap.php).
- Critical CSS inlining with file-size guardrails.
- Main stylesheet orchestration separated from critical CSS.
- Optional cleanup controls for WordPress head and block-style behavior.
- Local typography pipeline (validation, download, storage, registry, generated @font-face).
- Optional cookie consent banner.
- Optional accessibility popup with persisted user preferences.
- Single-post share bar with local icon tooling.
- Third-party script management with worker-friendly path and main-thread fallback path.
- Theme language selection with allowlisted locale handling.

## Architecture At A Glance

Runtime flow:

1. [functions.php](functions.php) loads the bootstrap.
2. [includes/Core/Bootstrap.php](includes/Core/Bootstrap.php) wires services and modules.
3. Each module registers WordPress hooks via register_hooks().

Main module groups:

- Core: boot lifecycle, setup, assets.
- Performance: critical CSS, head styles, cleanup.
- Admin: settings registration, sanitization, admin UI.
- Front: cookie banner, accessibility popup, custom CSS, third-party scripts.
- Typography: local font management pipeline.
- Content: content-specific behavior helpers.
- Support: shared utilities and repositories.

See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) for detailed boundaries and extension guidelines.

## Performance Strategy

The theme favors predictable render behavior over aggressive tricks:

- Inline only a bounded critical CSS payload early in wp_head.
- Load non-critical CSS as separate assets.
- Keep frontend JS small and feature-scoped.
- Load core block styles on demand by default.
- Keep expensive cleanup toggles opt-in and documented.
- Use local static assets for deterministic deploy behavior.

No benchmark claims are published in this repository. Validate with your own lab and field measurements.

See [docs/PERFORMANCE.md](docs/PERFORMANCE.md).

## Third-Party Scripts And Partytown

Third-party scripts are split into two explicit groups:

- Worker-friendly scripts: analytics, trackers, pixels, tag managers.
- Main-thread scripts: integrations that require synchronous DOM/browser access.

Partytown is used conservatively:

- It loads only when worker-friendly scripts exist and local assets are available.
- Worker scripts are not treated as universally safe by default.
- Missing Partytown assets trigger a safe fallback to main-thread loading.

See [docs/THIRD_PARTY_SCRIPTS.md](docs/THIRD_PARTY_SCRIPTS.md).

## Accessibility, Cookie Banner, Share Bar, Typography

- Accessibility popup: optional, keyboard-aware, ARIA-aware controls with localStorage persistence. See [docs/ACCESSIBILITY.md](docs/ACCESSIBILITY.md).
- Cookie banner: optional and lightweight, suited for baseline notice workflows.
- Share bar: configurable networks/order/icons, with local SVG handling.
- Typography: local font validation/download and @font-face generation pipeline.

## Why Vanilla JavaScript Here

This theme intentionally stays on vanilla JavaScript instead of jQuery or frontend libraries.

Practical benefits in this codebase:

- Lower payload and no framework runtime overhead.
- Fewer dependencies to patch, upgrade, and audit.
- Simpler long-term maintenance in WordPress theme environments.
- Better control over execution timing around render-critical paths.
- Fewer compatibility surprises from plugin/theme script interactions.
- Easier code auditing for performance and security-sensitive projects.

## WordPress Integration Notes

- Text domain: am24h.
- Theme translations loaded from languages via load_theme_textdomain().
- Theme locale override is intentionally limited to frontend requests.
- Admin pages and settings use WordPress Settings API patterns.

## Development

Requirements:

- WordPress-compatible PHP runtime.
- Composer for coding standards tooling.

Commands:

```bash
composer install
composer run lint:phpcs
composer run lint:phpcs-full
composer run lint:phpcbf
```

See [docs/DEVELOPMENT_NOTES.md](docs/DEVELOPMENT_NOTES.md).

## Installation

1. Put the theme in wp-content/themes/portal-am24h.
2. Activate it in WordPress admin.
3. Configure options under the Portal Am24h menu.
4. If using local typography features, configure fonts through the provided admin screen.

## Distribution

Release packaging is controlled with .distignore to keep development-only files out of ZIP builds.

## License

GPL-2.0-or-later.
