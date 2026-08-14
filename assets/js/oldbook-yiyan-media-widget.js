(function (component, $) {
	'use strict';

	var settings = window.oldbookYiyanWidgetSettings || {};
	var MediaWidgetModel = component.MediaWidgetModel;
	var MediaWidgetControl = component.MediaWidgetControl;

	var YiyanWidgetModel = MediaWidgetModel.extend({
		// Keep this in sync with the hidden inputs emitted by WP_Widget_Media::form().
		// The legacy fields are invisible, but still need a schema entry because
		// media-widgets.js synchronizes every .media-widget-instance-property.
		schema: {
			attachment_id: {
				type: 'integer',
				'default': 0,
				media_prop: 'id'
			},
			url: {
				type: 'string',
				'default': ''
			},
			image_id: {
				type: 'integer',
				'default': 0
			},
			image_url: {
				type: 'string',
				'default': ''
			},
			overlay_color: {
				type: 'string',
				'default': '#11201a'
			},
			overlay_opacity: {
				type: 'integer',
				'default': 26
			}
		}
	});
	var YiyanWidgetControl = MediaWidgetControl.extend({
		id_base: 'oldbook_yiyan',
		mime_type: 'image',
		showDisplaySettings: false,
		l10n: {
			add_to_widget: settings.addToWidget || '使用这张图片',
			add_media: settings.addMedia || '上传 / 选择图片'
		},

		events: _.extend({}, MediaWidgetControl.prototype.events, {
			'click .clear-media': 'clearMedia',
			'input .oldbook-yiyan-overlay-color': 'updateOverlayColor',
			'change .oldbook-yiyan-overlay-color': 'updateOverlayColor',
			'input .oldbook-yiyan-overlay-opacity': 'updateOverlayOpacity',
			'change .oldbook-yiyan-overlay-opacity': 'updateOverlayOpacity'
		}),

		initialize: function (options) {
			MediaWidgetControl.prototype.initialize.call(this, options);
			this.$el.closest('.widget-inside, .editwidget').addClass('oldbook-yiyan-widget-inside');

			// Migrate the two fields written by the former custom control in-place.
			if (!this.model.get('attachment_id') && this.model.get('image_id')) {
				this.model.set('attachment_id', this.model.get('image_id'));
			}
			if (!this.model.get('url') && this.model.get('image_url')) {
				this.model.set('url', this.model.get('image_url'));
			}
		},

		render: function () {
			MediaWidgetControl.prototype.render.call(this);

			var colorInput = this.$el.find('.oldbook-yiyan-overlay-color');
			var opacityInput = this.$el.find('.oldbook-yiyan-overlay-opacity');

			if (colorInput.length && colorInput[0] !== document.activeElement) {
				colorInput.val(this.model.get('overlay_color') || '#11201a');
			}
			if (opacityInput.length && opacityInput[0] !== document.activeElement) {
				opacityInput.val(this.model.get('overlay_opacity'));
			}

			this.updateOverlayOutputs();
		},

		updateOverlayColor: function (event) {
			this.model.set('overlay_color', event.currentTarget.value);
			this.updateOverlayOutputs();
		},

		updateOverlayOpacity: function (event) {
			this.model.set('overlay_opacity', parseInt(event.currentTarget.value, 10) || 0);
			this.updateOverlayOutputs();
		},

		updateOverlayOutputs: function () {
			var color = this.model.get('overlay_color') || '#11201a';
			var opacity = parseInt(this.model.get('overlay_opacity'), 10) || 0;

			this.$el.find('.oldbook-yiyan-overlay-color-value').text(color);
			this.$el.find('.oldbook-yiyan-overlay-opacity-value').text(opacity + '%');
		},

		hexToRgba: function (color, opacity) {
			var match = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(color || '');

			if (!match) {
				return 'rgba(17, 32, 26, ' + opacity + ')';
			}

			return 'rgba(' + parseInt(match[1], 16) + ', ' + parseInt(match[2], 16) + ', ' + parseInt(match[3], 16) + ', ' + opacity + ')';
		},

		renderPreview: function () {
			var preview = this.$el.find('.media-widget-preview');
			var attachmentId = this.model.get('attachment_id');
			var imageUrl = this.model.get('url') || this.selectedAttachment.get('url');
			var overlay = this.hexToRgba(
				this.model.get('overlay_color') || '#11201a',
				(Math.max(0, Math.min(100, parseInt(this.model.get('overlay_opacity'), 10) || 0)) / 100)
			);

			if (!attachmentId && !imageUrl) {
				preview.removeClass('populated');
				preview.html(
					'<div class="attachment-media-view">' +
					'<button type="button" class="select-media button-add-media not-selected">' +
					_.escape(settings.addMedia || '上传 / 选择图片') +
					'</button></div>'
				);
				return;
			}

			preview.addClass('populated');
			if (imageUrl) {
				preview.html(
					'<div class="attachment-media-view">' +
					'<img class="attachment-thumb" src="' + _.escape(imageUrl) + '" draggable="false" alt="">' +
					'<span class="oldbook-yiyan-media-preview__veil" style="background-color:' + _.escape(overlay) + '" aria-hidden="true"></span>' +
					'</div>'
				);
			}
		},

		clearMedia: function (event) {
			event.preventDefault();
			this.model.set({
				attachment_id: 0,
				url: '',
				image_id: 0,
				image_url: '',
				error: false
			});
		},

		mapModelToMediaFrameProps: function (modelProps) {
			modelProps = _.omit(_.extend({}, modelProps), 'overlay_color', 'overlay_opacity', 'image_id', 'image_url');

			if (!modelProps.attachment_id && modelProps.image_id) {
				modelProps.attachment_id = modelProps.image_id;
			}
			if (!modelProps.url && modelProps.image_url) {
				modelProps.url = modelProps.image_url;
			}

			return MediaWidgetControl.prototype.mapModelToMediaFrameProps.call(this, modelProps);
		},

		getModelPropsFromMediaFrame: function (mediaFrame) {
			var props = MediaWidgetControl.prototype.getModelPropsFromMediaFrame.call(this, mediaFrame);

			props.image_id = props.attachment_id || 0;
			props.image_url = props.url || '';
			props.overlay_color = this.model.get('overlay_color') || '#11201a';
			props.overlay_opacity = parseInt(this.model.get('overlay_opacity'), 10) || 0;

			return props;
		}
	});

	component.controlConstructors.oldbook_yiyan = YiyanWidgetControl;
	component.modelConstructors.oldbook_yiyan = YiyanWidgetModel;
}(wp.mediaWidgets, jQuery));
