(function () {
	'use strict';

	var app = document.querySelector('[data-oldbook-app]');
	var menuToggle = document.querySelector('[data-oldbook-menu-toggle]');
	var menuClose = document.querySelector('[data-oldbook-menu-close]');
	var themeToggle = document.querySelector('[data-oldbook-theme-toggle]');
	var root = document.documentElement;

	function setMenuState(open) {
		if (!app || !menuToggle) {
			return;
		}

		app.classList.toggle('is-menu-open', open);
		menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		menuToggle.setAttribute('aria-label', open ? '关闭导航' : '打开导航');
		if (menuClose) {
			menuClose.setAttribute('aria-hidden', open ? 'false' : 'true');
		}
	}

	function setupThemeToggle() {
		if (!themeToggle) {
			return;
		}

		function applyTheme(theme, persist) {
			var isDark = theme === 'dark';

			root.setAttribute('data-oldbook-theme', isDark ? 'dark' : 'light');
			themeToggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
			themeToggle.setAttribute('aria-label', isDark ? '切换浅色模式' : '切换深色模式');
			themeToggle.setAttribute('title', isDark ? '切换浅色模式' : '切换深色模式');

			if (persist) {
				try {
					window.localStorage.setItem('oldbook-theme', isDark ? 'dark' : 'light');
				} catch (error) {
					// The current theme still applies for this visit.
				}
			}
		}

		var storedTheme = '';
		try {
			storedTheme = window.localStorage.getItem('oldbook-theme') || '';
		} catch (error) {
			storedTheme = '';
		}

		applyTheme(storedTheme === 'dark' ? 'dark' : 'light', false);
		themeToggle.addEventListener('click', function () {
			applyTheme(root.getAttribute('data-oldbook-theme') === 'dark' ? 'light' : 'dark', true);
		});
	}

	function setupYiyanRefresh() {
		var settings = window.oldbookInteractions || {};
		var buttons = document.querySelectorAll('[data-oldbook-yiyan-refresh]');

		if (!buttons.length || !settings.ajaxUrl || !settings.yiyanNonce) {
			return;
		}

		buttons.forEach(function (button) {
			button.addEventListener('click', function () {
				var card = button.closest('.oldbook-yiyan-card');
				var quote = card ? card.querySelector('.oldbook-yiyan-card__quote') : null;

				if (!quote || button.disabled) {
					return;
				}

				button.disabled = true;
				button.classList.add('is-loading');

				var body = new URLSearchParams({
					action: 'oldbook_refresh_yiyan',
					nonce: settings.yiyanNonce
				});

				fetch(settings.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
					},
					body: body.toString()
				})
					.then(function (response) {
						if (!response.ok) {
							throw new Error('刷新失败');
						}

						return response.json();
					})
					.then(function (result) {
						if (!result.success || !result.data || !result.data.text) {
							throw new Error('刷新失败');
						}

						quote.textContent = result.data.text;
					})
					.catch(function () {
						button.classList.add('has-error');
						window.setTimeout(function () {
							button.classList.remove('has-error');
						}, 1200);
					})
					.finally(function () {
						button.disabled = false;
						button.classList.remove('is-loading');
					});
			});
		});
	}

	if (menuToggle) {
		menuToggle.addEventListener('click', function () {
			setMenuState(menuToggle.getAttribute('aria-expanded') !== 'true');
		});
	}

	if (menuClose) {
		menuClose.addEventListener('click', function () {
			setMenuState(false);
		});
	}

	document.querySelectorAll('.oldbook-primary-nav a').forEach(function (link) {
		link.addEventListener('click', function () {
			setMenuState(false);
		});
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') {
			setMenuState(false);
		}
	});

	window.addEventListener('resize', function () {
		if (window.innerWidth > 768) {
			setMenuState(false);
		}
	});

	setupThemeToggle();
	setupYiyanRefresh();
}());
