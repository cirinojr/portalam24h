(function () {
    'use strict';

    function setCookie(name, value, maxAge) {
        if (!name) {
            return;
        }

        var cookie = encodeURIComponent(name) + '=' + encodeURIComponent(value) + '; path=/; SameSite=Lax; max-age=' + String(maxAge);
        if (window.location.protocol === 'https:') {
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
        var banner = document.getElementById('am24h-cookie-consent');

        if (!banner) {
            return;
        }

        var cookieName = banner.getAttribute('data-cookie-name') || 'am24h_cookie_consent';
        var cookieMaxAge = parseInt(banner.getAttribute('data-cookie-max-age') || '31536000', 10);

        if (!cookieMaxAge || cookieMaxAge < 1) {
            cookieMaxAge = 31536000;
        }

        banner.addEventListener('click', function (event) {
            var target = event.target;

            if (!(target instanceof HTMLElement)) {
                return;
            }

            var action = target.getAttribute('data-consent-action');

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
