(function () {
    'use strict';

    const STORAGE_KEY = 'am24hAccessibilityPrefs';
    const FONT_BASE_PERCENT = 62.5;
    const FONT_STEP_PERCENT = 4;
    const FONT_MIN_STEP = -1;
    const FONT_MAX_STEP = 2;

    function defaultState() {
        return {
            fontStep: 0,
            lineHeight: false,
            letterSpacing: false,
            readableFont: false,
            readingMode: false,
            readingGuide: false,
            readingMask: false,
            highlightLinks: false,
            highlightHeadings: false,
            hideImages: false,
            pauseAnimations: false,
            highContrast: false,
            reducedSaturation: false,
            grayscale: false
        };
    }

    function clampFontStep(value) {
        const numeric = Number.parseInt(String(value), 10);

        if (Number.isNaN(numeric)) {
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
        const base = defaultState();

        if (!input || typeof input !== 'object') {
            return base;
        }

        base.fontStep = clampFontStep(input.fontStep);
        base.lineHeight = Boolean(input.lineHeight);
        base.letterSpacing = Boolean(input.letterSpacing);
        base.readableFont = Boolean(input.readableFont);
        base.readingMode = Boolean(input.readingMode);
        base.readingGuide = Boolean(input.readingGuide);
        base.readingMask = Boolean(input.readingMask);
        base.highlightLinks = Boolean(input.highlightLinks);
        base.highlightHeadings = Boolean(input.highlightHeadings);
        base.hideImages = Boolean(input.hideImages);
        base.pauseAnimations = Boolean(input.pauseAnimations);
        base.highContrast = Boolean(input.highContrast);
        base.reducedSaturation = Boolean(input.reducedSaturation);
        base.grayscale = Boolean(input.grayscale);

        return base;
    }

    function loadState() {
        try {
            const raw = globalThis.localStorage.getItem(STORAGE_KEY);

            if (!raw) {
                return defaultState();
            }

            return normalizeState(JSON.parse(raw));
        } catch {
            return defaultState();
        }
    }

    function saveState(state) {
        try {
            globalThis.localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
        } catch {
            // Continue without persistence when storage is blocked.
        }
    }

    function parseEnabledTools(container) {
        const raw = container.getAttribute('data-a11y-enabled-tools');

        if (!raw) {
            return null;
        }

        try {
            const parsed = JSON.parse(raw);

            if (!Array.isArray(parsed)) {
                return null;
            }

            return new Set(parsed.map((item) => String(item)));
        } catch {
            return null;
        }
    }

    function isToolEnabled(enabledTools, key) {
        if (!enabledTools) {
            return true;
        }

        return enabledTools.has(key);
    }

    function applyState(state) {
        const body = document.body;
        const html = document.documentElement;

        if (!body || !html) {
            return;
        }

        const htmlFontSize = FONT_BASE_PERCENT + (state.fontStep * FONT_STEP_PERCENT);

        html.style.fontSize = `${htmlFontSize}%`;
        body.dataset.am24hA11yFontStep = String(state.fontStep);

        body.classList.toggle('am24h-a11y-line-height', state.lineHeight);
        body.classList.toggle('am24h-a11y-letter-spacing', state.letterSpacing);
        body.classList.toggle('am24h-a11y-readable-font', state.readableFont);
        body.classList.toggle('am24h-a11y-reading-mode', state.readingMode);
        body.classList.toggle('am24h-a11y-reading-guide', state.readingGuide);
        body.classList.toggle('am24h-a11y-reading-mask', state.readingMask);
        body.classList.toggle('am24h-a11y-highlight-links', state.highlightLinks);
        body.classList.toggle('am24h-a11y-highlight-headings', state.highlightHeadings);
        body.classList.toggle('am24h-a11y-hide-images', state.hideImages);
        body.classList.toggle('am24h-a11y-pause-animations', state.pauseAnimations);
        body.classList.toggle('am24h-a11y-high-contrast', state.highContrast);
        body.classList.toggle('am24h-a11y-reduced-saturation', state.reducedSaturation);
        body.classList.toggle('am24h-a11y-grayscale', state.grayscale);
    }

    function updateToggleButtons(container, state) {
        const toggleButtons = container.querySelectorAll('[data-a11y-toggle]');
        const pressedMap = {
            'line-height': state.lineHeight,
            'letter-spacing': state.letterSpacing,
            'readable-font': state.readableFont,
            'reading-mode': state.readingMode,
            'reading-guide': state.readingGuide,
            'reading-mask': state.readingMask,
            'highlight-links': state.highlightLinks,
            'highlight-headings': state.highlightHeadings,
            'hide-images': state.hideImages,
            'pause-animations': state.pauseAnimations,
            'high-contrast': state.highContrast,
            'reduced-saturation': state.reducedSaturation,
            grayscale: state.grayscale
        };

        toggleButtons.forEach((button) => {
            const toggleKey = button.dataset.a11yToggle;
            const pressed = Boolean(toggleKey && pressedMap[toggleKey]);

            button.setAttribute('aria-pressed', pressed ? 'true' : 'false');
            button.classList.toggle('is-active', pressed);
        });
    }

    function setupReadingHelpers() {
        const body = document.body;

        if (!body) {
            return {
                updatePosition: function () {}
            };
        }

        const guide = document.createElement('div');

        guide.className = 'am24h-a11y-reading-guide-line';
        guide.setAttribute('aria-hidden', 'true');
        body.appendChild(guide);

        const maskTop = document.createElement('div');
        const maskBottom = document.createElement('div');

        maskTop.className = 'am24h-a11y-reading-mask-top';
        maskBottom.className = 'am24h-a11y-reading-mask-bottom';
        maskTop.setAttribute('aria-hidden', 'true');
        maskBottom.setAttribute('aria-hidden', 'true');
        body.appendChild(maskTop);
        body.appendChild(maskBottom);

        function updatePosition(y) {
            const vertical = Math.max(0, y || 0);

            guide.style.setProperty('--am24h-reading-guide-y', `${vertical}px`);
            body.style.setProperty('--am24h-reading-mask-y', `${vertical}px`);
        }

        updatePosition(globalThis.innerHeight / 2);

        return {
            updatePosition
        };
    }

    function initAccessibilityPopup() {
        const trigger = document.querySelector('[data-accessibility-open]');
        const container = document.querySelector('[data-accessibility-popup]');

        if (!trigger || !container) {
            return;
        }

        trigger.hidden = false;

        const enabledTools = parseEnabledTools(container);
        const dialog = container.querySelector('.am24h-accessibility-popup__dialog');
        const closeControls = container.querySelectorAll('[data-accessibility-close]');
        const readingHelpers = setupReadingHelpers();
        const hasReadingOverlayTools = isToolEnabled(enabledTools, 'reading_guide') || isToolEnabled(enabledTools, 'reading_mask');
        const focusableSelector = 'button,[href],input,select,textarea,[tabindex]:not([tabindex="-1"])';
        let previousFocus = null;
        let state = loadState();
        let pointerFrameRequested = false;
        let latestPointerY = globalThis.innerHeight / 2;

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

        function toggleFlag(key, toolKey) {
            if (!isToolEnabled(enabledTools, toolKey)) {
                return;
            }

            state[key] = !state[key];
            commitState();
        }

        function handleAction(action) {
            if (action === 'increase-font' && isToolEnabled(enabledTools, 'font_size')) {
                state.fontStep = clampFontStep(state.fontStep + 1);
                commitState();
                return;
            }

            if (action === 'decrease-font' && isToolEnabled(enabledTools, 'font_size')) {
                state.fontStep = clampFontStep(state.fontStep - 1);
                commitState();
                return;
            }

            if (action === 'reset-font' && isToolEnabled(enabledTools, 'font_size')) {
                state.fontStep = 0;
                commitState();
                return;
            }

            if (action === 'toggle-line-height') {
                toggleFlag('lineHeight', 'line_height');
                return;
            }

            if (action === 'toggle-letter-spacing') {
                toggleFlag('letterSpacing', 'letter_spacing');
                return;
            }

            if (action === 'toggle-readable-font') {
                toggleFlag('readableFont', 'readable_font');
                return;
            }

            if (action === 'toggle-reading-mode') {
                toggleFlag('readingMode', 'reading_mode');
                return;
            }

            if (action === 'toggle-reading-guide') {
                toggleFlag('readingGuide', 'reading_guide');
                return;
            }

            if (action === 'toggle-reading-mask') {
                toggleFlag('readingMask', 'reading_mask');
                return;
            }

            if (action === 'toggle-highlight-links') {
                toggleFlag('highlightLinks', 'highlight_links');
                return;
            }

            if (action === 'toggle-highlight-headings') {
                toggleFlag('highlightHeadings', 'highlight_headings');
                return;
            }

            if (action === 'toggle-hide-images') {
                toggleFlag('hideImages', 'hide_images');
                return;
            }

            if (action === 'toggle-pause-animations') {
                toggleFlag('pauseAnimations', 'pause_animations');
                return;
            }

            if (action === 'toggle-high-contrast') {
                toggleFlag('highContrast', 'high_contrast');
                return;
            }

            if (action === 'toggle-reduced-saturation') {
                toggleFlag('reducedSaturation', 'reduced_saturation');
                return;
            }

            if (action === 'toggle-grayscale') {
                toggleFlag('grayscale', 'grayscale');
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

        trigger.addEventListener('click', function () {
            if (container.hidden) {
                openPopup();
                return;
            }

            closePopup();
        });

        closeControls.forEach((control) => {
            control.addEventListener('click', closePopup);
        });

        container.addEventListener('click', (event) => {
            const actionTarget = event.target.closest('[data-a11y-action]');

            if (!actionTarget) {
                return;
            }

            const action = actionTarget.dataset.a11yAction;

            if (!action) {
                return;
            }

            handleAction(action);
        });

        if (hasReadingOverlayTools) {
            document.addEventListener('mousemove', (event) => {
                latestPointerY = event.clientY;

                if (pointerFrameRequested) {
                    return;
                }

                pointerFrameRequested = true;
                globalThis.requestAnimationFrame(() => {
                    pointerFrameRequested = false;
                    readingHelpers.updatePosition(latestPointerY);
                });
            });
        }

        document.addEventListener('focusin', (event) => {
            if (!hasReadingOverlayTools) {
                return;
            }

            if (!(event.target instanceof HTMLElement)) {
                return;
            }

            const rect = event.target.getBoundingClientRect();

            readingHelpers.updatePosition(rect.top + (rect.height / 2));
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !container.hidden) {
                closePopup();
                return;
            }

            if (event.key !== 'Tab' || container.hidden || !dialog) {
                return;
            }

            const focusable = dialog.querySelectorAll(focusableSelector);

            if (!focusable.length) {
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
                return;
            }

            if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    }

    if (document.readyState === 'complete') {
        initAccessibilityPopup();
        return;
    }

    globalThis.addEventListener('load', initAccessibilityPopup, { once: true });
})();
