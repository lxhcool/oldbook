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

	document.querySelectorAll('.oldbook-admin-danger').forEach(function (button) {
		button.addEventListener('click', function (event) {
			if (!window.confirm('确定删除这项内容吗？')) {
				event.preventDefault();
			}
		});
	});

	syncConditionalFields();
	syncMediaSource();
}());
