/**
 * Ajinsafro Traveler Home — home.js
 * Mobile drawer menu + accordion sub-menus + Modern search tabs + sliders
 */
(function(){
    'use strict';

    function initDrawer() {
        var burger = document.getElementById('aj-burger');
        var drawer = document.getElementById('aj-drawer');
        var drawerClose = document.getElementById('aj-drawer-close');
        var navMenu = document.getElementById('aj-nav-menu');

        if (!burger || !drawer) return;

        function openDrawer() {
            if (document.body) {
                document.body.classList.add('menu-open');
                document.body.style.overflow = 'hidden';
            }
            drawer.classList.add('aj-menu-open');
            drawer.setAttribute('aria-hidden', 'false');
            burger.setAttribute('aria-expanded', 'true');
        }
        function closeDrawer() {
            if (document.body) {
                document.body.classList.remove('menu-open');
                document.body.style.overflow = '';
            }
            drawer.classList.remove('aj-menu-open');
            drawer.setAttribute('aria-hidden', 'true');
            burger.setAttribute('aria-expanded', 'false');
        }

        burger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (document.body.classList.contains('menu-open')) closeDrawer();
            else openDrawer();
        });
        if (drawerClose) {
            drawerClose.addEventListener('click', function (e) {
                e.preventDefault();
                closeDrawer();
            });
        }
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1280 && document.body.classList.contains('menu-open')) closeDrawer();
        });

        if (navMenu) {
            navMenu.addEventListener('click', function (e) {
                var li = e.target.closest('li.aj-has-sub, li.menu-item-has-children');
                if (!li || !li.querySelector('.aj-sub-menu, .sub-menu')) return;
                var link = li.querySelector(':scope > a');
                if (link && link.contains(e.target)) {
                    e.preventDefault();
                    li.classList.toggle('aj-sub-open');
                }
            });
        }
    }

    /* ── Modern Search Tabs ──────────────────────────────────────── */
    function initSearchTabs() {
        var tabsContainer = document.getElementById('aj-search-tabs');
        if (!tabsContainer) return;

        var tabs = tabsContainer.querySelectorAll('.aj-search-tab');
        var forms = document.querySelectorAll('.aj-search-form');

        tabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                var targetId = tab.getAttribute('data-target');

                tabs.forEach(function(t) {
                    t.classList.remove('aj-search-tab--active');
                });
                tab.classList.add('aj-search-tab--active');

                forms.forEach(function(f) {
                    f.classList.remove('aj-search-form--active');
                });

                var targetForm = document.getElementById('aj-form-' + targetId);
                if (targetForm) {
                    targetForm.classList.add('aj-search-form--active');
                }
            });
        });
    }

    /* ── Promotions : carte principale + pile de prévisualisations à droite ─ */
    function initPromoSplit() {
        var root = document.querySelector('.aj-promo-split[data-slides]');
        if (!root) return;

        var raw = root.getAttribute('data-slides');
        if (!raw) return;

        var slides;
        try {
            slides = JSON.parse(raw);
        } catch (err) {
            return;
        }
        if (!slides || !slides.length) return;

        var featuredWrap = root.querySelector('[data-featured] .aj-promo-split__featured-inner');
        var previewsEl = root.querySelector('[data-previews]');
        if (!featuredWrap) return;

        var autoplay = root.getAttribute('data-autoplay') === '1';
        var delay = parseInt(root.getAttribute('data-delay') || '5000', 10);
        if (isNaN(delay) || delay < 2000) delay = 5000;

        var activeIdx = parseInt(root.getAttribute('data-active') || '0', 10);
        if (isNaN(activeIdx)) activeIdx = 0;
        if (activeIdx >= slides.length) activeIdx = 0;

        var timer = null;

        function esc(s) {
            if (s == null) return '';
            var d = document.createElement('div');
            d.textContent = String(s);
            return d.innerHTML;
        }

        function escAttr(s) {
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;')
                .replace(/</g, '&lt;');
        }

        var firstFeaturedPaint = true;

        function buildFeaturedHtml(slide) {
            var accent = slide.accent_color && /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(slide.accent_color)
                ? slide.accent_color
                : '';
            var style = accent ? ' style="--aj-promo-accent:' + escAttr(accent) + ';"' : '';
            var rail = slide.title
                ? '<span class="aj-promo-split__rail" aria-hidden="true"><span class="aj-promo-split__rail-text">' + esc(slide.title) + '</span></span>'
                : '';
            var img = slide.image_url
                ? '<div class="aj-promo-split__media"><img src="' + escAttr(slide.image_url) + '" alt="" loading="lazy" decoding="async" width="800" height="520"></div>'
                : '<div class="aj-promo-split__media"><span class="aj-promo-split__fallback" aria-hidden="true"></span></div>';

            var titleHtml = '';
            if (slide.title) {
                if (slide.conflict_links && slide.link_url) {
                    titleHtml =
                        '<a class="aj-promo-split__title aj-promo-split__title--link" href="' +
                        escAttr(slide.link_url) +
                        '"' +
                        (slide.link_target === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '') +
                        '>' +
                        esc(slide.title) +
                        '</a>';
                } else {
                    titleHtml = '<span class="aj-promo-split__title">' + esc(slide.title) + '</span>';
                }
            }
            var descHtml = slide.subtitle ? '<p class="aj-promo-split__desc">' + esc(slide.subtitle) + '</p>' : '';

            var btnHtml = '';
            var showBtn = slide.button_enabled && slide.button_text && slide.button_url;
            if (showBtn) {
                btnHtml =
                    '<span class="aj-promo-split__btn-wrap"><a class="aj-promo-split__btn" href="' +
                    escAttr(slide.button_url) +
                    '"' +
                    (slide.link_target === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '') +
                    '>' +
                    esc(slide.button_text) +
                    '</a></span>';
            } else if (slide.button_enabled && slide.button_text) {
                btnHtml =
                    '<span class="aj-promo-split__btn-wrap"><span class="aj-promo-split__btn aj-promo-split__btn--text">' +
                    esc(slide.button_text) +
                    '</span></span>';
            }

            var body =
                '<div class="aj-promo-split__body">' +
                img +
                '<div class="aj-promo-split__scrim" aria-hidden="true"></div>' +
                '<div class="aj-promo-split__content">' +
                titleHtml +
                descHtml +
                btnHtml +
                '</div></div>';

            if (slide.wrap_link && slide.link_url) {
                return (
                    '<a class="aj-promo-split__featured-surface aj-promo-split__featured-surface--link"' +
                    style +
                    ' href="' +
                    escAttr(slide.link_url) +
                    '"' +
                    (slide.link_target === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '') +
                    '>' +
                    rail +
                    body +
                    '</a>'
                );
            }
            return '<div class="aj-promo-split__featured-surface"' + style + ' role="region">' + rail + body + '</div>';
        }

        function buildPreviewsHtml(idxActive) {
            if (!previewsEl || slides.length < 2) return;
            var parts = [];
            for (var j = 0; j < slides.length; j++) {
                if (j === idxActive) continue;
                var s = slides[j];
                var pvStyle = '';
                if (s.accent_color && /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(s.accent_color)) {
                    pvStyle = ' style="--preview-accent:' + escAttr(s.accent_color) + ';"';
                }
                var thumb = s.image_url
                    ? '<span class="aj-promo-split__preview-thumb" aria-hidden="true"><img src="' +
                      escAttr(s.image_url) +
                      '" alt="" loading="lazy" decoding="async" width="120" height="400"></span>'
                    : '<span class="aj-promo-split__preview-thumb" aria-hidden="true"><span class="aj-promo-split__preview-fallback"></span></span>';
                parts.push(
                    '<button type="button" class="aj-promo-split__preview"' +
                        pvStyle +
                        ' data-go-index="' +
                        j +
                        '" role="listitem" aria-label="' +
                        escAttr(s.title || 'Voir cette offre') +
                        '">' +
                        thumb +
                        '<span class="aj-promo-split__preview-label">' +
                        esc(s.title || '') +
                        '</span></button>'
                );
            }
            previewsEl.innerHTML = parts.join('');
        }

        function setActive(i) {
            var n = ((i % slides.length) + slides.length) % slides.length;
            activeIdx = n;
            root.setAttribute('data-active', String(n));
            root.classList.toggle('aj-promo-split--single', slides.length < 2);

            var skipInitialDom = firstFeaturedPaint;
            if (firstFeaturedPaint) {
                firstFeaturedPaint = false;
            } else {
                featuredWrap.style.opacity = '0.88';
                window.requestAnimationFrame(function () {
                    featuredWrap.innerHTML = buildFeaturedHtml(slides[n]);
                    featuredWrap.style.opacity = '1';
                });
            }

            if (!skipInitialDom) {
                buildPreviewsHtml(n);
            }
        }

        featuredWrap.style.transition = 'opacity 0.22s ease';

        function clearTimer() {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        }

        function startTimer() {
            clearTimer();
            if (!autoplay || slides.length < 2) return;
            timer = setInterval(function () {
                setActive(activeIdx + 1);
            }, delay);
        }

        if (previewsEl) {
            previewsEl.addEventListener('click', function (e) {
                var btn = e.target.closest('.aj-promo-split__preview');
                if (!btn || !previewsEl.contains(btn)) return;
                var go = parseInt(btn.getAttribute('data-go-index') || '-1', 10);
                if (isNaN(go) || go === activeIdx) return;
                setActive(go);
                clearTimer();
                startTimer();
            });
        }

        root.addEventListener('mouseenter', clearTimer);
        root.addEventListener('mouseleave', startTimer);

        var rid = root.id || '';
        var prevBtn = document.querySelector('.aj-promo-split__prev[aria-controls="' + rid + '"]');
        var nextBtn = document.querySelector('.aj-promo-split__next[aria-controls="' + rid + '"]');
        if (!prevBtn) prevBtn = root.closest('.aj-promos') ? root.closest('.aj-promos').querySelector('.aj-promo-split__prev') : null;
        if (!nextBtn) nextBtn = root.closest('.aj-promos') ? root.closest('.aj-promos').querySelector('.aj-promo-split__next') : null;

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                setActive(activeIdx - 1);
                clearTimer();
                startTimer();
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                setActive(activeIdx + 1);
                clearTimer();
                startTimer();
            });
        }

        setActive(activeIdx);
        startTimer();
    }

    /* ── Generic Slider (prev/next arrows) ──────────────────────── */
    function initSlider(trackId, prevSelector, nextSelector) {
        var track = document.getElementById(trackId);
        if (!track) return;

        function scrollAmt() {
            var item = track.querySelector('.aj-slider-v2__item, .aj-card');
            if (!item) return 320;
            var gap = parseFloat(getComputedStyle(track).gap) || 16;
            return item.offsetWidth + gap;
        }

        var prevBtn = document.querySelector(prevSelector);
        var nextBtn = document.querySelector(nextSelector);

        if (prevBtn) prevBtn.addEventListener('click', function() {
            track.scrollBy({ left: -scrollAmt(), behavior: 'smooth' });
        });
        if (nextBtn) nextBtn.addEventListener('click', function() {
            track.scrollBy({ left: scrollAmt(), behavior: 'smooth' });
        });
    }

    /* ── Init Everything ─────────────────────────────────────────── */
    function init() {
        initDrawer();
        initSearchTabs();

        initSlider('aj-lm-track', '.aj-arrow--prev', '.aj-arrow--next');
        initSlider('aj-accom-track', '.aj-accom-prev', '.aj-accom-next');
        initSlider('aj-theme-track', '.aj-theme-prev', '.aj-theme-next');
        initPromoSplit();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
