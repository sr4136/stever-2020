/**
 * Quotes Page Slideshow
 */

/**
 * HSL Color Calculations
 * JSFiddle: https://jsfiddle.net/sr4136/9k7dmhLe/
 * Based on: https://stackoverflow.com/a/36688245/2203220
 */
function calcColor(min, max, val) {
	var minHue = 300;
	var maxHue = 0;
	var denominator = max - min;
	var curPercent = denominator === 0 ? 0 : (val - min) / denominator;
	return 'hsl(' + ((curPercent * (maxHue - minHue)) + minHue) + ',100%,50%)';
}

/**
 * Shuffles array in place.
 * From: https://stackoverflow.com/a/6274381
 */
function shuffle(items) {
	var j;
	var x;
	var i;

	for (i = items.length - 1; i > 0; i--) {
		j = Math.floor(Math.random() * (i + 1));
		x = items[i];
		items[i] = items[j];
		items[j] = x;
	}

	return items;
}

function shouldIgnoreKeydownTarget(target) {
	if (!target || !target.tagName) {
		return false;
	}

	var tagName = target.tagName.toLowerCase();
	if (tagName === 'input' || tagName === 'textarea' || tagName === 'select' || tagName === 'button') {
		return true;
	}

	return target.closest('[contenteditable="true"]') !== null;
}

(function () {
	var params = new URLSearchParams(window.location.search);
	var orderby = params.get('orderby');
	var layout = params.get('layout');
	var isSlideshowLayout = layout === 'slideshow';

	if (isSlideshowLayout) {
		document.documentElement.classList.add('quotes-slideshow');
	}

	window.addEventListener('load', function () {
		var quotes = Array.prototype.slice.call(document.querySelectorAll('article'));
		var quotesContainer;
		var quoteCount;
		var colorsArray = [];
		var main;
		var progressWrap;
		var progress;
		var slideTimeout = null;
		var isPaused = false;
		var currentSlideDuration = 0;
		var remainingSlideTime = 0;
		var slideStartTime = 0;

		if ((orderby === 'random' || isSlideshowLayout) && quotes.length) {
			quotesContainer = quotes[0].parentNode;
			shuffle(quotes);
			quotes.forEach(function (quote) {
				quotesContainer.appendChild(quote);
			});
		}

		quotes = Array.prototype.slice.call(document.querySelectorAll('article'));
		quoteCount = quotes.length;
		if (!quoteCount) {
			return;
		}

		for (var i = 0; i <= quoteCount; i++) {
			colorsArray.push(calcColor(0, quoteCount, i));
		}
		shuffle(colorsArray);

		quotes.forEach(function (quote, quoteIndex) {
			var quoteColor = colorsArray[quoteIndex];
			var blockquote = quote.querySelector('blockquote');

			quote.dataset.slideColor = quoteColor;
			if (blockquote) {
				blockquote.style.borderLeftColor = quoteColor;
			}
		});

		if (!isSlideshowLayout) {
			return;
		}

		main = document.getElementById('main') || document.body;
		progressWrap = document.createElement('div');
		progressWrap.className = 'quotes-progress-indicator';
		progress = document.createElement('span');
		progressWrap.appendChild(progress);
		main.appendChild(progressWrap);

		function wrapIndex(index) {
			if (index < 0) {
				return quoteCount - 1;
			}
			if (index >= quoteCount) {
				return 0;
			}
			return index;
		}

		function getActiveIndex() {
			for (var idx = 0; idx < quoteCount; idx++) {
				if (quotes[idx].classList.contains('active')) {
					return idx;
				}
			}
			return 0;
		}

		function getSlideTime(index) {
			var slideTime = parseInt(quotes[index].dataset.slidetime, 10);
			if (!slideTime || slideTime < 1) {
				slideTime = 7000;
			}
			return slideTime;
		}

		function clearSlideTimer() {
			if (slideTimeout) {
				window.clearTimeout(slideTimeout);
				slideTimeout = null;
			}
		}

		function setProgress(color, widthPercent, withTransition, duration) {
			progress.style.background = color;
			progress.style.transition = withTransition ? 'width ' + duration + 'ms linear' : 'none';
			progress.style.width = widthPercent;
		}

		function scheduleSlideAdvance() {
			var timerDuration = Math.max(0, remainingSlideTime || currentSlideDuration);
			clearSlideTimer();
			if (isPaused) {
				return;
			}

			slideStartTime = Date.now();
			setProgress(progress.style.background, progress.style.width || '0%', false, 0);
			window.requestAnimationFrame(function () {
				setProgress(progress.style.background, '100%', true, timerDuration);
			});

			slideTimeout = window.setTimeout(function () {
				goToSlide(getActiveIndex() + 1);
			}, timerDuration);
		}

		function goToSlide(index) {
			var nextIndex = wrapIndex(index);
			var nextQuote = quotes[nextIndex];
			var progressColor = nextQuote.dataset.slideColor || colorsArray[nextIndex] || colorsArray[0];

			quotes.forEach(function (quote) {
				quote.classList.remove('active');
			});
			nextQuote.classList.add('active');

			currentSlideDuration = getSlideTime(nextIndex);
			remainingSlideTime = currentSlideDuration;

			setProgress(progressColor, '0%', false, 0);

			if (!isPaused) {
				scheduleSlideAdvance();
			}
		}

		function pauseSlideshow() {
			var elapsed;
			var progressPct;

			if (isPaused) {
				return;
			}

			isPaused = true;
			clearSlideTimer();

			elapsed = Date.now() - slideStartTime;
			remainingSlideTime = Math.max(0, remainingSlideTime - elapsed);
			progressPct = currentSlideDuration > 0 ? ((currentSlideDuration - remainingSlideTime) / currentSlideDuration) * 100 : 0;
			setProgress(progress.style.background, progressPct + '%', false, 0);
		}

		function resumeSlideshow() {
			if (!isPaused) {
				return;
			}

			isPaused = false;
			if (remainingSlideTime <= 0) {
				goToSlide(getActiveIndex() + 1);
				return;
			}

			scheduleSlideAdvance();
		}

		document.addEventListener('keydown', function (event) {
			if (shouldIgnoreKeydownTarget(event.target)) {
				return;
			}

			if (event.key === 'ArrowRight') {
				event.preventDefault();
				goToSlide(getActiveIndex() + 1);
				return;
			}

			if (event.key === 'ArrowLeft') {
				event.preventDefault();
				goToSlide(getActiveIndex() - 1);
				return;
			}

			if (event.key === ' ' || event.key === 'Spacebar' || event.key === 'Space') {
				event.preventDefault();
				if (isPaused) {
					resumeSlideshow();
				} else {
					pauseSlideshow();
				}
			}
		});

		goToSlide(0);
	});
})();