/**
 * UTM Parameter Passer
 * Captures UTM parameters from the URL and appends them to all links on the page
 * Compatível com navegadores antigos, incluindo IE11+
 */

(function () {
    // Wait for the DOM to be fully loaded
    function ready(fn) {
        if (
            document.attachEvent
                ? document.readyState === "complete"
                : document.readyState !== "loading"
        ) {
            fn();
        } else {
            document.addEventListener("DOMContentLoaded", fn);
        }
    }

    // Helper function to parse URL parameters
    function getUrlParams() {
        var match,
            pl = /\+/g, // Regex for replacing addition symbol with a space
            search = /([^&=]+)=?([^&]*)/g,
            decode = function (s) {
                return decodeURIComponent(s.replace(pl, " "));
            },
            query = window.location.search.substring(1);

        var urlParams = {};
        while ((match = search.exec(query))) {
            if (match[1].indexOf("utm_") === 0) {
                urlParams[decode(match[1])] = decode(match[2]);
            }
        }
        return urlParams;
    }

    // Helper function to check if URL is external
    function isExternal(url) {
        var tmp = document.createElement("a");
        tmp.href = url;
        return tmp.hostname && tmp.hostname !== window.location.hostname;
    }

    // Helper function to add parameters to URL
    function addParamsToUrl(url, params) {
        if (!params || Object.keys(params).length === 0) return url;

        var urlParts = url.split("?");
        var baseUrl = urlParts[0];
        var queryString = urlParts[1] || "";

        // Parse existing query string
        var queryParams = {};
        if (queryString) {
            queryString.split("&").forEach(function (param) {
                var pair = param.split("=");
                if (pair[0]) {
                    queryParams[decodeURIComponent(pair[0])] = pair[1]
                        ? decodeURIComponent(pair[1].replace(/\+/g, " "))
                        : "";
                }
            });
        }

        // Add UTM params
        for (var key in params) {
            if (
                params.hasOwnProperty(key) &&
                !queryParams.hasOwnProperty(key)
            ) {
                queryParams[key] = params[key];
            }
        }

        // Rebuild query string
        var newQueryString = Object.keys(queryParams)
            .map(function (key) {
                return (
                    encodeURIComponent(key) +
                    "=" +
                    encodeURIComponent(queryParams[key])
                );
            })
            .join("&");

        return baseUrl + (newQueryString ? "?" + newQueryString : "");
    }

    // Main function to process links
    function processLinks(utmParams) {
        var links = document.getElementsByTagName("a");

        for (var i = 0; i < links.length; i++) {
            var link = links[i];
            var href = link.getAttribute("href");

            if (
                !href ||
                href.indexOf("#") === 0 ||
                href.indexOf("javascript:") === 0 ||
                href.indexOf("mailto:") === 0 ||
                href.indexOf("tel:") === 0
            ) {
                continue;
            }

            // Skip external links
            if (isExternal(href)) {
                continue;
            }

            // Update the href with UTM parameters (will handle both URLs with and without existing parameters)
            link.href = addParamsToUrl(href, utmParams);
        }
    }

    // Initialize
    ready(function () {
        var utmParams = getUrlParams();

        if (Object.keys(utmParams).length > 0) {
            // Process existing links
            processLinks(utmParams);

            // Set up MutationObserver for dynamic content if supported
            if (typeof MutationObserver !== "undefined") {
                var observer = new MutationObserver(function () {
                    processLinks(utmParams);
                });

                observer.observe(document.body, {
                    childList: true,
                    subtree: true,
                });
            }
        }
    });
})();
