# Security Model

Portal AM24h follows a defensive, WordPress-native security model focused on predictable output and strict input handling.

## Principles

- Sanitize on write.
- Validate and normalize on read.
- Escape on output.
- Minimize trust boundaries and keep third-party behavior explicit.

## Input Handling

Settings data is sanitized in [includes/Admin/SettingsSanitizer.php](../includes/Admin/SettingsSanitizer.php).

Key controls:

- Strict allowlists for option enums and toggles.
- URL sanitization restricted to http/https for external resources.
- Inline script snippets stripped from script tags and bounded in size.
- Share/network order and third-party keys constrained to known patterns.

## Output Safety

Template and UI output uses context-appropriate escaping:

- esc_html, esc_attr, esc_url for plain output contexts.
- wp_kses_post for controlled HTML returned by trusted WordPress helpers.

This pattern is used across frontend templates and admin settings screens.

## Admin Action Protection

Administrative mutations require capability and nonce checks.

Examples:

- current_user_can('manage_options') on privileged operations.
- wp_verify_nonce checks on state-changing admin actions.

## Third-Party Script Controls

Third-party scripts are split into explicit pipelines:

- Worker-friendly scripts (Partytown path).
- Main-thread scripts (fallback and compatibility path).

Security-relevant controls:

- URL normalization and scheme/host validation.
- Duplicate URL suppression.
- Controlled strategy selection (async/defer) by allowlist.

## File System and Network Safety

For local file writes (for example SVG/icon or font flows), the theme uses WordPress filesystem APIs and normalized path checks to avoid path traversal.

Remote fetch behavior is constrained to validated URLs and then sanitized before persistence.

## Operational Guidance

Before production rollout:

1. Keep WordPress core/plugins updated.
2. Restrict admin access to trusted users.
3. Test cleanup/performance toggles against critical plugins.
4. Re-run static checks (PHPCS) before deploy.

## Responsible Disclosure

If you identify a security issue, do not open a public issue with exploit details.

Share a private report with:

- Reproduction steps.
- Affected versions/commit.
- Impact and mitigation suggestion.
