(function () {
    function initCarousel(root) {
        var track = root.querySelector('[data-tmd-success-track]');
        var slides = Array.prototype.slice.call(root.querySelectorAll('[data-tmd-success-slide]'));
        var prev = root.querySelector('[data-tmd-success-prev]');
        var next = root.querySelector('[data-tmd-success-next]');
        var dots = Array.prototype.slice.call(root.querySelectorAll('[data-tmd-success-dot]'));
        var current = 0;

        if (!track || slides.length === 0) {
            return;
        }

        function go(index) {
            current = (index + slides.length) % slides.length;
            track.style.transform = 'translateX(' + (-100 * current) + '%)';

            dots.forEach(function (dot, dotIndex) {
                dot.classList.toggle('is-active', dotIndex === current);
                dot.setAttribute('aria-current', dotIndex === current ? 'true' : 'false');
            });
        }

        if (prev) {
            prev.addEventListener('click', function () {
                go(current - 1);
            });
        }

        if (next) {
            next.addEventListener('click', function () {
                go(current + 1);
            });
        }

        dots.forEach(function (dot, index) {
            dot.addEventListener('click', function () {
                go(index);
            });
        });

        go(0);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-tmd-success-carousel]').forEach(initCarousel);
    });
}());
