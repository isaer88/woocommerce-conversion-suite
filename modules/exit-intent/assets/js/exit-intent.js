(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const popup = document.getElementById('wcs-exit-intent');
        const config = window.wcsExitIntent || {};

        if (!popup) {
            return;
        }

        const dialog = popup.querySelector(
            '.wcs-exit-intent__dialog'
        );

        const closeButtons = popup.querySelectorAll(
            '[data-wcs-ei-close]'
        );

        if (!dialog) {
            return;
        }

        const delay = Math.max(
            0,
            Number(config.delay) || 0
        ) * 1000;

        const mobileDelay = Math.max(
            5000,
            Number(config.mobileDelay) * 1000 || 30000
        );

        const frequencyHours = Math.max(
            1,
            Number(config.frequencyHours) || 24
        );

        const storageKey = String(
            config.storageKey || 'wcs_exit_intent_last_shown_v1'
        );

        const isMobile = window.matchMedia(
            '(hover: none), (pointer: coarse)'
        ).matches;

        const sessionStart = Date.now();

        let shown = false;
        let isOpen = false;
        let previousFocus = null;
        let previousBodyOverflow = '';
        let previousBodyClass = false;
        let mobileTimer = null;

        const focusableSelector = [
            'a[href]',
            'button:not([disabled])',
            'input:not([disabled])',
            'select:not([disabled])',
            'textarea:not([disabled])',
            '[tabindex]:not([tabindex="-1"])'
        ].join(',');

        function readStorage() {
            try {
                const value = window.localStorage.getItem(storageKey);
                const timestamp = Number(value);

                return Number.isFinite(timestamp)
                    ? timestamp
                    : 0;
            } catch (error) {
                return 0;
            }
        }

        function writeStorage() {
            try {
                window.localStorage.setItem(
                    storageKey,
                    String(Date.now())
                );
            } catch (error) {
                // Storage can be unavailable in restricted browsers.
            }
        }

        function frequencyAllows() {
            const lastShown = readStorage();

            if (!lastShown) {
                return true;
            }

            const elapsed = Date.now() - lastShown;
            const interval = frequencyHours * 60 * 60 * 1000;

            return elapsed >= interval;
        }

        function otherModalIsOpen() {
            const selectors = [
                'dialog[open]',
                '[role="dialog"][aria-modal="true"]',
                '.elementor-popup-modal',
                '.mfp-wrap',
                '.woocommerce-modal'
            ];

            return Array.from(
                document.querySelectorAll(selectors.join(','))
            ).some(function (element) {
                if (popup.contains(element)) {
                    return false;
                }

                const style = window.getComputedStyle(element);

                return (
                    style.display !== 'none' &&
                    style.visibility !== 'hidden' &&
                    element.getClientRects().length > 0
                );
            });
        }

        function canShow() {
            if (shown || isOpen) {
                return false;
            }

            if (document.visibilityState !== 'visible') {
                return false;
            }

            if (Date.now() - sessionStart < delay) {
                return false;
            }

            if (!frequencyAllows()) {
                return false;
            }

            if (otherModalIsOpen()) {
                return false;
            }

            return true;
        }

        function getFocusable() {
            return Array.from(
                dialog.querySelectorAll(focusableSelector)
            ).filter(function (element) {
                return (
                    element.getClientRects().length > 0 &&
                    window.getComputedStyle(element).visibility !== 'hidden'
                );
            });
        }

        function openPopup() {
            if (!canShow()) {
                return;
            }

            shown = true;
            isOpen = true;

            previousFocus = document.activeElement;
            previousBodyOverflow = document.body.style.overflow;
            previousBodyClass = document.body.classList.contains(
                'wcs-exit-intent-open'
            );

            popup.hidden = false;

            document.body.classList.add(
                'wcs-exit-intent-open'
            );

            document.body.style.overflow = 'hidden';

            writeStorage();

            if (mobileTimer) {
                window.clearTimeout(mobileTimer);
                mobileTimer = null;
            }

            document.removeEventListener(
                'mouseout',
                onMouseOut
            );

            document.addEventListener(
                'keydown',
                onKeyDown
            );

            const focusable = getFocusable();

            if (focusable.length) {
                focusable[0].focus();
            } else {
                dialog.focus();
            }
        }

        function closePopup() {
            if (!isOpen) {
                return;
            }

            isOpen = false;
            popup.hidden = true;

            if (!previousBodyClass) {
                document.body.classList.remove(
                    'wcs-exit-intent-open'
                );
            }

            document.body.style.overflow = previousBodyOverflow;

            document.removeEventListener(
                'keydown',
                onKeyDown
            );

            if (
                previousFocus &&
                typeof previousFocus.focus === 'function' &&
                previousFocus.isConnected
            ) {
                previousFocus.focus();
            }
        }

        function onKeyDown(event) {
            if (!isOpen) {
                return;
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                closePopup();
                return;
            }

            if (event.key !== 'Tab') {
                return;
            }

            const focusable = getFocusable();

            if (!focusable.length) {
                event.preventDefault();
                dialog.focus();
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            const active = document.activeElement;

            if (event.shiftKey) {
                if (active === first || !dialog.contains(active)) {
                    event.preventDefault();
                    last.focus();
                }
            } else if (
                active === last ||
                !dialog.contains(active)
            ) {
                event.preventDefault();
                first.focus();
            }
        }

        function onMouseOut(event) {
            if (isMobile) {
                return;
            }

            // Only react when the pointer leaves the document.
            if (event.relatedTarget || event.toElement) {
                return;
            }

            if (event.clientY > 5) {
                return;
            }

            openPopup();
        }

        closeButtons.forEach(function (button) {
            button.addEventListener('click', closePopup);
        });

        if (isMobile) {
            if (config.mobileEnabled === true) {
                mobileTimer = window.setTimeout(
                    openPopup,
                    Math.max(delay, mobileDelay)
                );
            }
        } else {
            document.addEventListener(
                'mouseout',
                onMouseOut
            );
        }
    });
})();
