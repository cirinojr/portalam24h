# Accessibility

Portal AM24h includes an optional accessibility popup intended as a practical frontend aid, not a full accessibility compliance substitute.

## Scope

The popup provides user-level reading and contrast adjustments that can help with comfort and readability:

- Font size step controls
- Line-height and letter-spacing toggles
- Readable font toggle
- Reading guide and reading mask overlays
- Link/headline highlighting
- Image hiding
- Animation pause
- High contrast, reduced saturation, grayscale

These are assistive controls layered on top of semantic markup and content quality.

## Architectural Notes

- Server-side rendering and settings live in [includes/Front/AccessibilityPopup.php](../includes/Front/AccessibilityPopup.php).
- Runtime interaction logic lives in [assets/js/accessibility-popup.js](../assets/js/accessibility-popup.js).
- Styles live in [assets/styles/Components/accessibility-popup.css](../assets/styles/Components/accessibility-popup.css).
- Feature toggles are stored as theme options and exposed in admin.

## Keyboard And ARIA Behavior

- Launcher button exposes aria-expanded and aria-controls.
- Dialog uses role="dialog" and aria-modal="true".
- Escape closes the dialog.
- Focus is trapped inside the dialog while open.
- Toggle controls synchronize visual and aria-pressed state.

## Persistence

User preferences are persisted in localStorage.

- Storage key: am24hAccessibilityPrefs
- State is normalized on load.
- Invalid or partial state is safely coerced.

The feature continues to function without persistence if localStorage is blocked.

## Performance Considerations

- Event delegation is used for popup action handling to avoid one-listener-per-button growth.
- Pointer tracking for reading overlays is gated and throttled.
- No external libraries are required.

## CSS Override Policy

The component uses targeted !important declarations for accessibility overrides where selector conflicts are expected (for example line-height or contrast modes). Keep these declarations narrow and intentional.

## Content And UX Caveats

- This popup does not replace correct heading structure, alt text, labels, or keyboard-safe custom widgets.
- Avoid claiming legal compliance based on this feature alone.
- Test real content pages with keyboard and screen-reader workflows.

## Recommended QA Checklist

1. Keyboard-only navigation from launcher to close/reset controls.
2. Escape behavior from multiple focus contexts.
3. aria-pressed updates for all toggles.
4. Contrast and readability checks on long-form article pages.
5. Regression check with cookie banner and share bar enabled.
