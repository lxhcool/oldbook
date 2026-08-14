(function () {
	'use strict';

	var mediaFrame;
	var activeField;

	function markWidgetDirty(input) {
		var widget = input && input.closest('.widget');

		if (!widget) {
			return;
		}

		widget.classList.add('widget-dirty');

		if (window.wpWidgets && window.wpWidgets.dirtyWidgets) {
			var widgetId = widget.id.replace(/^widget-\d+_/, '');
			window.wpWidgets.dirtyWidgets[widgetId] = true;
		}

		var saveButton = widget.querySelector('.widget-control-save');

		if (saveButton) {
			saveButton.disabled = false;
			saveButton.value = '保存';
			saveButton.removeAttribute('aria-disabled');
		}
	}

	function notifyChanged(input) {
		if (!input) {
			return;
		}

		if (window.jQuery) {
			window.jQuery(input).trigger('input').trigger('change');
		} else {
			input.dispatchEvent(new Event('input', { bubbles: true }));
			input.dispatchEvent(new Event('change', { bubbles: true }));
		}

		markWidgetDirty(input);
	}

	function renderPreview(field, attachment) {
		var idInput = field.querySelector('[data-oldbook-widget-media-id]');
		var urlInput = field.querySelector('[data-oldbook-widget-media-url]');
		var preview = field.querySelector('[data-oldbook-widget-media-preview]');
		var removeButton = field.querySelector('[data-oldbook-widget-media-remove]');

		if (!idInput || !urlInput || !preview) {
			return;
		}

		var sizes = attachment.sizes || {};
		var previewSize = sizes.medium || sizes.large || sizes.full || {};
		var attachmentId = attachment.id || attachment.ID || '';
		var attachmentUrl = attachment.url || previewSize.url || '';

		idInput.value = attachmentId;
		urlInput.value = attachmentUrl;
		notifyChanged(idInput);
		notifyChanged(urlInput);
		preview.innerHTML = '';

		if (attachmentUrl) {
			var image = document.createElement('img');
			image.src = attachmentUrl;
			image.alt = '';
			preview.appendChild(image);
		} else {
			preview.textContent = '已选择图片，保存后显示';
		}

		if (removeButton) {
			removeButton.hidden = false;
		}
	}

	function clearPreview(field) {
		var idInput = field.querySelector('[data-oldbook-widget-media-id]');
		var urlInput = field.querySelector('[data-oldbook-widget-media-url]');
		var preview = field.querySelector('[data-oldbook-widget-media-preview]');
		var removeButton = field.querySelector('[data-oldbook-widget-media-remove]');

		if (idInput) {
			idInput.value = '';
			notifyChanged(idInput);
		}

		if (urlInput) {
			urlInput.value = '';
			notifyChanged(urlInput);
		}

		if (preview) {
			preview.textContent = '留空使用站点封面';
		}

		if (removeButton) {
			removeButton.hidden = true;
		}
	}

	document.addEventListener('click', function (event) {
		var target = event.target;

		if (target && target.nodeType !== 1) {
			target = target.parentElement;
		}

		if (!target || !target.closest) {
			return;
		}

		var selectButton = target.closest('[data-oldbook-widget-media-select]');
		var removeButton = target.closest('[data-oldbook-widget-media-remove]');

		if (selectButton) {
			event.preventDefault();
			activeField = selectButton.closest('[data-oldbook-widget-media-field]');

			if (!activeField || !window.wp || !window.wp.media) {
				return;
			}

			if (!mediaFrame) {
				mediaFrame = window.wp.media({
					frame: 'select',
					title: '选择一言背景图',
					button: { text: '使用这张图片' },
					library: { type: 'image' },
					multiple: false
				});

				mediaFrame.on('select', function () {
					var selection = mediaFrame.state().get('selection').first();

					if (selection && activeField) {
						renderPreview(activeField, selection.toJSON());
					}
				});
			}
			mediaFrame.open();
		}

		if (removeButton) {
			event.preventDefault();
			var field = removeButton.closest('[data-oldbook-widget-media-field]');

			if (field) {
				clearPreview(field);
			}
		}
	});
}());
