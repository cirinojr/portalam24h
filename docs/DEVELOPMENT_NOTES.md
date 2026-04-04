# Development Notes

This document captures practical conventions for extending and maintaining Portal AM24h.

## Local Workflow

1. Install tooling:

```bash
composer install
```

2. Run coding standards checks:

```bash
composer run lint:phpcs
composer run lint:phpcs-full
composer run lint:phpcbf
```

## Engineering Conventions

- Keep modules small and domain-scoped under includes/.
- Register hooks inside register_hooks() methods.
- Sanitize on write, escape on output.
- Prefer explicit option defaults and normalization on read.
- Avoid global side effects unless strictly necessary.

## JavaScript Conventions

- Use vanilla JavaScript only.
- Prefer event delegation for dynamic or repeated controls.
- Avoid hidden framework patterns in plain JS files.
- Keep DOM queries and state updates explicit and small.

## CSS Conventions

- Keep component CSS in assets/styles/Components.
- Keep selector specificity predictable.
- Use !important only for accessibility/user-override contexts where necessary.
- Avoid wide-reaching selectors for feature-specific components.

## Admin Safety

- Validate capabilities for all custom admin actions.
- Use nonces for state-changing actions.
- Keep sanitization methods centralized in SettingsSanitizer.
- Avoid updating global WordPress options from theme-only actions unless unavoidable.

## Third-Party Scripts

- Default to worker-friendly entries for analytics/tracking.
- Keep main-thread entries minimal and justified.
- Document integration assumptions and required forwarding keys.
- Test each integration after changing mode or strategy.

## Translation Notes

- Text domain is am24h.
- Keep strings translation-ready in PHP and templates.
- Ensure release artifacts include the language files required by your locale setup.

## Release Notes

Before packaging:

1. Confirm .distignore excludes development and local artifacts.
2. Confirm Partytown assets exist if worker scripts are enabled.
3. Validate admin pages after settings/sanitizer changes.
4. Smoke test single post, archive, search, and homepage templates.
