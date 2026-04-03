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

    /* ── Promotions accordion (horizontal expand + autoplay) ─────── */
    function initPromoAccordion() {
        var root = document.getElementById('aj-promos') ? document.querySelector('#aj-promos .aj-promo-acc') : null;
        if (!root) return;

        var strip = root.querySelector('.aj-promo-acc__strip');
        if (!strip) return;

        var panels = strip.querySelectorAll('.aj-promo-acc__panel');
        if (!panels.length) return;

        var autoplay = root.getAttribute('data-autoplay') === '1';
        var delay = parseInt(root.getAttribute('data-delay') || '5000', 10);
        if (isNaN(delay) || delay < 2000) delay = 5000;

        var defIdx = parseInt(root.getAttribute('data-default-index') || '0', 10);
        if (isNaN(defIdx)) defIdx = 0;
        if (defIdx >= panels.length) defIdx = 0;

        var timer = null;
        var active = defIdx;

        function setActive(i) {
            if (!panels.length) return;
            var n = ((i % panels.length) + panels.length) % panels.length;
            active = n;
            panels.forEach(function (p, idx) {
                var on = idx === n;
                p.classList.toggle('is-active', on);
                p.setAttribute('aria-expanded', on ? 'true' : 'false');
            });
        }

        function clearTimer() {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        }

        function startTimer() {
            clearTimer();
            if (!autoplay || panels.length < 2) return;
            timer = setInterval(function () {
                setActive(active + 1);
            }, delay);
        }

        strip.addEventListener('click', function (e) {
            var innerBtn = e.target.closest('.aj-promo-acc__btn');
            if (innerBtn) return;

            var surf = e.target.closest('.aj-promo-acc__surface');
            if (!surf || !strip.contains(surf)) return;

            var panel = surf.closest('.aj-promo-acc__panel');
            if (!panel) return;

            var idx = parseInt(panel.getAttribute('data-index') || '0', 10);
            if (isNaN(idx)) return;

            var isLink = surf.classList.contains('aj-promo-acc__surface--link');
            if (isLink && panel.classList.contains('is-active')) {
                return;
            }
            if (isLink && !panel.classList.contains('is-active')) {
                e.preventDefault();
                setActive(idx);
                clearTimer();
                startTimer();
                return;
            }
            if (!isLink) {
                setActive(idx);
                clearTimer();
                startTimer();
            }
        });

        strip.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            var surf = e.target.closest('.aj-promo-acc__surface--static');
            if (!surf || !strip.contains(surf)) return;
            e.preventDefault();
            var panel = surf.closest('.aj-promo-acc__panel');
            if (!panel) return;
            var idx = parseInt(panel.getAttribute('data-index') || '0', 10);
            setActive(idx);
            clearTimer();
            startTimer();
        });

        root.addEventListener('mouseenter', clearTimer);
        root.addEventListener('mouseleave', startTimer);

        var rid = root.getAttribute('data-root') || '';
        var prevBtn = document.querySelector('.aj-promo-acc__prev[aria-controls="' + rid + '-strip"]');
        var nextBtn = document.querySelector('.aj-promo-acc__next[aria-controls="' + rid + '-strip"]');
        if (!prevBtn) prevBtn = root.parentElement ? root.parentElement.querySelector('.aj-promo-acc__prev') : null;
        if (!nextBtn) nextBtn = root.parentElement ? root.parentElement.querySelector('.aj-promo-acc__next') : null;

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                setActive(active - 1);
                clearTimer();
                startTimer();
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                setActive(active + 1);
                clearTimer();
                startTimer();
            });
        }

        setActive(defIdx);
        startTimer();
    }

    /* ── Promotions split (featured banner + right previews) ─────── */
    function initPromoSplit() {
        var root = document.getElementById('aj-promos') ? document.querySelector('#aj-promos .aj-promo-split') : null;
        if (!root) return;

        var featuredHost = root.querySelector('[data-featured] .aj-promo-split__featured-inner');
        if (!featuredHost) return;

        var previewsHost = root.querySelector('[data-previews]');
        var slidesRaw = root.getAttribute('data-slides') || '[]';
        var slides = [];
        try {
            slides = JSON.parse(slidesRaw);
        } catch (e) {
            slides = [];
        }
        if (!Array.isArray(slides) || !slides.length) return;

        var autoplay = root.getAttribute('data-autoplay') === '1';
        var delay = parseInt(root.getAttribute('data-delay') || '5000', 10);
        if (isNaN(delay) || delay < 2000) delay = 5000;

        var active = parseInt(root.getAttribute('data-active') || '0', 10);
        if (isNaN(active)) active = 0;
        active = Math.min(Math.max(active, 0), slides.length - 1);

        var timer = null;

        function esc(text) {
            return String(text || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function normIndex(i) {
            return ((i % slides.length) + slides.length) % slides.length;
        }

        function renderFeatured(slide) {
            if (!slide) return '';

            var imageUrl = slide.image_url ? String(slide.image_url) : '';
            var title = slide.title ? String(slide.title) : '';
            var subtitle = slide.subtitle ? String(slide.subtitle) : '';
            var linkUrl = slide.link_url ? String(slide.link_url) : '';
            var linkTarget = slide.link_target === '_blank' ? '_blank' : '_self';
            var buttonText = slide.button_text ? String(slide.button_text) : '';
            var buttonUrl = slide.button_url ? String(slide.button_url) : '';
            var btnOn = !!slide.button_enabled;
            var wrapLink = !!slide.wrap_link;
            var conflict = !!slide.conflict_links;
            var showBtn = btnOn && buttonText !== '' && buttonUrl !== '';
            var accent = slide.accent_color ? String(slide.accent_color) : '';

            var styleAttr = '';
            if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(accent)) {
                styleAttr = ' style="--aj-promo-accent:' + esc(accent) + ';"';
            }

            var media = imageUrl !== ''
                ? '<div class="aj-promo-split__media"><img src="' + esc(imageUrl) + '" alt="" loading="lazy" decoding="async" width="800" height="520"></div>'
                : '<div class="aj-promo-split__media"><span class="aj-promo-split__fallback" aria-hidden="true"></span></div>';

            var titleHtml = '';
            if (title !== '') {
                if (conflict && linkUrl !== '') {
                    titleHtml = '<a class="aj-promo-split__title aj-promo-split__title--link" href="' + esc(linkUrl) + '"'
                        + (linkTarget === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '')
                        + '>' + esc(title) + '</a>';
                } else {
                    titleHtml = '<span class="aj-promo-split__title">' + esc(title) + '</span>';
                }
            }

            var descHtml = subtitle !== '' ? '<p class="aj-promo-split__desc">' + esc(subtitle) + '</p>' : '';

            var btnHtml = '';
            if (showBtn) {
                btnHtml = '<span class="aj-promo-split__btn-wrap"><a class="aj-promo-split__btn" href="' + esc(buttonUrl) + '"'
                    + (linkTarget === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '')
                    + '>' + esc(buttonText) + '</a></span>';
            } else if (btnOn && buttonText !== '') {
                btnHtml = '<span class="aj-promo-split__btn-wrap"><span class="aj-promo-split__btn aj-promo-split__btn--text">' + esc(buttonText) + '</span></span>';
            }

            var body = '<div class="aj-promo-split__body">'
                + media
                + '<div class="aj-promo-split__scrim" aria-hidden="true"></div>'
                + '<div class="aj-promo-split__content">' + titleHtml + descHtml + btnHtml + '</div>'
                + '</div>';

            if (wrapLink && linkUrl !== '') {
                return '<a class="aj-promo-split__featured-surface aj-promo-split__featured-surface--link"' + styleAttr + ' href="' + esc(linkUrl) + '"'
                    + (linkTarget === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '')
                    + '>' + body + '</a>';
            }

            return '<div class="aj-promo-split__featured-surface"' + styleAttr + ' role="region">' + body + '</div>';
        }

        function renderPreviews() {
            if (!previewsHost || slides.length < 2) return;

            var html = '';
            for (var i = 0; i < slides.length; i += 1) {
                if (i === active) continue;

                var slide = slides[i] || {};
                var pTitle = slide.title ? String(slide.title) : '';
                var pImage = slide.image_url ? String(slide.image_url) : '';
                var pAccent = slide.accent_color ? String(slide.accent_color) : '';
                var style = '';
                if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(pAccent)) {
                    style = ' style="--preview-accent:' + esc(pAccent) + ';"';
                }

                html += '<button type="button" class="aj-promo-split__preview"' + style
                    + ' data-go-index="' + i + '" role="listitem" aria-label="' + esc(pTitle || 'Voir cette offre') + '">'
                    + '<span class="aj-promo-split__preview-thumb" aria-hidden="true">'
                    + (pImage !== ''
                        ? '<img src="' + esc(pImage) + '" alt="" loading="lazy" decoding="async" width="120" height="400">'
                        : '<span class="aj-promo-split__preview-fallback"></span>')
                    + '</span>'
                    + '<span class="aj-promo-split__preview-label">' + esc(pTitle) + '</span>'
                    + '</button>';
            }

            previewsHost.innerHTML = html;
        }

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
                setActive(active + 1, true);
            }, delay);
        }

        function setActive(nextIndex, fromTimer) {
            active = normIndex(nextIndex);
            root.setAttribute('data-active', String(active));
            featuredHost.innerHTML = renderFeatured(slides[active]);
            renderPreviews();

            if (!fromTimer) {
                clearTimer();
                startTimer();
            }
        }

        if (previewsHost) {
            previewsHost.addEventListener('click', function (e) {
                var btn = e.target.closest('.aj-promo-split__preview[data-go-index]');
                if (!btn || !previewsHost.contains(btn)) return;
                var idx = parseInt(btn.getAttribute('data-go-index') || '0', 10);
                if (isNaN(idx)) return;
                setActive(idx, false);
            });
        }

        var rid = root.getAttribute('id') || '';
        var section = root.closest('#aj-promos') || root.parentElement;
        var prevBtn = section ? section.querySelector('.aj-promo-split__prev[aria-controls="' + rid + '"]') : null;
        var nextBtn = section ? section.querySelector('.aj-promo-split__next[aria-controls="' + rid + '"]') : null;
        if (!prevBtn && section) prevBtn = section.querySelector('.aj-promo-split__prev');
        if (!nextBtn && section) nextBtn = section.querySelector('.aj-promo-split__next');

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                setActive(active - 1, false);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                setActive(active + 1, false);
            });
        }

        root.addEventListener('mouseenter', clearTimer);
        root.addEventListener('mouseleave', startTimer);
        root.addEventListener('focusin', clearTimer);
        root.addEventListener('focusout', function (e) {
            if (!root.contains(e.relatedTarget)) startTimer();
        });

        setActive(active, true);
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
        initPromoAccordion();
        initPromoSplit();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
