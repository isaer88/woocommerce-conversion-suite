(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof wcsFBT === 'undefined') {
            return;
        }

        document.querySelectorAll('[data-wcs-fbt]').forEach(function (box) {
            const checkboxes = Array.from(
                box.querySelectorAll('.wcs-fbt__checkbox')
            );

            const total = box.querySelector('[data-wcs-fbt-total]');
            const button = box.querySelector('[data-wcs-fbt-add]');
            const message = box.querySelector('.wcs-fbt__message');
            const products = box.querySelector('.wcs-fbt__products');

            if (!total || !button || !products || !checkboxes.length) {
                return;
            }

            const mainId = parseInt(products.dataset.productId, 10);

            function formatPrice(value) {
                try {
                    return new Intl.NumberFormat(
                        document.documentElement.lang || 'en-US',
                        {
                            style: 'currency',
                            currency: wcsFBT.currencyCode,
                            minimumFractionDigits: Number(wcsFBT.decimals),
                            maximumFractionDigits: Number(wcsFBT.decimals)
                        }
                    ).format(value);
                } catch (error) {
                    return value.toFixed(2);
                }
            }

            function updateTotal() {
                let amount = 0;

                checkboxes.forEach(function (checkbox) {
                    if (checkbox.checked) {
                        amount += Number(checkbox.dataset.price) || 0;
                    }
                });

                total.textContent = formatPrice(amount);
            }

            checkboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', updateTotal);
            });

            updateTotal();

            button.addEventListener('click', function () {
                if (button.disabled) {
                    return;
                }

                const selected = checkboxes
                    .filter(function (checkbox) {
                        return checkbox.checked && !checkbox.disabled;
                    })
                    .map(function (checkbox) {
                        return parseInt(checkbox.value, 10);
                    });

                const data = new FormData();

                data.append('action', 'wcs_fbt_add');
                data.append('nonce', wcsFBT.nonce);
                data.append('product_id', String(mainId));

                selected.forEach(function (id) {
                    data.append('related_ids[]', String(id));
                });

                button.disabled = true;
                message.textContent = 'Adding products...';

                fetch(wcsFBT.ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: data
                })
                    .then(function (response) {
                        return response.json().then(function (json) {
                            if (!response.ok || !json.success) {
                                throw new Error(
                                    json.data && json.data.message
                                        ? json.data.message
                                        : 'Could not add products.'
                                );
                            }

                            return json;
                        });
                    })
                    .then(function (result) {
                        message.textContent = result.data.message;

                        window.location.href =
                            result.data.cartUrl || wcsFBT.cartUrl;
                    })
                    .catch(function (error) {
                        message.textContent = error.message;
                        button.disabled = false;
                    });
            });
        });
    });
})();
