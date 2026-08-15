(function () {
	'use strict';

	function stripIconfontFills() {
		document.querySelectorAll('.oldbook-iconfont use').forEach(function (use) {
			var href = use.getAttribute('href') || use.getAttribute('xlink:href');

			if (!href || href.charAt(0) !== '#') {
				return;
			}

			var symbol = document.getElementById(href.slice(1));

			if (!symbol) {
				return;
			}

			symbol.querySelectorAll('[fill]').forEach(function (element) {
				element.removeAttribute('fill');
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', stripIconfontFills);
	} else {
		stripIconfontFills();
	}
}());
