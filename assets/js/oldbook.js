(function () {
	'use strict';

	var playIcon = '<svg class="oldbook-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m8 5 11 7-11 7Z"/></svg>';
	var pauseIcon = '<svg class="oldbook-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 4v16M17 4v16"/></svg>';
	var moonIcon = '<svg class="oldbook-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>';
	var sunIcon = '<svg class="oldbook-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>';
	var menuIcon = '<svg class="oldbook-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 7h16M4 12h16M4 17h16"/></svg>';
	var closeIcon = '<svg class="oldbook-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M18 6 6 18M6 6l12 12"/></svg>';

	var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function highlightCodeBlocks() {
		if (!window.hljs) {
			return;
		}

		document.querySelectorAll('.entry-content pre code').forEach(function (block) {
			window.hljs.highlightElement(block);
		});
	}

	function formatTime(seconds) {
		if (!isFinite(seconds) || seconds < 0) {
			return '--:--';
		}

		var minutes = Math.floor(seconds / 60);
		var remainder = Math.floor(seconds % 60);

		return minutes + ':' + (remainder < 10 ? '0' : '') + remainder;
	}

	function updateToggle(toggle, playing, type) {
		var label = playing ? (type === 'video' ? '暂停视频' : '暂停音频') : (type === 'video' ? '播放视频' : '播放音频');

		toggle.innerHTML = playing ? pauseIcon : playIcon;
		toggle.setAttribute('aria-label', label);
		toggle.setAttribute('title', label);
	}

	function pauseOtherPlayers(currentMedia) {
		document.querySelectorAll('[data-oldbook-player] .oldbook-player__media').forEach(function (media) {
			if (media !== currentMedia && !media.paused) {
				media.pause();
			}
		});
	}

	function requestInteraction(data) {
		var config = window.oldbookInteractions || {};

		if (!config.ajaxUrl) {
			return Promise.reject(new Error('互动服务暂不可用'));
		}

		return fetch(config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: data
		}).then(function (response) {
			return response.json();
		});
	}

	function setInteractionStatus(container, message, type) {
		if (!container) {
			return;
		}

		var status = container.querySelector('[data-oldbook-interaction-status]');

		if (!status) {
			return;
		}

		status.textContent = message || '';
		status.classList.toggle('is-error', 'error' === type);
		status.classList.toggle('is-success', 'success' === type);
	}

	function setCommentsOpen(toggle, panel, open) {
		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		toggle.setAttribute('aria-label', open ? '收起评论' : '查看评论');
		toggle.setAttribute('title', open ? '收起评论' : '查看评论');
		panel.classList.toggle('is-collapsed', !open);
	}

	function setupThemeToggle() {
		var toggle = document.querySelector('[data-oldbook-theme-toggle]');
		var root = document.documentElement;

		if (!toggle) {
			return;
		}

		var icon = toggle.querySelector('[data-oldbook-theme-icon]');

		function applyTheme(theme, persist) {
			var isDark = 'dark' === theme;
			var nextLabel = isDark ? '浅色模式' : '深色模式';

			root.setAttribute('data-oldbook-theme', isDark ? 'dark' : 'light');
			toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
			toggle.setAttribute('aria-label', nextLabel);
			toggle.setAttribute('title', nextLabel);

			if (icon) {
				icon.innerHTML = isDark ? sunIcon : moonIcon;
			}

			if (persist) {
				try {
					window.localStorage.setItem('oldbook-theme', isDark ? 'dark' : 'light');
				} catch (error) {
					// The current theme still applies for this visit.
				}
			}
		}

		applyTheme('dark' === root.getAttribute('data-oldbook-theme') ? 'dark' : 'light', false);
		toggle.addEventListener('click', function () {
			applyTheme('dark' === root.getAttribute('data-oldbook-theme') ? 'light' : 'dark', true);
		});
	}

	function setupSearchPanel() {
		var toggle = document.querySelector('[data-oldbook-search-toggle]');
		var panel = document.getElementById('oldbook-searchbar');

		if (!toggle || !panel) {
			return;
		}

		var field = panel.querySelector('input[type="search"], input[name="s"]');

		function setOpen(open, focusField) {
			toggle.classList.toggle('is-active', open);
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			panel.classList.toggle('is-open', open);

			if (open && focusField && field) {
				window.setTimeout(function () {
					field.focus();
				}, reducedMotion ? 0 : 240);
			}

			if (!open && document.activeElement && document.activeElement.closest('[data-oldbook-searchbar]')) {
				toggle.focus();
			}
		}

		toggle.addEventListener('click', function () {
			setOpen('true' !== toggle.getAttribute('aria-expanded'), true);
		});

		document.addEventListener('keydown', function (event) {
			if ('Escape' === event.key && panel.classList.contains('is-open')) {
				setOpen(false, false);
			}
		});

		document.addEventListener('click', function (event) {
			if (panel.classList.contains('is-open') && !event.target.closest('.oldbook-topbar')) {
				setOpen(false, false);
			}
		});
	}

	function setupDrawer() {
		var toggle = document.querySelector('[data-oldbook-menu-toggle]');
		var close = document.querySelector('[data-oldbook-menu-close]');
		var drawer = document.getElementById('oldbook-drawer');
		var overlay = document.querySelector('[data-oldbook-overlay]');

		if (!toggle || !drawer) {
			return;
		}

		var firstLink = drawer.querySelector('a');

		function setOpen(open, focusContent) {
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			drawer.classList.toggle('is-open', open);
			document.body.style.overflow = open ? 'hidden' : '';

			if (overlay) {
				overlay.classList.toggle('is-visible', open);
			}

			if (open && focusContent && firstLink) {
				window.setTimeout(function () {
					firstLink.focus();
				}, reducedMotion ? 0 : 260);
			}

			if (!open && document.activeElement && document.activeElement.closest('[data-oldbook-drawer]')) {
				toggle.focus();
			}
		}

		toggle.addEventListener('click', function () {
			setOpen('true' !== toggle.getAttribute('aria-expanded'), true);
		});

		if (close) {
			close.addEventListener('click', function () {
				setOpen(false, false);
			});
		}

		if (overlay) {
			overlay.addEventListener('click', function () {
				setOpen(false, false);
			});
		}

		document.addEventListener('keydown', function (event) {
			if ('Escape' === event.key && drawer.classList.contains('is-open')) {
				setOpen(false, false);
			}
		});

		drawer.addEventListener('click', function (event) {
			if (event.target.closest('a')) {
				setOpen(false, false);
			}
		});
	}

	function setupInteractions() {
		var config = window.oldbookInteractions || {};

		document.querySelectorAll('[data-oldbook-comment-toggle]').forEach(function (toggle) {
			var panelId = toggle.getAttribute('aria-controls');
			var panel = panelId ? document.getElementById(panelId) : null;

			if (!panel) {
				return;
			}

			toggle.addEventListener('click', function () {
				setCommentsOpen(toggle, panel, 'true' !== toggle.getAttribute('aria-expanded'));
			});
		});

		document.querySelectorAll('[data-oldbook-like]').forEach(function (button) {
			button.addEventListener('click', function () {
				if (button.disabled) {
					return;
				}

				var social = button.closest('.oldbook-update__social');
				var data = new FormData();
				var count = button.querySelector('[data-oldbook-like-count]');

				data.append('action', 'oldbook_toggle_like');
				data.append('nonce', config.likeNonce || '');
				data.append('post_id', button.getAttribute('data-post-id') || '');
				button.disabled = true;
				button.setAttribute('aria-busy', 'true');
				setInteractionStatus(social, '处理中...', '');

				requestInteraction(data).then(function (payload) {
					if (!payload.success) {
						throw new Error(payload.data && payload.data.message ? payload.data.message : '点赞没有完成');
					}

					var liked = Boolean(payload.data.liked);
					button.classList.toggle('is-liked', liked);
					button.setAttribute('aria-pressed', liked ? 'true' : 'false');
					button.setAttribute('aria-label', liked ? '取消点赞' : '点赞');
					button.setAttribute('title', liked ? '取消点赞' : '点赞');

					if (count) {
						count.textContent = Number(payload.data.count || 0).toLocaleString();
					}

					setInteractionStatus(social, '', '');
				}).catch(function (error) {
					setInteractionStatus(social, error.message, 'error');
				}).then(function () {
					button.disabled = false;
					button.removeAttribute('aria-busy');
				});
			});
		});

		document.querySelectorAll('[data-oldbook-comment-form]').forEach(function (form) {
			form.addEventListener('submit', function (event) {
				event.preventDefault();

				var social = form.closest('.oldbook-update__social');
				var panel = social ? social.querySelector('[data-oldbook-comments]') : null;
				var toggle = social ? social.querySelector('[data-oldbook-comment-toggle]') : null;
				var list = form.closest('.oldbook-update__comments-inner').querySelector('[data-oldbook-comment-list]');
				var count = toggle ? toggle.querySelector('[data-oldbook-comment-count]') : null;
				var submit = form.querySelector('[type="submit"]');
				var status = form.querySelector('[data-oldbook-comment-status]');
				var data = new FormData(form);

				data.set('nonce', config.commentNonce || data.get('nonce') || '');
				submit.disabled = true;
				submit.setAttribute('aria-busy', 'true');
				status.textContent = '发送中...';
				status.classList.remove('is-error', 'is-success');

				requestInteraction(data).then(function (payload) {
					if (!payload.success) {
						throw new Error(payload.data && payload.data.message ? payload.data.message : '评论没有发布');
					}

					if (payload.data.html && list) {
						list.insertAdjacentHTML('beforeend', payload.data.html);
					}

					if (count) {
						count.textContent = Number(payload.data.count || 0).toLocaleString();
					}

					if (toggle && panel) {
						setCommentsOpen(toggle, panel, true);
					}

					form.reset();
					status.textContent = payload.data.message || '评论已提交。';
					status.classList.add('is-success');
				}).catch(function (error) {
					status.textContent = error.message;
					status.classList.add('is-error');
				}).then(function () {
					submit.disabled = false;
					submit.removeAttribute('aria-busy');
				});
			});
		});
	}

	document.querySelectorAll('[data-oldbook-player]').forEach(function (player) {
		var media = player.querySelector('.oldbook-player__media');
		var toggles = player.querySelectorAll('[data-oldbook-player-toggle]');
		var progress = player.querySelector('[data-oldbook-player-progress]');
		var current = player.querySelector('[data-oldbook-player-current]');
		var duration = player.querySelector('[data-oldbook-player-duration]');
		var state = player.querySelector('[data-oldbook-player-state]');
		var type = media && media.tagName.toLowerCase() === 'video' ? 'video' : 'audio';
		var loading = false;

		if (!media) {
			return;
		}

		function refresh() {
			var percentage = media.duration ? (media.currentTime / media.duration) * 100 : 0;

			if (progress) {
				progress.value = percentage;
			}

			if (current) {
				current.textContent = formatTime(media.currentTime);
			}

			if (duration) {
				duration.textContent = formatTime(media.duration);
			}
		}

		function setLoading(nextLoading) {
			loading = nextLoading;
			player.classList.toggle('is-loading', loading);
			player.setAttribute('aria-busy', loading ? 'true' : 'false');
		}

		function refreshState() {
			var playing = !media.paused && !media.ended;

			toggles.forEach(function (toggle) {
				updateToggle(toggle, playing, type);
			});

			if (state) {
				state.textContent = loading ? '加载中' : (playing ? '播放中' : (media.ended ? '播放完毕' : '就绪'));
			}
		}

		toggles.forEach(function (toggle) {
			toggle.addEventListener('click', function () {
				if (media.paused || media.ended) {
					pauseOtherPlayers(media);
					media.play().catch(function () {});
				} else {
					media.pause();
				}
			});
		});

		if (progress) {
			progress.addEventListener('input', function () {
				if (media.duration) {
					media.currentTime = (Number(progress.value) / 100) * media.duration;
				}
			});
		}

		media.addEventListener('loadedmetadata', refresh);
		media.addEventListener('timeupdate', refresh);
		media.addEventListener('loadstart', function () {
			setLoading(true);
			refreshState();
		});
		media.addEventListener('waiting', function () {
			setLoading(true);
			refreshState();
		});
		media.addEventListener('stalled', function () {
			setLoading(true);
			refreshState();
		});
		media.addEventListener('loadeddata', function () {
			setLoading(false);
			refreshState();
		});
		media.addEventListener('canplay', function () {
			setLoading(false);
			refreshState();
		});
		media.addEventListener('playing', function () {
			setLoading(false);
			refreshState();
		});
		media.addEventListener('play', refreshState);
		media.addEventListener('pause', refreshState);
		media.addEventListener('ended', function () {
			refresh();
			refreshState();
		});
		media.addEventListener('error', function () {
			setLoading(false);
			if (state) {
				state.textContent = '暂时无法播放';
			}
		});

		setLoading(media.readyState < 3);
		refresh();
		refreshState();
	});

	setupThemeToggle();
	setupSearchPanel();
	setupDrawer();
	setupInteractions();
	highlightCodeBlocks();
}());
