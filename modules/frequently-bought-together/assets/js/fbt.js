(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof veltrixaFBT === 'undefined') {
            return;
        }

        document.querySelectorAll('[data-veltrixa-fbt]').forEach(function (box) {
            const checkboxes = Array.from(
                box.querySelectorAll('.veltrixa-fbt__checkbox')
            );

            const total = box.querySelector('[data-veltrixa-fbt-total]');
            const button = box.querySelector('[data-veltrixa-fbt-add]');
            const message = box.querySelector('.veltrixa-fbt__message');
            const products = box.querySelector('.veltrixa-fbt__products');

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
                            currency: veltrixaFBT.currencyCode,
                            minimumFractionDigits: Number(veltrixaFBT.decimals),
                            maximumFractionDigits: Number(veltrixaFBT.decimals)
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

                data.append('action', 'veltrixa_fbt_add');
                data.append('nonce', veltrixaFBT.nonce);
                data.append('product_id', String(mainId));

                selected.forEach(function (id) {
                    data.append('related_ids[]', String(id));
                });

                button.disabled = true;
                message.textContent = 'Adding products...';

                fetch(veltrixaFBT.ajaxUrl, {
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
                            result.data.cartUrl || veltrixaFBT.cartUrl;
                    })
                    .catch(function (error) {
                        message.textContent = error.message;
                        button.disabled = false;
                    });
            });
        });
    });
})();
