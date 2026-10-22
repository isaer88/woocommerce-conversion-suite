(function ($) {
    'use strict';

    $(function () {
        const config = window.wcsWhatsAppOrder || {};
        const buttons = document.querySelectorAll('[data-wcs-wa-order]');

        if (!buttons.length || !config.phone || !config.message) {
            return;
        }

        function formatPrice(value) {
            const amount = Number(value);

            if (!Number.isFinite(amount)) {
                return '';
            }

            const decimals = Math.max(
                0,
                Math.min(8, Number(config.decimals) || 0)
            );

            const fixed = amount.toFixed(decimals).split('.');
            const separator = String(config.thousandSep || '');
            const decimal = String(config.decimalSep || '.');

            const whole = fixed[0].replace(
                /\B(?=(\d{3})+(?!\d))/g,
                function () {
                    return separator;
                }
            );

            const number = decimals
                ? whole + decimal + fixed[1]
                : whole;

            return String(config.priceFormat || '%1$s%2$s')
                .replace('%1$s', String(config.symbol || ''))
                .replace('%2$s', number);
        }

        function getProductData(button) {
            try {
                return JSON.parse(button.dataset.product || '{}');
            } catch (error) {
                return {};
            }
        }

        function getQuantity(form) {
            const input = form
                ? form.querySelector('input.qty')
                : null;

            if (!input) {
                return 1;
            }

            const quantity = Number(input.value);
            const min = Number(input.min || 1);
            const max = input.max ? Number(input.max) : Infinity;
            const step = input.step && input.step !== 'any'
                ? Number(input.step)
                : 1;

            if (
                !Number.isFinite(quantity) ||
                !Number.isFinite(min) ||
                !Number.isFinite(max) && max !== Infinity ||
                !Number.isFinite(step) ||
                step <= 0 ||
                quantity < min ||
                quantity > max ||
                quantity <= 0
            ) {
                return null;
            }

            const steps = (quantity - min) / step;

            if (Math.abs(steps - Math.round(steps)) > 0.000001) {
                return null;
            }

            return quantity;
        }

        function getVariationDescription(form, variation) {
            const attributes = variation.attributes || {};
            const parts = [];

            Object.keys(attributes).forEach(function (key) {
                const value = attributes[key];

                const select = Array.from(
                    form.querySelectorAll('select[name]')
                ).find(function (element) {
                    return element.name === key;
                });

                let label = key
                    .replace(/^attribute_/, '')
                    .replace(/^pa_/, '')
                    .replace(/[_-]/g, ' ');

                let selected = value || '';

                if (select) {
                    const option = select.options[select.selectedIndex];

                    if (option && option.value) {
                        selected = option.textContent.trim();
                    }
                }

                if (selected) {
                    parts.push(label + ': ' + selected);
                }
            });

            return parts.join(', ');
        }

        function replaceTokens(template, values) {
            const tokens = {
                product_name: values.productName || '',
                price: values.price || '',
                quantity: String(values.quantity || 1),
                product_url: values.productUrl || '',
                sku: values.sku || '',
                variation: values.variation || ''
            };

            return template.replace(
                /\{(product_name|price|quantity|product_url|sku|variation)\}/g,
                function (match, key) {
                    return tokens[key];
                }
            );
        }

        function openWhatsApp(message) {
            const phone = String(config.phone).replace(/\D/g, '');

            if (!/^[1-9][0-9]{6,14}$/.test(phone)) {
                return;
            }

            const url = 'https://wa.me/' +
                phone +
                '?text=' +
                encodeURIComponent(message);

            if (config.newTab === true) {
                const link = document.createElement('a');
                link.href = url;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';

                document.body.appendChild(link);
                link.click();
                link.remove();
            } else {
                window.location.assign(url);
            }
        }

        buttons.forEach(function (button) {
            const product = getProductData(button);
            const wrapper = button.closest('.wcs-wa-order');
            const hint = wrapper
                ? wrapper.querySelector('.wcs-wa-order__hint')
                : null;

            const form = document.querySelector(
                'form.cart'
            );

            let currentVariation = null;

            function setHint(message) {
                if (hint) {
                    hint.textContent = message;
                }
            }

            function resetVariation() {
                currentVariation = null;

                if (product.variable) {
                    button.disabled = true;
                    setHint('Select product options first.');
                }
            }

            if (product.variable) {
                button.disabled = true;

                if (form && form.classList.contains('variations_form')) {
                    const $form = $(form);

                    $form.on(
                        'found_variation.wcsWhatsAppOrder',
                        function (event, variation) {
                            if (
                                !variation ||
                                !variation.variation_id ||
                                variation.is_in_stock === false ||
                                variation.is_purchasable === false ||
                                variation.variation_is_active === false
                            ) {
                                resetVariation();
                                return;
                            }

                            currentVariation = variation;
                            button.disabled = false;
                            setHint('');
                        }
                    );

                    $form.on(
                        'reset_data.wcsWhatsAppOrder ' +
                        'hide_variation.wcsWhatsAppOrder',
                        resetVariation
                    );

                    $form.on(
                        'change.wcsWhatsAppOrder',
                        'select[name^="attribute_"], input[name^="attribute_"]',
                        resetVariation
                    );
                }
            }

            button.addEventListener('click', function () {
                if (button.disabled) {
                    return;
                }

                const quantity = getQuantity(form);

                if (quantity === null) {
                    window.alert('Please enter a valid quantity.');
                    return;
                }

                const values = {
                    productName: product.productName || '',
                    productUrl: product.productUrl || window.location.href,
                    sku: product.sku || '',
                    price: product.price || '',
                    quantity: quantity,
                    variation: ''
                };

                if (product.variable) {
                    if (!currentVariation || !currentVariation.variation_id) {
                        setHint('Select product options first.');
                        return;
                    }

                    const variationIdInput = form
                        ? form.querySelector('input[name="variation_id"]')
                        : null;

                    if (
                        variationIdInput &&
                        Number(variationIdInput.value) !==
                        Number(currentVariation.variation_id)
                    ) {
                        resetVariation();
                        return;
                    }

                    values.variation = getVariationDescription(
                        form,
                        currentVariation
                    );

                    values.price = formatPrice(
                        currentVariation.display_price
                    );

                    values.sku = currentVariation.sku || product.sku || '';

                    values.productUrl = product.productUrl +
                        (product.productUrl.includes('?') ? '&' : '?') +
                        'variation_id=' +
                        encodeURIComponent(currentVariation.variation_id);

                    const selectedAttributes = new URLSearchParams();

                    Object.keys(currentVariation.attributes || {}).forEach(
                        function (key) {
                            const value = currentVariation.attributes[key];

                            const field = Array.from(
                                form.querySelectorAll('[name]')
                            ).find(function (element) {
                                return element.name === key;
                            });

                            const selected = field ? field.value : value;

                            if (selected) {
                                selectedAttributes.append(key, selected);
                            }
                        }
                    );

                    const query = selectedAttributes.toString();

                    if (query) {
                        values.productUrl += '&' + query;
                    }
                }

                const message = replaceTokens(
                    String(config.message),
                    values
                ).replace(/\n{3,}/g, '\n\n').trim();

                if (!message) {
                    return;
                }

                openWhatsApp(message);
            });
        });
    });
})(jQuery);
