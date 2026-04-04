# Architecture

This document explains the implementation choices behind Portal AM24h and the tradeoffs made for performance, maintainability, and predictable WordPress behavior.

## System Overview

Theme boot flow:
1. `functions.php` includes `includes/Core/Bootstrap.php`.
2. `Am24h_Bootstrap` instantiates module objects and shared services.
3. Each module exposes `register_hooks()` and only attaches the hooks it owns.

The goal is explicit wiring and low coupling between concerns.

## Asset Loading Strategy

### CSS
- Critical stylesheet source: `assets/styles/Critical/critical.min.css`.
- Critical CSS is inlined early in `wp_head` by `Am24h_CriticalCss::render_inline_critical_css()`.
- Main stylesheet source: `assets/styles/style.css`.
- Main stylesheet is enqueued by `Am24h_HeadStyles::enqueue_main_stylesheet()`.

### JavaScript
- JavaScript is kept as small feature-specific files:
  - `assets/js/search-bar.js`
  - `assets/js/cookie-consent.js`
  - `assets/js/accessibility-popup.js`
- Scripts are enqueued with defer strategy when supported.
- No jQuery dependency is declared for these scripts.

### URL/version handling
- `Am24h_Assets` removes query-string asset version parameters from script/style URLs.
- Physical file mtime-based versioning still exists internally through `Am24h_AssetLocator` where needed.

## Critical CSS Approach

`Am24h_CriticalCss` is intentionally conservative:
- Reads only the critical CSS file from the theme.
- Enforces a maximum inline payload size (`MAX_INLINE_CSS_BYTES`).
- Skips output if file is missing, unreadable, empty, or too large.
- Sanitizes inline CSS output before rendering in `<style id="critical-css">`.

Design intent:
- Keep first paint stable and fast.
- Avoid bloated inline payloads.
- Keep critical and non-critical layers separate so they can evolve independently.

## Template Modularity

Template composition uses `get_template_part()` with small reusable units under `template-parts/`.

Examples:
- Card rendering (`template-parts/news-card.php`) reused in index/archive/search sections.
- Section and pagination partials extracted from page-level templates.
- Header/footer/home partial groups separated into dedicated folders.

Benefits:
- Lower duplication across template files.
- Safer refactors with localized rendering changes.
- Clear separation between content queries and presentational fragments.

## Avoidance Of Unnecessary Dependencies

The theme intentionally avoids heavy frontend/runtime dependencies:
- No external UI framework.
- No external JS runtime library requirement for core interactions.
- Font delivery defaults to local hosting through the typography subsystem.

This reduces network variability and dependency churn, and makes production behavior easier to reason about.

## Maintainability Decisions

### 1) Responsibility-oriented folders
`includes/` is split by domain (`Core`, `Performance`, `Typography`, `Admin`, `Front`, `Content`, `Support`) to keep class scopes clear.

### 2) Options and sanitization boundaries
Input sanitation is centralized in `includes/Admin/SettingsSanitizer.php` instead of being scattered across rendering logic.

### 3) Feature toggles over hardcoded behavior
Performance cleanup and optional UI features are controlled through theme options and filters.

### 4) Progressive enhancement mindset
Optional frontend features (cookie banner, accessibility popup, font preload/deferred visual overrides) are layered, not required for baseline rendering.

### 5) Tooling for consistency
`composer.json` and `phpcs.xml.dist` define code quality checks aligned with WordPress standards.

## Known Tradeoffs

- Aggressive cleanup options can conflict with plugin expectations on some installations.
- Custom locale switching depends on availability of matching translation files at runtime.
- Critical CSS requires manual discipline to keep scope and size under control.

## Extension Guidelines

When extending the theme:
1. Add new behavior as a module under the most relevant `includes/*` domain.
2. Keep side effects behind `register_hooks()`.
3. Sanitize on write, escape on render.
4. Prefer template-part composition over adding complexity to top-level templates.
5. Treat performance options as opt-in policies and document their effects.
