(function () {
    'use strict';

    function scriptGroups() {
        return Array.prototype.slice.call(document.querySelectorAll('.am24h-script-group[data-group]'));
    }

    function rowFields(row) {
        return Array.prototype.slice.call(row.querySelectorAll('[data-field]'));
    }

    function indexGroup(group) {
        var groupName = group.getAttribute('data-group');
        var rows = Array.prototype.slice.call(group.querySelectorAll('[data-script-row]'));

        rows.forEach(function (row, index) {
            rowFields(row).forEach(function (field) {
                var key = field.getAttribute('data-field');

                if (!key) {
                    return;
                }

                if (key === 'enabled_hidden') {
                    field.name = groupName + '[' + index + '][enabled]';
                    return;
                }

                field.name = groupName + '[' + index + '][' + key + ']';
            });
        });
    }

    function indexAllGroups() {
        scriptGroups().forEach(indexGroup);
    }

    function addRow(button) {
        var group = button.closest('.am24h-script-group');

        if (!group) {
            return;
        }

        var rowsContainer = group.querySelector('[data-rows]');
        var template = group.querySelector('template[data-template]');

        if (!rowsContainer || !template || !template.content.firstElementChild) {
            return;
        }

        rowsContainer.appendChild(template.content.firstElementChild.cloneNode(true));
        indexGroup(group);
    }

    function removeRow(button) {
        var row = button.closest('[data-script-row]');

        if (!row) {
            return;
        }

        var group = row.closest('.am24h-script-group');
        row.remove();

        if (group) {
            indexGroup(group);
        }
    }

    function createPreset(group, preset) {
        var template = group.querySelector('template[data-template]');

        if (!template || !template.content.firstElementChild) {
            return null;
        }

        var row = template.content.firstElementChild.cloneNode(true);

        if (preset === 'ga4') {
            row.querySelector('[data-field="label"]').value = 'Google Analytics 4';
            row.querySelector('[data-field="url"]').value = 'https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX';
            row.querySelector('[data-field="inline"]').value = [
                'window.dataLayer = window.dataLayer || [];',
                'function gtag(){dataLayer.push(arguments);}',
                'gtag("js", new Date());',
                'gtag("config", "G-XXXXXXXXXX");'
            ].join('\n');
            row.querySelector('[data-field="forward"]').value = 'dataLayer.push gtag';
        }

        if (preset === 'gtm') {
            row.querySelector('[data-field="label"]').value = 'Google Tag Manager';
            row.querySelector('[data-field="url"]').value = 'https://www.googletagmanager.com/gtm.js?id=GTM-XXXXXXX';
            row.querySelector('[data-field="inline"]').value = [
                'window.dataLayer = window.dataLayer || [];',
                'window.dataLayer.push({"gtm.start": new Date().getTime(), event: "gtm.js"});'
            ].join('\n');
            row.querySelector('[data-field="forward"]').value = 'dataLayer.push';
        }

        return row;
    }

    function addPreset(button) {
        var group = button.closest('.am24h-script-group');
        var presetName = button.getAttribute('data-add-preset');

        if (!group || !presetName) {
            return;
        }

        var rowsContainer = group.querySelector('[data-rows]');
        var row = createPreset(group, presetName);

        if (!rowsContainer || !row) {
            return;
        }

        rowsContainer.appendChild(row);
        indexGroup(group);
    }

    document.addEventListener('click', function (event) {
        var addButton = event.target.closest('[data-add-row]');

        if (addButton) {
            event.preventDefault();
            addRow(addButton);
            return;
        }

        var presetButton = event.target.closest('[data-add-preset]');

        if (presetButton) {
            event.preventDefault();
            addPreset(presetButton);
            return;
        }

        var removeButton = event.target.closest('[data-remove-row]');

        if (removeButton) {
            event.preventDefault();
            removeRow(removeButton);
        }
    });

    // Delegation keeps listener count stable as rows are added/removed.
    // This avoids per-row handlers, observers, and timers for predictable admin performance.
    document.addEventListener('submit', function (event) {
        if (!event.target || event.target.id !== 'am24h-third-party-form') {
            return;
        }

        indexAllGroups();
    });

    indexAllGroups();
})();
