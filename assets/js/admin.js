(function () {
	'use strict';

	function setPickerValue(group, value) {
		var input = document.querySelector('[data-oldbook-picker-input="' + group + '"]');
		var options = document.querySelectorAll('[data-oldbook-picker="' + group + '"]');

		if (input) {
			input.value = value;
		}

		options.forEach(function (option) {
			var selected = option.getAttribute('data-value') === value;

			option.classList.toggle('is-selected', selected);
			option.setAttribute('aria-checked', selected ? 'true' : 'false');
		});
	}

	function syncConditionalFields() {
		var typeInput = document.querySelector('[data-oldbook-picker-input="update-type"]');
		var type = typeInput ? typeInput.value : '';

		document.querySelectorAll('[data-oldbook-type]').forEach(function (section) {
			var types = section.getAttribute('data-oldbook-type').split(' ');
			var visible = types.indexOf(type) !== -1;

			section.classList.toggle('is-visible', visible);
			section.setAttribute('aria-hidden', visible ? 'false' : 'true');
		});
	}

	function syncMediaSource() {
		var sourceInput = document.querySelector('[data-oldbook-picker-input="media-source"]');
		var source = sourceInput ? sourceInput.value : 'external';

		document.querySelectorAll('[data-oldbook-source]').forEach(function (section) {
			var visible = section.getAttribute('data-oldbook-source') === source;

			section.classList.toggle('is-visible', visible);
			section.setAttribute('aria-hidden', visible ? 'false' : 'true');
		});
	}

	function syncRangeOutputs() {
		document.querySelectorAll('[data-oldbook-range]').forEach(function (input) {
			var output = document.querySelector('[data-oldbook-range-output="' + input.id + '"]');

			if (output) {
				var suffix = output.getAttribute('data-oldbook-range-suffix') || '%';
				output.textContent = input.value + suffix;
			}
		});
	}

	function syncGradientFields() {
		var gradientInput = document.querySelector('[data-oldbook-picker-input="cover-gradient"]');
		var gradientFields = document.querySelector('[data-oldbook-gradient-fields]');
		var enabled = gradientInput && '1' === gradientInput.value;

		if (!gradientFields) {
			return;
		}

		gradientFields.classList.toggle('is-enabled', enabled);
		gradientFields.setAttribute('aria-hidden', enabled ? 'false' : 'true');
		gradientFields.querySelectorAll('[data-oldbook-cover-gradient-start], [data-oldbook-cover-gradient-end]').forEach(function (input) {
			input.disabled = !enabled;
		});
	}

	function syncLogoPreview() {
		var input = document.querySelector('#oldbook-site-logo-height');

		if (!input) {
			return;
		}

		document.querySelectorAll('[data-oldbook-logo-preview]').forEach(function (preview) {
			preview.style.setProperty('--oldbook-admin-logo-height', input.value + 'px');
		});
	}

	function setupLogoPicker() {
		var input = document.querySelector('#oldbook-site-logo-file');
		var preview = document.querySelector('[data-oldbook-logo-preview]');

		if (!input || !preview) {
			return;
		}

		input.addEventListener('change', function () {
			var file = input.files && input.files[0];
			var image;
			var objectUrl;

			if (!file || !file.type || file.type.indexOf('image/') !== 0) {
				return;
			}

			if (preview.dataset.oldbookObjectUrl) {
				URL.revokeObjectURL(preview.dataset.oldbookObjectUrl);
			}

			objectUrl = URL.createObjectURL(file);
			preview.dataset.oldbookObjectUrl = objectUrl;
			image = preview.querySelector('img') || document.createElement('img');
			image.src = objectUrl;
			image.alt = '';
			preview.replaceChildren(image);
		});
	}

	function syncCoverPreview() {
		var colorInput = document.querySelector('[data-oldbook-cover-overlay-color]');
		var opacityInput = document.querySelector('[data-oldbook-cover-overlay-opacity]');
		var gradientInput = document.querySelector('[data-oldbook-picker-input="cover-gradient"]');
		var gradientStartInput = document.querySelector('[data-oldbook-cover-gradient-start]');
		var gradientEndInput = document.querySelector('[data-oldbook-cover-gradient-end]');
		var heightInput = document.querySelector('#oldbook-cover-height');
		var colorOutput = document.querySelector('[data-oldbook-cover-overlay-output]');
		var gradientStartOutput = document.querySelector('[data-oldbook-cover-gradient-start-output]');
		var gradientEndOutput = document.querySelector('[data-oldbook-cover-gradient-end-output]');
		var color = colorInput ? colorInput.value : '';
		var gradientStart = gradientStartInput ? gradientStartInput.value : '';
		var gradientEnd = gradientEndInput ? gradientEndInput.value : '';
		var opacity = opacityInput ? Number(opacityInput.value) / 100 : 0;
		var overlay;
		var gradientStartOverlay;
		var gradientEndOverlay;
		var previews = document.querySelectorAll('[data-oldbook-cover-preview]');
		var gradientEnabled = gradientInput && '1' === gradientInput.value;

		previews.forEach(function (preview) {
			if (heightInput) {
				preview.style.setProperty('--oldbook-cover-height', heightInput.value + 'px');
			}
		});

		if (!color || !opacityInput) {
			return;
		}

		if (colorOutput) {
			colorOutput.textContent = color;
		}

		if (gradientStartOutput) {
			gradientStartOutput.textContent = gradientStart;
		}

		if (gradientEndOutput) {
			gradientEndOutput.textContent = gradientEnd;
		}

		gradientStartOverlay = hexToRgba(gradientStart, opacity);
		gradientEndOverlay = hexToRgba(gradientEnd, opacity);

		if (gradientEnabled && gradientStartOverlay && gradientEndOverlay) {
			overlay = 'linear-gradient(to bottom, ' + gradientStartOverlay + ', ' + gradientEndOverlay + ')';
		} else {
			overlay = hexToRgba(color, opacity);
		}

		if (!overlay) {
			return;
		}

		previews.forEach(function (preview) {
			preview.style.setProperty('--oldbook-cover-overlay', overlay);
		});
	}

	function hexToRgba(color, opacity) {
		var match = color.replace('#', '').match(/^([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i);

		if (!match) {
			return '';
		}

		return 'rgba(' + parseInt(match[1], 16) + ', ' + parseInt(match[2], 16) + ', ' + parseInt(match[3], 16) + ', ' + opacity.toFixed(2) + ')';
	}

	function setupLayoutPicker() {
		document.querySelectorAll('[data-oldbook-layout-choice]').forEach(function (option) {
			option.addEventListener('click', function () {
				var left = option.getAttribute('data-oldbook-layout-left');
				var right = option.getAttribute('data-oldbook-layout-right');

				setPickerValue('show-left-sidebar', left);
				setPickerValue('show-right-sidebar', right);

				document.querySelectorAll('[data-oldbook-layout-choice]').forEach(function (item) {
					var selected = item === option;

					item.classList.toggle('is-selected', selected);
					item.setAttribute('aria-checked', selected ? 'true' : 'false');
				});
			});
		});
	}

	document.querySelectorAll('[data-oldbook-picker]').forEach(function (option) {
		option.addEventListener('click', function () {
			var group = option.getAttribute('data-oldbook-picker');
			var value = option.getAttribute('data-value');

			setPickerValue(group, value);

			if ('update-type' === group) {
				syncConditionalFields();
			}

			if ('media-source' === group) {
				syncMediaSource();
			}

			if ('cover-gradient' === group) {
				syncGradientFields();
				syncCoverPreview();
			}
		});

		option.addEventListener('keydown', function (event) {
			var keys = ['ArrowLeft', 'ArrowUp', 'ArrowRight', 'ArrowDown', 'Home', 'End'];
			var group;
			var options;
			var currentIndex;
			var nextIndex;

			if (keys.indexOf(event.key) === -1) {
				return;
			}

			event.preventDefault();
			group = option.getAttribute('data-oldbook-picker');
			options = Array.prototype.slice.call(document.querySelectorAll('[data-oldbook-picker="' + group + '"]'));
			currentIndex = options.indexOf(option);
			nextIndex = currentIndex;

			if ('Home' === event.key || 'ArrowLeft' === event.key || 'ArrowUp' === event.key) {
				nextIndex = 'Home' === event.key ? 0 : Math.max(0, currentIndex - 1);
			}

			if ('End' === event.key || 'ArrowRight' === event.key || 'ArrowDown' === event.key) {
				nextIndex = 'End' === event.key ? options.length - 1 : Math.min(options.length - 1, currentIndex + 1);
			}

			options[nextIndex].focus();
			options[nextIndex].click();
		});
	});

	document.querySelectorAll('[data-oldbook-range]').forEach(function (input) {
		input.addEventListener('input', syncRangeOutputs);
		input.addEventListener('input', syncLogoPreview);
		input.addEventListener('input', syncCoverPreview);
	});

	document.querySelectorAll('[data-oldbook-cover-overlay-color]').forEach(function (input) {
		input.addEventListener('input', syncCoverPreview);
		input.addEventListener('change', syncCoverPreview);
	});

	document.querySelectorAll('[data-oldbook-cover-gradient-start], [data-oldbook-cover-gradient-end]').forEach(function (input) {
		input.addEventListener('input', syncCoverPreview);
		input.addEventListener('change', syncCoverPreview);
	});

	document.querySelectorAll('.oldbook-admin-danger').forEach(function (button) {
		button.addEventListener('click', function (event) {
			if (!window.confirm('确定删除这项内容吗？')) {
				event.preventDefault();
			}
		});
	});

	setupLayoutPicker();
	setupLogoPicker();
	syncConditionalFields();
	syncMediaSource();
	syncGradientFields();
	syncRangeOutputs();
	syncLogoPreview();
	syncCoverPreview();
}());
