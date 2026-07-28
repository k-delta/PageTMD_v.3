(function () {
    function initFeaturedCarousel(section) {
        var viewport = section.querySelector('[data-tm-eqd-viewport]');
        var track = section.querySelector('[data-tm-eqd-track]');
        var cards = Array.prototype.slice.call(section.querySelectorAll('[data-tm-eqd-card]'));
        var dotsWrap = section.querySelector('[data-tm-eqd-dots]');
        var prev = section.querySelector('[data-tm-eqd-prev]');
        var next = section.querySelector('[data-tm-eqd-next]');

        if (!viewport || !track || !cards.length || !dotsWrap) {
            return;
        }

        function getPerView() {
            if (window.matchMedia('(max-width: 640px)').matches) {
                return 1;
            }
            if (window.matchMedia('(max-width: 1024px)').matches) {
                return 2;
            }
            return 3;
        }

        function getPages() {
            return Math.max(1, Math.ceil(cards.length / getPerView()));
        }

        function getCardLeft(index) {
            var card = cards[Math.min(index, cards.length - 1)];
            return card ? card.offsetLeft - track.offsetLeft : 0;
        }

        function getCurrentPage() {
            var perView = getPerView();
            var closestIndex = 0;
            var closestDistance = Infinity;

            cards.forEach(function (card, index) {
                var left = card.offsetLeft - track.offsetLeft;
                var distance = Math.abs(left - viewport.scrollLeft);
                if (distance < closestDistance) {
                    closestDistance = distance;
                    closestIndex = index;
                }
            });

            return Math.min(getPages() - 1, Math.round(closestIndex / perView));
        }

        function goToPage(page) {
            var perView = getPerView();
            var pages = getPages();
            var safePage = Math.max(0, Math.min(page, pages - 1));
            viewport.scrollTo({
                left: getCardLeft(safePage * perView),
                behavior: 'smooth'
            });
        }

        function updateDots() {
            var dots = Array.prototype.slice.call(dotsWrap.querySelectorAll('.tm-eqd-dot'));
            var activePage = getCurrentPage();

            dots.forEach(function (dot, index) {
                var active = index === activePage;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-current', active ? 'true' : 'false');
            });

            if (prev) {
                prev.hidden = getPages() <= 1;
            }
            if (next) {
                next.hidden = getPages() <= 1;
            }
        }

        function buildDots() {
            var pages = getPages();
            dotsWrap.innerHTML = '';

            if (pages <= 1) {
                updateDots();
                return;
            }

            for (var i = 0; i < pages; i += 1) {
                (function (page) {
                    var dot = document.createElement('button');
                    dot.type = 'button';
                    dot.className = 'tm-eqd-dot';
                    dot.setAttribute('aria-label', 'Ir al grupo ' + (page + 1));
                    dot.addEventListener('click', function () {
                        goToPage(page);
                    });
                    dotsWrap.appendChild(dot);
                }(i));
            }

            updateDots();
        }

        if (prev) {
            prev.addEventListener('click', function () {
                goToPage(getCurrentPage() - 1);
            });
        }

        if (next) {
            next.addEventListener('click', function () {
                goToPage(getCurrentPage() + 1);
            });
        }

        var raf = null;
        viewport.addEventListener('scroll', function () {
            if (raf) {
                cancelAnimationFrame(raf);
            }
            raf = requestAnimationFrame(updateDots);
        });

        var resizeTimer = null;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(buildDots, 150);
        });

        buildDots();
    }

    function guessSocialPlatform(link) {
        var text = [
            link.getAttribute('data-tm-social') || '',
            link.getAttribute('aria-label') || '',
            link.getAttribute('title') || '',
            link.textContent || '',
            link.href || ''
        ].join(' ').toLowerCase();

        if (text.indexOf('facebook') !== -1 || text === 'f') {
            return 'facebook';
        }
        if (text.indexOf('instagram') !== -1 || text.indexOf('insta') !== -1 || text === 'ig') {
            return 'instagram';
        }
        if (text.indexOf('youtube') !== -1 || text.indexOf('youtu') !== -1 || text === 'yt') {
            return 'youtube';
        }
        return '';
    }

    function updateFooterSocialLinks() {
        if (!window.tmEqdSocialLinks) {
            return;
        }

        var wrappers = Array.prototype.slice.call(document.querySelectorAll('.tmd-footer-social'));
        wrappers.forEach(function (wrapper) {
            var links = Array.prototype.slice.call(wrapper.querySelectorAll('a'));
            var changed = 0;

            links.forEach(function (link) {
                var platform = guessSocialPlatform(link);
                if (platform && window.tmEqdSocialLinks[platform]) {
                    link.href = window.tmEqdSocialLinks[platform];
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    changed += 1;
                }
            });

            if (changed === 0 && links.length >= 3) {
                ['facebook', 'instagram', 'youtube'].forEach(function (platform, index) {
                    if (links[index] && window.tmEqdSocialLinks[platform]) {
                        links[index].href = window.tmEqdSocialLinks[platform];
                        links[index].target = '_blank';
                        links[index].rel = 'noopener noreferrer';
                    }
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-tm-eqd]').forEach(initFeaturedCarousel);
        updateFooterSocialLinks();
    });
}());
