/**
 * Ajinsafro Traveler Home — home.js
 * Mobile drawer menu + accordion sub-menus + Tab switching + horizontal slider
 */
(function(){
    'use strict';

    /* ── Mobile drawer (burger opens drawer, close button, overlay) ── */
    var burger = document.getElementById('aj-burger');
    var drawer = document.getElementById('aj-drawer');
    var drawerClose = document.getElementById('aj-drawer-close');
    var navMenu = document.getElementById('aj-nav-menu');

    function openDrawer() {
        if (document.body) {
            document.body.classList.add('menu-open');
            document.body.style.overflow = 'hidden';
        }
        if (drawer) {
            drawer.classList.add('aj-menu-open');
            drawer.setAttribute('aria-hidden', 'false');
        }
        if (burger) burger.setAttribute('aria-expanded', 'true');
    }
    function closeDrawer() {
        if (document.body) {
            document.body.classList.remove('menu-open');
            document.body.style.overflow = '';
        }
        if (drawer) {
            drawer.classList.remove('aj-menu-open');
            drawer.setAttribute('aria-hidden', 'true');
        }
        if (burger) burger.setAttribute('aria-expanded', 'false');
    }

    if (burger && drawer) {
        burger.addEventListener('click', function () {
            if (drawer.classList.contains('aj-menu-open')) closeDrawer();
            else openDrawer();
        });
    }
    if (drawerClose && drawer) {
        drawerClose.addEventListener('click', closeDrawer);
    }
    window.addEventListener('resize', function () {
        if (window.innerWidth > 991 && drawer && drawer.classList.contains('aj-menu-open')) closeDrawer();
    });

    /* Accordion sub-menus in drawer (mobile) */
    if (drawer && navMenu) {
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

    /* ── Tabs ──────────────────────────────────────────────────── */
    var tabs = document.querySelectorAll('.aj-tab');
    var postTypeInput = document.querySelector('.aj-search__form input[name="post_type"]');
    var map = {
        voyage:'st_tours', hebergement:'st_hotel', activites:'st_activity',
        location:'st_rental', transport:'st_cars', guide:'st_tours'
    };
    tabs.forEach(function(tab){
        tab.addEventListener('click', function(){
            tabs.forEach(function(t){ t.classList.remove('aj-tab--on'); });
            tab.classList.add('aj-tab--on');
            if (postTypeInput && map[tab.dataset.tab]) {
                postTypeInput.value = map[tab.dataset.tab];
            }
        });
    });

    /* ── Slider ────────────────────────────────────────────────── */
    var track = document.getElementById('aj-lm-track');
    if (track) {
        var prevBtn = document.querySelector('.aj-arrow--prev');
        var nextBtn = document.querySelector('.aj-arrow--next');

        function scrollAmt(){
            var card = track.querySelector('.aj-card');
            if (!card) return 320;
            var gap = parseFloat(getComputedStyle(track).gap) || 22;
            return card.offsetWidth + gap;
        }

        if (prevBtn) prevBtn.addEventListener('click', function(){
            track.scrollBy({ left: -scrollAmt(), behavior:'smooth' });
        });
        if (nextBtn) nextBtn.addEventListener('click', function(){
            track.scrollBy({ left: scrollAmt(), behavior:'smooth' });
        });
    }
})();
