(function () {
    'use strict';

    function initStickyCart() {
        const sticky = document.getElementById('wcs-sticky-cart');

        if (!sticky) {
            return;
        }

        const form = document.querySelector(
            'form.cart'
        );

        if (!form) {
            return;
        }

        const button = sticky.querySelector(
            '.wcs-sticky-button'
        );

        const price = sticky.querySelector(
            '.wcs-sticky-price'
        );

        const productType = sticky.dataset.productType;
        const originalPrice = price ? price.innerHTML : '';

        if (!button) {
            return;
        }

        const getNativeButton = function () {
            return form.querySelector(
                '.single_add_to_cart_button'
            );
        };

        const getVariationId = function () {
            const input = form.querySelector(
                'input[name="variation_id"]'
            );

            return input ? input.value : '';
        };

        const isReady = function () {
            const nativeButton = getNativeButton();

            if (!nativeButton || nativeButton.disabled) {
                return false;
            }

            if (
                nativeButton.classList.contains('disabled') ||
                nativeButton.classList.contains('wc-variation-selection-needed') ||
                nativeButton.classList.contains('wc-variation-is-unavailable')
            ) {
                return false;
            }

            if (productType === 'variable') {
                if (!getVariationId() || getVariationId() === '0') {
                    return false;
                }

                const selects = form.querySelectorAll(
                    'select[name^="attribute_"]'
                );

                for (const select of selects) {
                    if (!select.value) {
                        return false;
                    }
                }
            }

            return true;
        };

        const syncButton = function () {
            const ready = isReady();

            button.disabled = !ready;

            button.textContent = ready
                ? 'Add to cart'
                : (
                    productType === 'variable'
                        ? 'Choose options'
                        : 'Unavailable'
                );
        };

        const updateVisibility = function () {
            const nativeButton = getNativeButton();

            if (!nativeButton) {
                sticky.classList.remove('is-visible');
                sticky.setAttribute('aria-hidden', 'true');
                return;
            }

            const rect = nativeButton.getBoundingClientRect();

            const shouldShow = rect.bottom < 0;

            sticky.classList.toggle(
                'is-visible',
                shouldShow
            );

            sticky.setAttribute(
                'aria-hidden',
                shouldShow ? 'false' : 'true'
            );
        };

        button.addEventListener('click', function () {
            const nativeButton = getNativeButton();

            if (!nativeButton) {
                return;
            }

            if (!isReady()) {
                form.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                return;
            }

            // Use WooCommerce's original submit button.
            nativeButton.click();
        });

        form.addEventListener('change', function () {
            window.setTimeout(syncButton, 50);
        });

        form.addEventListener('input', function () {
            window.setTimeout(syncButton, 50);
        });

        if (window.jQuery) {
            const $form = window.jQuery(form);

            $form.on(
                'found_variation',
                function (event, variation) {
                    if (
                        price &&
                        variation &&
                        variation.price_html
                    ) {
                        price.innerHTML = variation.price_html;
                    }

                    window.setTimeout(syncButton, 0);
                }
            );

            $form.on(
                'reset_data hide_variation',
                function () {
                    if (price) {
                        price.innerHTML = originalPrice;
                    }

                    window.setTimeout(syncButton, 0);
                }
            );
        }

        const observer = new MutationObserver(function () {
            syncButton();
            updateVisibility();
        });

        const nativeButton = getNativeButton();

        if (nativeButton) {
            observer.observe(nativeButton, {
                attributes: true,
                attributeFilter: ['class', 'disabled']
            });
        }

        window.addEventListener(
            'scroll',
            updateVisibility,
            { passive: true }
        );

        window.addEventListener(
            'resize',
            updateVisibility,
            { passive: true }
        );

        syncButton();
        updateVisibility();
    }

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initStickyCart
        );
    } else {
        initStickyCart();
    }
})();
