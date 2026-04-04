/**
 * Ajinsafro accordion promo — matches reference behavior:
 * one expanded slide, opacity pane, 5s autoplay ping-pong, pause on hover, click to select.
 */
(function () {
	'use strict';

	var DELAY_MS = 5000;
	var PANE_SHOW_MS = 100;

	function initPromoAccordion(root) {
		if (!root || root.getAttribute('data-ajih-initialized') === '1') {
			return;
		}
		root.setAttribute('data-ajih-initialized', '1');

		var slides = root.querySelectorAll('.ajih-acc__slide');
		var panes = root.querySelectorAll('.ajih-acc__pane');
		var n = slides.length;
		if (!n || panes.length !== n) {
			return;
		}

		var currentIndex = 0;
		var direction = 1;
		var timer = null;
		var paneShowToken = 0;

		function clearTimer() {
			if (timer !== null) {
				clearInterval(timer);
				timer = null;
			}
		}

		function setActive(index) {
			var next = ((index % n) + n) % n;
			currentIndex = next;
			var token = ++paneShowToken;

			for (var i = 0; i < n; i++) {
				var slide = slides[i];
				var pane = panes[i];
				if (i === next) {
					slide.classList.add('ajih-acc__slide--active');
					window.setTimeout(function (p, t) {
						return function () {
							if (t === paneShowToken) {
								p.classList.add('ajih-acc__pane--visible');
							}
						};
					}(pane, token), PANE_SHOW_MS);
				} else {
					slide.classList.remove('ajih-acc__slide--active');
					pane.classList.remove('ajih-acc__pane--visible');
				}
			}
		}

		function step() {
			if (currentIndex >= n - 1) {
				direction = -1;
			} else if (currentIndex <= 0) {
				direction = 1;
			}
			setActive(currentIndex + direction);
		}

		function startAutoplay() {
			clearTimer();
			timer = window.setInterval(step, DELAY_MS);
		}

		for (var j = 0; j < n; j++) {
			(function (idx) {
				slides[idx].addEventListener('click', function () {
					setActive(idx);
					clearTimer();
					startAutoplay();
				});
				slides[idx].addEventListener('keydown', function (e) {
					if (e.key === 'Enter' || e.key === ' ') {
						e.preventDefault();
						setActive(idx);
						clearTimer();
						startAutoplay();
					}
				});
			}(j));
		}

		root.addEventListener('mouseenter', clearTimer);
		root.addEventListener('mouseleave', startAutoplay);
		root.addEventListener('focusin', clearTimer);
		root.addEventListener('focusout', function () {
			if (!root.contains(document.activeElement)) {
				startAutoplay();
			}
		});

		for (var k = 0; k < n; k++) {
			slides[k].setAttribute('tabindex', '0');
		}

		setActive(0);
		startAutoplay();
	}

	function boot() {
		var root = document.getElementById('ajih-promo-accordion');
		if (root) {
			initPromoAccordion(root);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
