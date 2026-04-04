(function () {
    'use strict';

    var STORAGE_KEY = 'am24hAccessibilityPrefs';
    var FONT_BASE_PERCENT = 62.5;
    var FONT_STEP_PERCENT = 4;
    var FONT_MIN_STEP = -1;
    var FONT_MAX_STEP = 2;

    function defaultState() {
        return {
            fontStep: 0,
            highContrast: false,
            readingBg: false,
            highlightLinks: false
        };
    }

    function clampFontStep(value) {
        var numeric = parseInt(String(value), 10);

        if (isNaN(numeric)) {
            return 0;
        }

        if (numeric < FONT_MIN_STEP) {
            return FONT_MIN_STEP;
        }

        if (numeric > FONT_MAX_STEP) {
            return FONT_MAX_STEP;
        }

        return numeric;
    }

    function normalizeState(input) {
        var base = defaultState();

        if (!input || typeof input !== 'object') {
            return base;
        }

        base.fontStep = clampFontStep(input.fontStep);
        base.highContrast = Boolean(input.highContrast);
        base.readingBg = Boolean(input.readingBg);
        base.highlightLinks = Boolean(input.highlightLinks);

        return base;
    }

    function loadState() {
        try {
            var raw = window.localStorage.getItem(STORAGE_KEY);

            if (!raw) {
                return defaultState();
            }

            return normalizeState(JSON.parse(raw));
        } catch (error) {
            return defaultState();
        }
    }

    function saveState(state) {
        try {
            window.localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
        } catch (error) {
            // Keep behavior safe when storage is blocked.
        }
    }

    function applyState(state) {
        var body = document.body;
        var html = document.documentElement;

        if (!body || !html) {
            return;
        }

        body.setAttribute('data-am24h-a11y-font-step', String(state.fontStep));

        var htmlFontSize = FONT_BASE_PERCENT + (state.fontStep * FONT_STEP_PERCENT);
        html.style.fontSize = String(htmlFontSize) + '%';

        body.classList.toggle('am24h-a11y-high-contrast', state.highContrast);
        body.classList.toggle('am24h-a11y-reading-bg', state.readingBg);
        body.classList.toggle('am24h-a11y-highlight-links', state.highlightLinks);
    }

    function updateToggleButtons(container, state) {
        var toggleButtons = container.querySelectorAll('[data-a11y-toggle]');

        toggleButtons.forEach(function (button) {
            var toggleKey = button.getAttribute('data-a11y-toggle');
            var pressed = false;

            if (toggleKey === 'contrast') {
                pressed = state.highContrast;
            } else if (toggleKey === 'reading-bg') {
                pressed = state.readingBg;
            } else if (toggleKey === 'highlight-links') {
                pressed = state.highlightLinks;
            }

            button.setAttribute('aria-pressed', pressed ? 'true' : 'false');
        });
    }

    function initAccessibilityPopup() {
        var trigger = document.querySelector('[data-accessibility-open]');
        var container = document.querySelector('[data-accessibility-popup]');

        if (!trigger || !container) {
            return;
        }

        var dialog = container.querySelector('.am24h-accessibility-popup__dialog');
        var closeControls = container.querySelectorAll('[data-accessibility-close]');
        var actionButtons = container.querySelectorAll('[data-a11y-action]');
        var previousFocus = null;
        var state = loadState();

        applyState(state);
        updateToggleButtons(container, state);

        function commitState() {
            applyState(state);
            updateToggleButtons(container, state);
            saveState(state);
        }

        function resetState() {
            state = defaultState();
            commitState();
        }

        function handleAction(action) {
            if (action === 'increase-font') {
                state.fontStep = clampFontStep(state.fontStep + 1);
                commitState();
                return;
            }

            if (action === 'decrease-font') {
                state.fontStep = clampFontStep(state.fontStep - 1);
                commitState();
                return;
            }

            if (action === 'reset-font') {
                state.fontStep = 0;
                commitState();
                return;
            }

            if (action === 'toggle-contrast') {
                state.highContrast = !state.highContrast;

                if (state.highContrast) {
                    state.readingBg = false;
                }

                commitState();
                return;
            }

            if (action === 'toggle-reading-bg') {
                state.readingBg = !state.readingBg;

                if (state.readingBg) {
                    state.highContrast = false;
                }

                commitState();
                return;
            }

            if (action === 'toggle-highlight-links') {
                state.highlightLinks = !state.highlightLinks;
                commitState();
                return;
            }

            if (action === 'reset-all') {
                resetState();
            }
        }

        function openPopup() {
            previousFocus = document.activeElement;
            container.hidden = false;
            trigger.setAttribute('aria-expanded', 'true');
            if (dialog) {
                dialog.focus();
            }
        }

        function closePopup() {
            container.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
            if (previousFocus && typeof previousFocus.focus === 'function') {
                previousFocus.focus();
                return;
            }
            trigger.focus();
        }

        trigger.addEventListener('click', openPopup);

        closeControls.forEach(function (control) {
            control.addEventListener('click', closePopup);
        });

        actionButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var action = button.getAttribute('data-a11y-action');

                if (!action) {
                    return;
                }

                handleAction(action);
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !container.hidden) {
                closePopup();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAccessibilityPopup);
        return;
    }

    initAccessibilityPopup();
})();
