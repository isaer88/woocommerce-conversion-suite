(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('wcs-oco-modal');
        const form = document.getElementById('wcs-oco-form');
        const config = window.wcsOneClickOrder || {};

        if (!modal || !form || !config.ajaxUrl) {
            return;
        }

        const dialog = modal.querySelector('.wcs-oco__dialog');
        const openButtons = document.querySelectorAll(
            '[data-wcs-oco-open]'
        );
        const closeButtons = modal.querySelectorAll(
            '[data-wcs-oco-close]'
        );
        const submitButton = form.querySelector(
            '[type="submit"]'
        );
        const feedback = document.getElementById(
            'wcs-oco-feedback'
        );

        let isOpen = false;
        let isSubmitting = false;
        let completed = false;
        let rateLimited = false;
        let previousFocus = null;
        let previousOverflow = '';
        let requestId = generateRequestId();

        function generateRequestId() {
            if (window.crypto && window.crypto.randomUUID) {
                return window.crypto.randomUUID();
            }

            if (window.crypto && window.crypto.getRandomValues) {
                const bytes = new Uint8Array(16);
                window.crypto.getRandomValues(bytes);

                bytes[6] = (bytes[6] & 15) | 64;
                bytes[8] = (bytes[8] & 63) | 128;

                const hex = Array.from(bytes, function (byte) {
                    return byte.toString(16).padStart(2, '0');
                }).join('');

                return [
                    hex.slice(0, 8),
                    hex.slice(8, 12),
                    hex.slice(12, 16),
                    hex.slice(16, 20),
                    hex.slice(20)
                ].join('-');
            }

            // Do not submit without a cryptographically strong ID.
            return '';
        }

        function focusableElements() {
            return Array.from(
                dialog.querySelectorAll(
                    'button:not([disabled]), input:not([disabled]), a[href]'
                )
            ).filter(function (element) {
                return element.getClientRects().length > 0;
            });
        }

        function openModal() {
            if (isOpen) {
                return;
            }

            if (rateLimited && !completed) {
                rateLimited = false;
                submitButton.disabled = false;
                submitButton.textContent = 'Submit Order Request';
            }

            previousFocus = document.activeElement;
            previousOverflow = document.body.style.overflow;

            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            isOpen = true;

            const name = form.querySelector('[name="customer_name"]');

            if (name && !completed) {
                name.focus();
            } else {
                dialog.focus();
            }
        }

        function closeModal() {
            if (!isOpen || isSubmitting) {
                return;
            }

            modal.hidden = true;
            document.body.style.overflow = previousOverflow;
            isOpen = false;

            if (
                previousFocus &&
                previousFocus.isConnected &&
                typeof previousFocus.focus === 'function'
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
                closeModal();
                return;
            }

            if (event.key !== 'Tab') {
                return;
            }

            const elements = focusableElements();

            if (!elements.length) {
                event.preventDefault();
                dialog.focus();
                return;
            }

            const first = elements[0];
            const last = elements[elements.length - 1];

            if (
                event.shiftKey &&
                document.activeElement === first
            ) {
                event.preventDefault();
                last.focus();
            } else if (
                !event.shiftKey &&
                document.activeElement === last
            ) {
                event.preventDefault();
                first.focus();
            }
        }

        function setFeedback(message, success) {
            feedback.textContent = message;
            feedback.classList.toggle(
                'wcs-oco__feedback--success',
                !!success
            );
        }

        openButtons.forEach(function (button) {
            button.addEventListener('click', openModal);
        });

        closeButtons.forEach(function (button) {
            button.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', onKeyDown);

        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (isSubmitting || completed) {
                return;
            }

            if (!form.reportValidity()) {
                return;
            }

            if (!requestId) {
                setFeedback(
                    'Secure request generation is unavailable. Please use regular checkout.',
                    false
                );
                return;
            }

            isSubmitting = true;
            submitButton.disabled = true;
            submitButton.textContent = 'Submitting...';
            setFeedback('', false);

            const data = new FormData(form);

            data.append('action', 'wcs_oco_create_order');
            data.append('nonce', config.nonce || '');
            data.append('request_id', requestId);

            try {
                const response = await fetch(config.ajaxUrl, {
                    method: 'POST',
                    body: data,
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    const message = result.data &&
                        result.data.message
                            ? result.data.message
                            : 'Unable to create the order.';

                    if (response.status === 409) {
                        completed = true;
                        submitButton.disabled = true;
                        submitButton.textContent = 'Request Already Submitted';

                        setFeedback(
                            'This request has already been submitted. ' +
                            'Please contact the store to confirm your order ' +
                            'before trying again.',
                            false
                        );

                        return;
                    }

                    if (response.status === 429) {
                        rateLimited = true;
                        setFeedback(
                            'Too many requests. Please try again later.',
                            false
                        );

                        submitButton.disabled = true;
                        submitButton.textContent = 'Please Try Again Later';

                        return;
                    }

                    throw new Error(message);
                }

                completed = true;

                const message = result.data.message ||
                    config.successText ||
                    'Your request has been received.';

                setFeedback(
                    message + ' Order #' + result.data.order_id,
                    true
                );

                Array.from(form.elements).forEach(function (element) {
                    if (
                        element.matches('input') &&
                        element.type !== 'hidden'
                    ) {
                        element.disabled = true;
                    }
                });

                submitButton.textContent = 'Request Submitted';
            } catch (error) {
                setFeedback(
                    error.message || 'Request failed. Please try again.',
                    false
                );

                /*
                 * Preserve requestId after an uncertain network failure:
                 * retrying must not silently create a second order.
                 */
                submitButton.disabled = false;
                submitButton.textContent = 'Submit Order Request';
            } finally {
                isSubmitting = false;
            }
        });
    });
})();
