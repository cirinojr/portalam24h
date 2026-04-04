# Architecture

Portal AM24h uses a module-first theme architecture focused on predictable behavior in production WordPress installs.

## Boot Lifecycle

1. [functions.php](../functions.php) loads [includes/Core/Bootstrap.php](../includes/Core/Bootstrap.php).
2. Bootstrap instantiates shared services and feature modules.
3. Each module registers only its own hooks through register_hooks().

This keeps side effects explicit and reduces accidental cross-module coupling.

## Domain Layout

- Core: boot sequence, theme setup, frontend asset wiring.
- Performance: critical CSS, stylesheet strategy, cleanup policies.
- Admin: settings registration, sanitization, admin pages/actions.
- Front: cookie banner, accessibility popup, third-party scripts, custom CSS.
- Typography: local font validation/downloading/storage/face generation.
- Content: excerpt and featured-image behavior helpers.
- Support: utility classes and shared repositories.

## Key Boundaries

### 1. Options and Sanitization

- Settings registration lives in [includes/Admin/SettingsRegistrar.php](../includes/Admin/SettingsRegistrar.php).
- Sanitization lives in [includes/Admin/SettingsSanitizer.php](../includes/Admin/SettingsSanitizer.php).
- Option reads are centralized in [includes/Support/ThemeOptionsRepository.php](../includes/Support/ThemeOptionsRepository.php).

Sanitize on write, normalize on read, escape on output.

### 2. Asset Responsibility

- [includes/Core/Assets.php](../includes/Core/Assets.php) handles theme JS enqueue and theme-scoped URL filtering.
- [includes/Performance/CriticalCss.php](../includes/Performance/CriticalCss.php) inlines bounded critical CSS.
- [includes/Performance/HeadStyles.php](../includes/Performance/HeadStyles.php) controls non-critical stylesheet loading.

Asset URL filtering is intentionally scoped to theme handles/theme URLs to avoid mutating plugin assets globally.

### 3. Locale Behavior

- [includes/Support/Localization.php](../includes/Support/Localization.php) loads the theme text domain and applies theme language overrides.
- Locale override is frontend-only and allowlisted through the language catalog.

### 4. Third-Party Script Pipeline

- Settings UI lives in [includes/Admin/ThemeSettingsPage.php](../includes/Admin/ThemeSettingsPage.php).
- Runtime rendering/enqueue lives in [includes/Front/ThirdPartyScripts.php](../includes/Front/ThirdPartyScripts.php).
- Worker-friendly scripts are preferred; main-thread scripts remain available when required.

## Performance Model

- Keep critical CSS small, static, and bounded.
- Keep frontend JS feature-scoped and dependency-light.
- Prefer defer for non-critical script execution.
- Keep WordPress cleanup settings configurable and reversible.

## Extension Rules

1. Add new behavior to the smallest relevant module.
2. Register hooks only inside register_hooks().
3. Keep templates dumb: rendering and light branching only.
4. Do not introduce jQuery or frontend frameworks for small interaction needs.
5. Keep option names and defaults stable once released.
6. Document any behavior that can affect plugin interoperability.

## Known Tradeoffs

- Aggressive cleanup settings can conflict with plugin assumptions.
- Partytown can improve script isolation but cannot make DOM-heavy integrations worker-safe.
- Locale switching depends on available translation files in deploy artifacts.
