(function () {
	function init() {
		var slider = document.getElementById('aji-accordion-slider');
		if (!slider) {
			return;
		}
		var slides = slider.querySelectorAll('.aji-slide');
		var current = 0;
		var direction = 1;
		var timer = null;

		function activate(i) {
			current = i;
			slides.forEach(function (s, j) {
				if (j === i) {
					s.classList.add('aji-slide--active');
				} else {
					s.classList.remove('aji-slide--active');
				}
			});
		}

		function startAuto() {
			clearInterval(timer);
			timer = setInterval(function () {
				if (current >= slides.length - 1) {
					direction = -1;
				} else if (current <= 0) {
					direction = 1;
				}
				activate(current + direction);
			}, 5000);
		}

		slides.forEach(function (s) {
			s.addEventListener('click', function () {
				activate(parseInt(s.getAttribute('data-index'), 10));
			});
		});

		slider.addEventListener('mouseenter', function () {
			clearInterval(timer);
		});
		slider.addEventListener('mouseleave', startAuto);

		activate(0);
		startAuto();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
