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

	function setupUpdateInteractions() {
		var settings = window.oldbookInteractions || {};

		if (!settings.ajaxUrl) {
			return;
		}

		function sendForm(body) {
			return fetch(settings.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
				},
				body: body.toString()
			}).then(function (response) {
				if (!response.ok) {
					throw new Error('请求失败');
				}

				return response.json();
			});
		}

		document.querySelectorAll('[data-oldbook-like]').forEach(function (button) {
			button.addEventListener('click', function () {
				if (button.disabled) {
					return;
				}

				var card = button.closest('.oldbook-feed-card');
				var status = card ? card.querySelector('[data-oldbook-interaction-status]') : null;
				var countEl = button.querySelector('[data-oldbook-like-count]');

				if (!settings.likeNonce) {
					return;
				}

				button.disabled = true;

				sendForm(new URLSearchParams({
					action: 'oldbook_toggle_like',
					nonce: settings.likeNonce,
					post_id: button.getAttribute('data-post-id')
				}))
					.then(function (result) {
						if (!result.success || !result.data) {
							throw new Error((result.data && result.data.message) || '点赞失败');
						}

						var liked = !!result.data.liked;

						button.classList.toggle('is-liked', liked);
						button.setAttribute('aria-pressed', liked ? 'true' : 'false');

						if (countEl) {
							countEl.textContent = result.data.count;
						}

						if (status) {
							status.textContent = liked ? '已点赞' : '已取消点赞';
							window.clearTimeout(status._oldbookTimer);
							status._oldbookTimer = window.setTimeout(function () {
								status.textContent = '';
							}, 2000);
						}
					})
					.catch(function (error) {
						if (status) {
							status.textContent = error.message;
							window.clearTimeout(status._oldbookTimer);
							status._oldbookTimer = window.setTimeout(function () {
								status.textContent = '';
							}, 2000);
						}
					})
					.finally(function () {
						button.disabled = false;
					});
			});
		});

		document.querySelectorAll('[data-oldbook-comment-toggle]').forEach(function (button) {
			button.addEventListener('click', function () {
				var target = document.getElementById(button.getAttribute('aria-controls'));

				if (!target) {
					return;
				}

				var opening = target.classList.contains('is-collapsed');

				document.querySelectorAll('[data-oldbook-comments]').forEach(function (other) {
					if (other !== target) {
						other.classList.add('is-collapsed');

						var otherButton = document.querySelector('[data-oldbook-comment-toggle][aria-controls="' + other.id + '"]');

						if (otherButton) {
							otherButton.setAttribute('aria-expanded', 'false');
						}
					}
				});

				target.classList.toggle('is-collapsed', !opening);
				button.setAttribute('aria-expanded', opening ? 'true' : 'false');
			});
		});

		document.querySelectorAll('[data-oldbook-comment-form]').forEach(function (form) {
			form.addEventListener('submit', function (event) {
				event.preventDefault();

				var statusEl = form.querySelector('[data-oldbook-comment-status]');
				var textarea = form.querySelector('textarea[name="content"]');
				var submitBtn = form.querySelector('button[type="submit"]');
				var content = textarea ? textarea.value.trim() : '';

				if (!content) {
					if (statusEl) {
						statusEl.textContent = '请输入内容';
					}
					return;
				}

				var body = new URLSearchParams(new FormData(form));
				body.set('action', 'oldbook_add_comment');

				if (submitBtn) {
					submitBtn.disabled = true;
				}

				sendForm(body)
					.then(function (result) {
						if (!result.success) {
							throw new Error((result.data && result.data.message) || '评论失败');
						}

						if (statusEl) {
							statusEl.textContent = result.data.message || '已发送';
						}

						var commentsWrap = form.closest('.oldbook-update__comments');

						if (result.data.approved && result.data.html && commentsWrap) {
							var list = commentsWrap.querySelector('[data-oldbook-comment-list]');

							if (list) {
								if (list.tagName === 'P') {
									var freshList = document.createElement('ol');

									freshList.className = 'oldbook-comment-list';
									freshList.setAttribute('data-oldbook-comment-list', '');
									list.replaceWith(freshList);
									list = freshList;
								}

								list.insertAdjacentHTML('beforeend', result.data.html);
							}
						}

						var card = form.closest('.oldbook-feed-card');

						if (card) {
							var countEl = card.querySelector('[data-oldbook-comment-count]');

							if (countEl && typeof result.data.count !== 'undefined') {
								countEl.textContent = result.data.count;
							}
						}

						if (commentsWrap) {
							var titleCount = commentsWrap.querySelector('[data-oldbook-comments-title-count]');

							if (titleCount && typeof result.data.count !== 'undefined') {
								titleCount.textContent = '| ' + result.data.count + ' 条评论';
							}
						}

						if (textarea) {
							textarea.value = '';
						}
					})
					.catch(function (error) {
						if (statusEl) {
							statusEl.textContent = error.message;
						}
					})
					.finally(function () {
						if (submitBtn) {
							submitBtn.disabled = false;
						}
					});
			});
		});
	}

	function setupScrollRadius() {
		var appShell = document.querySelector('.oldbook-app');

		if (!appShell) {
			return;
		}

		function onScroll() {
			var scrolled = (window.scrollY || document.documentElement.scrollTop) > 0;
			appShell.classList.toggle('is-scrolled', scrolled);
		}

		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	window.addEventListener('resize', function () {
		if (window.innerWidth > 768) {
			setMenuState(false);
		}
	});

	setupThemeToggle();
	setupYiyanRefresh();
	setupUpdateInteractions();
	setupScrollRadius();
}());
