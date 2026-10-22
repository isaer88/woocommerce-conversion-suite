(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const timers = document.querySelectorAll(
            '[data-wcs-sale-countdown]'
        );

        if (!timers.length) {
            return;
        }

        const serverTime = (
            typeof wcsSaleCountdown !== 'undefined'
            && Number.isFinite(Number(wcsSaleCountdown.serverTime))
        )
            ? Number(wcsSaleCountdown.serverTime)
            : Math.floor(Date.now() / 1000);

        const startTime = performance.now();

        function currentTime() {
            return serverTime + (performance.now() - startTime) / 1000;
        }

        function pad(value) {
            return String(value).padStart(2, '0');
        }

        timers.forEach(function (timer) {
            const end = Number(timer.dataset.end);

            if (!Number.isFinite(end) || end <= 0) {
                return;
            }

            const fields = {
                days: timer.querySelector('[data-wcs-sc="days"]'),
                hours: timer.querySelector('[data-wcs-sc="hours"]'),
                minutes: timer.querySelector('[data-wcs-sc="minutes"]'),
                seconds: timer.querySelector('[data-wcs-sc="seconds"]')
            };

            if (Object.values(fields).some(function (field) {
                return !field;
            })) {
                return;
            }

            let interval = null;

            function update() {
                const remaining = Math.max(
                    0,
                    Math.ceil(end - currentTime())
                );

                if (remaining <= 0) {
                    timer.hidden = true;

                    if (interval !== null) {
                        clearInterval(interval);
                    }

                    return;
                }

                const days = Math.floor(remaining / 86400);
                const hours = Math.floor((remaining % 86400) / 3600);
                const minutes = Math.floor((remaining % 3600) / 60);
                const seconds = remaining % 60;

                fields.days.textContent = pad(days);
                fields.hours.textContent = pad(hours);
                fields.minutes.textContent = pad(minutes);
                fields.seconds.textContent = pad(seconds);

                timer.hidden = false;
            }

            update();
            interval = setInterval(update, 1000);

            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) {
                    update();
                }
            });
        });
    });
})();
