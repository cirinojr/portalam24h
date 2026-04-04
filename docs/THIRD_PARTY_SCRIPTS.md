# Third-Party Scripts

Portal AM24h provides a controlled third-party script pipeline for theme owners who need analytics/marketing integrations without turning every script into a main-thread dependency.

## Two Script Groups

The admin UI separates scripts into:

- Worker-friendly scripts (recommended default path).
- Main-thread scripts (exception path).

This separation is intentional and should remain explicit in project review.

## Worker-Friendly Scripts

Use for scripts that mainly collect and forward events:

- Google Analytics / GA4
- Google Tag Manager loader
- Pixels and telemetry tags
- Other measurement scripts with limited DOM coupling

Worker mode runs through Partytown when assets are present.

### Forward Keys

Forward keys are used when the integration expects specific globals/functions (for example dataLayer.push). Keep this list minimal and explicit.

## Main-Thread Scripts

Use when integration behavior requires immediate page-context execution:

- Chat widgets
- Form/embed runtimes
- Synchronous DOM mutation scripts
- Integrations that rely on direct event cancellation or early rendering hooks

### Strategy Guidance

- Prefer defer for deterministic order after HTML parsing.
- Use async only when execution order is irrelevant.
- If inline initialization is required, the theme intentionally avoids async/defer for that entry to prevent race conditions.

## Partytown Integration Model

Runtime behavior:

1. If no worker-friendly scripts are enabled, Partytown is not loaded.
2. If worker-friendly scripts exist and local Partytown assets exist, worker script tags are emitted.
3. If assets are missing, worker entries fall back to main-thread async loading and an admin warning is shown.

This model is conservative by design and avoids silent failure.

## Important Limitations

- Worker mode is not a universal compatibility layer.
- A script that manipulates DOM heavily may fail or behave differently in worker mode.
- Always test integration behavior after moving a script between groups.
- Do not duplicate the same URL in both groups.

## Operational Checklist

Before release:

1. Verify enabled script URLs are unique and valid.
2. Confirm each script is in the correct group.
3. Validate event delivery in production-like conditions.
4. Confirm fallback behavior when Partytown assets are removed.

## Partytown Asset Maintenance

Keep local Partytown files committed under assets/vendor/partytown so deploys do not depend on package-manager build steps.

Update command:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/update-partytown.ps1
```

Specific version:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/update-partytown.ps1 -Version 0.10.3
```

Post-update checks:

1. assets/vendor/partytown/partytown.js exists.
2. Worker entries load without admin warnings.
3. Analytics events still fire.
