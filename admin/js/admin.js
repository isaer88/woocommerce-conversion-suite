
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const toggles = document.querySelectorAll('.wcs-module-toggle');
        const notice = document.getElementById('wcs-dashboard-notice');
        const activeCount = document.getElementById('wcs-active-count');

        const cards = Array.from(document.querySelectorAll('.wcs-module-card[data-module-id]'));
        const search = document.getElementById('wcs-search');
        const filter = document.getElementById('wcs-filter');
        const empty = document.getElementById('wcs-no-results');
        const inactiveCount = document.getElementById('wcs-inactive-count');
        function refresh() {
            let active = 0, visible = 0;
            cards.forEach(function (card) {
                const enabled = !!card.querySelector('.wcs-module-status.is-active');
                if (enabled) active++;
                const name = card.querySelector('h2').textContent.toLowerCase();
                const matches = name.includes(search ? search.value.trim().toLowerCase() : '') &&
                    (!filter || filter.value === 'all' || (filter.value === 'active') === enabled);
                card.hidden = !matches;
                if (matches) visible++;
            });
            if (activeCount) activeCount.textContent = String(active);
            if (inactiveCount) inactiveCount.textContent = String(cards.length - active);
            if (empty) empty.hidden = visible !== 0;
        }
        if (search && filter) {
            search.closest('.wcs-toolbar').hidden = false;
            search.addEventListener('input', refresh);
            filter.addEventListener('change', refresh);
            refresh();
        }
        if (!toggles.length || typeof veltrixaAdmin === 'undefined') {
            return;
        }

        function showNotice(message, isError) {
            if (!notice) return;

            notice.textContent = message;
            notice.className = 'wcs-dashboard-notice ' +
                (isError ? 'is-error' : 'is-success');

            notice.hidden = false;
        }

        toggles.forEach(function (toggle) {
            toggle.addEventListener('change', async function () {
                const previousState = !toggle.checked;
                const requestedState = toggle.checked;
                const card = toggle.closest('.wcs-module-card');
                const status = card
                    ? card.querySelector('.wcs-module-status')
                    : null;

                toggle.disabled = true;

                if (notice) {
                    notice.hidden = true;
                }

                const data = new URLSearchParams();
                data.append('action', 'veltrixa_toggle_module');
                data.append('nonce', veltrixaAdmin.nonce);
                data.append('module_id', toggle.dataset.moduleId);
                data.append('enabled', requestedState ? '1' : '0');

                try {
                    const response = await fetch(veltrixaAdmin.ajaxUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                        },
                        body: data.toString()
                    });

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(
                            result.data && result.data.message
                                ? result.data.message
                                : veltrixaAdmin.error
                        );
                    }

                    const savedState = result.data.enabled === true;
                    toggle.checked = savedState;

                    if (status) {
                        status.textContent = savedState ? 'Active' : 'Inactive';
                        status.classList.toggle('is-active', savedState);
                        status.classList.toggle('is-inactive', !savedState);
                    }

                    refresh();

                    showNotice(veltrixaAdmin.saved, false);
                } catch (error) {
                    toggle.checked = previousState;
                    showNotice(error.message || veltrixaAdmin.error, true);
                } finally {
                    toggle.disabled = false;
                }
            });
        });
    });
})();
