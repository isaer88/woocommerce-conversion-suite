
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const toggles = document.querySelectorAll('.wcs-module-toggle');
        const notice = document.getElementById('wcs-dashboard-notice');
        const activeCount = document.getElementById('wcs-active-count');

        if (!toggles.length || typeof wcsAdmin === 'undefined') {
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
                data.append('action', 'wcs_toggle_module');
                data.append('nonce', wcsAdmin.nonce);
                data.append('module_id', toggle.dataset.moduleId);
                data.append('enabled', requestedState ? '1' : '0');

                try {
                    const response = await fetch(wcsAdmin.ajaxUrl, {
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
                                : wcsAdmin.error
                        );
                    }

                    const savedState = result.data.enabled === true;
                    toggle.checked = savedState;

                    if (status) {
                        status.textContent = savedState ? 'Active' : 'Inactive';
                        status.classList.toggle('is-active', savedState);
                        status.classList.toggle('is-inactive', !savedState);
                    }

                    if (activeCount) {
                        const count = document.querySelectorAll(
                            '.wcs-module-toggle:checked'
                        ).length;

                        activeCount.textContent = String(count);
                    }

                    showNotice(wcsAdmin.saved, false);
                } catch (error) {
                    toggle.checked = previousState;
                    showNotice(error.message || wcsAdmin.error, true);
                } finally {
                    toggle.disabled = false;
                }
            });
        });
    });
})();
