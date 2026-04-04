(function () {
    'use strict';

    function setCookie(name, value, maxAge) {
        if (!name) {
            return;
        }

        let cookie = `${encodeURIComponent(name)}=${encodeURIComponent(value)}; path=/; SameSite=Lax; max-age=${String(maxAge)}`;
        if (globalThis.location.protocol === 'https:') {
            cookie += '; Secure';
        }

        document.cookie = cookie;
    }

    function dismissBanner(banner) {
        if (!banner) {
            return;
        }

        banner.setAttribute('hidden', 'hidden');
    }

    function init() {
        const banner = document.getElementById('am24h-cookie-consent');

        if (!banner) {
            return;
        }

        const cookieName = banner.dataset.cookieName || 'am24h_cookie_consent';
        let cookieMaxAge = Number.parseInt(banner.dataset.cookieMaxAge || '31536000', 10);

        if (!cookieMaxAge || cookieMaxAge < 1) {
            cookieMaxAge = 31536000;
        }

        banner.addEventListener('click', (event) => {
            const target = event.target;

            if (!(target instanceof HTMLElement)) {
                return;
            }

            const action = target.dataset.consentAction;

            if (!action) {
                return;
            }

            if (action !== 'accepted' && action !== 'rejected' && action !== 'closed') {
                return;
            }

            setCookie(cookieName, action, cookieMaxAge);
            dismissBanner(banner);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
        return;
    }

    init();
})();
