# Performance Strategy

Portal AM24h uses explicit, conservative performance controls designed for maintainability in production WordPress sites.

## Goals

- Improve first render reliability.
- Keep JavaScript execution predictable.
- Avoid broad global behavior that can break plugin/theme interoperability.
- Keep optimization controls understandable by site operators.

## CSS Loading Model

- Critical CSS is read from assets/styles/Critical/critical.min.css and inlined early in wp_head.
- Inline payload is bounded by a max byte guard to prevent accidental head bloat.
- Main stylesheet is loaded separately.

This preserves a stable first-paint path while keeping larger style layers cacheable.

## JavaScript Model

- Frontend behavior is split into small, feature-scoped vanilla JS files.
- Scripts are deferred where appropriate.
- No jQuery dependency is introduced.
- Third-party scripts are isolated behind dedicated worker/main-thread settings.

## WordPress Cleanup Controls

Cleanup options can remove unnecessary default head output and adjust block-style behavior.

Important:

- These settings are optional and reversible.
- Aggressive cleanup can affect plugin assumptions.
- Validate content/plugin pages after enabling advanced cleanup toggles.

## Asset Version Query Behavior

Theme asset query-string removal is scoped to theme assets only.

Reason:

- Avoid mutating plugin asset URLs globally.
- Preserve theme URL cleanliness preferences without increasing cross-plugin risk.

## Third-Party Script Performance

- Worker-friendly scripts are preferred for analytics/tracking workloads.
- Main-thread scripts remain available for DOM-bound integrations.
- Missing Partytown assets trigger controlled fallback behavior.

See [THIRD_PARTY_SCRIPTS.md](THIRD_PARTY_SCRIPTS.md).

## Vanilla JS Rationale

Vanilla JS is a performance and maintenance decision in this theme:

- Smaller payload.
- Fewer moving parts in production.
- Better timing control around render-critical paths.
- Easier debugging and code auditing.

## Measurement Guidance

This repository intentionally avoids publishing synthetic benchmark claims.

Evaluate with your own toolchain and workloads:

1. Lighthouse or WebPageTest for lab baselines.
2. Real-user monitoring for production behavior.
3. Regression checks after enabling cleanup or third-party script changes.
