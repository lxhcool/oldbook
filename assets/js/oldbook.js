(function () {
	'use strict';

	var playIcon = '<svg class="oldbook-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m8 5 11 7-11 7Z"/></svg>';
	var pauseIcon = '<svg class="oldbook-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 4v16M17 4v16"/></svg>';

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

	highlightCodeBlocks();
}());
