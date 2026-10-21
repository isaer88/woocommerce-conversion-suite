(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof wcsRecentlyViewed === 'undefined') {
            return;
        }

        const config = wcsRecentlyViewed;
        const cookieName = config.cookieName;
        const maxHistory = 20;
        const productId = parseInt(config.productId, 10) || 0;

        /*
         * Consent integration:
         * When required, another consent manager must explicitly set
         * window.wcsRecentlyViewedConsent = true before this script runs.
         */
        if (
            config.requireConsent &&
            window.wcsRecentlyViewedConsent !== true
        ) {
            return;
        }

        function getHistory() {
            const prefix = cookieName + '=';
            const entry = document.cookie
                .split('; ')
                .find(function (part) {
                    return part.startsWith(prefix);
                });

            if (!entry) {
                return [];
            }

            try {
                const value = JSON.parse(
                    decodeURIComponent(entry.substring(prefix.length))
                );

                if (!Array.isArray(value)) {
                    return [];
                }

                return value
                    .map(Number)
                    .filter(function (id) {
                        return Number.isSafeInteger(id) && id > 0;
                    })
                    .slice(0, maxHistory);
            } catch (error) {
                return [];
            }
        }

        function saveHistory(ids) {
            const seconds = 30 * 24 * 60 * 60;

            document.cookie =
                cookieName + '=' +
                encodeURIComponent(JSON.stringify(ids)) +
                '; path=/; max-age=' + seconds +
                '; SameSite=Lax';
        }

        let history = getHistory();

        if (productId > 0) {
            history = [
                productId,
                ...history.filter(function (id) {
                    return id !== productId;
                })
            ].slice(0, maxHistory);

            saveHistory(history);
        }

        const containers = document.querySelectorAll(
            '[data-wcs-recently-viewed]'
        );

        if (!containers.length) {
            return;
        }

        let displayIds = history.slice();

        if (!config.showCurrent && productId > 0) {
            displayIds = displayIds.filter(function (id) {
                return id !== productId;
            });
        }

        displayIds = displayIds.slice(
            0,
            Math.max(4, Math.min(12, parseInt(config.limit, 10) || 4))
        );

        if (!displayIds.length) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'wcs_recently_viewed_products');

        displayIds.forEach(function (id) {
            formData.append('product_ids[]', String(id));
        });

        fetch(config.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Request failed: ' + response.status);
                }

                return response.json();
            })
            .then(function (result) {
                if (
                    !result.success ||
                    !result.data ||
                    !Array.isArray(result.data.products)
                ) {
                    return;
                }

                const products = result.data.products;

                if (!products.length) {
                    return;
                }

                containers.forEach(function (container) {
                    const grid = container.querySelector(
                        '.wcs-rv__products'
                    );

                    if (!grid) {
                        return;
                    }

                    grid.replaceChildren();

                    products.forEach(function (product) {
                        const card = document.createElement('article');
                        card.className = 'wcs-rv__product';

                        const link = document.createElement('a');
                        link.className = 'wcs-rv__link';
                        link.href = product.url;

                        const image = document.createElement('div');
                        image.className = 'wcs-rv__image';

                        // Image HTML comes from WooCommerce on our server.
                        image.innerHTML = product.image;

                        const name = document.createElement('h3');
                        name.className = 'wcs-rv__name';
                        name.textContent = product.name;

                        const price = document.createElement('div');
                        price.className = 'wcs-rv__price';

                        // Price HTML comes from WooCommerce on our server.
                        price.innerHTML = product.price;

                        link.appendChild(image);
                        link.appendChild(name);
                        card.appendChild(link);
                        card.appendChild(price);

                        grid.appendChild(card);
                    });

                    container.hidden = false;
                });
            })
            .catch(function (error) {
                console.warn('Recently Viewed:', error.message);
            });
    });
})();
