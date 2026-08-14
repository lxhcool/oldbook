<?php
/**
 * Theme widgets.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

function oldbook_get_yiyan_quote($force_refresh = false) {
	$cache_key = 'oldbook_yiyan_quote';

	if ($force_refresh) {
		delete_transient($cache_key);
	}

	$cached    = get_transient($cache_key);
	$fallback  = array(
		'text'     => __('愿你在自己的节奏里，慢慢抵达。', 'oldbook'),
		'from'     => '',
		'from_who' => '',
		'uuid'     => '',
	);

	if (is_array($cached) && ! empty($cached['text'])) {
		return $cached;
	}

	$response = wp_remote_get(
		'https://v1.hitokoto.cn/?encode=json',
		array(
			'timeout' => 3,
			'headers' => array(
				'Accept'     => 'application/json',
				'User-Agent' => 'oldbook-theme/' . wp_get_theme()->get('Version'),
			),
		)
	);

	if (is_wp_error($response) || 200 !== (int) wp_remote_retrieve_response_code($response)) {
		return $fallback;
	}

	$data = json_decode(wp_remote_retrieve_body($response), true);

	if (! is_array($data) || empty($data['hitokoto'])) {
		return $fallback;
	}

	$quote = array(
		'text'     => sanitize_text_field($data['hitokoto']),
		'from'     => isset($data['from']) ? sanitize_text_field($data['from']) : '',
		'from_who' => isset($data['from_who']) ? sanitize_text_field($data['from_who']) : '',
		'uuid'     => isset($data['uuid']) ? sanitize_text_field($data['uuid']) : '',
	);

	set_transient($cache_key, $quote, 10 * MINUTE_IN_SECONDS);

	return $quote;
}

function oldbook_ajax_refresh_yiyan() {
	check_ajax_referer('oldbook_yiyan', 'nonce');

	$quote = oldbook_get_yiyan_quote(true);

	wp_send_json_success(
		array(
			'text' => $quote['text'],
		)
	);
}
add_action('wp_ajax_oldbook_refresh_yiyan', 'oldbook_ajax_refresh_yiyan');
add_action('wp_ajax_nopriv_oldbook_refresh_yiyan', 'oldbook_ajax_refresh_yiyan');

if (class_exists('WP_Widget_Media') && ! class_exists('Oldbook_Yiyan_Widget')) {
	/**
	 * A compact quote card for the theme sidebars.
	 */
	class Oldbook_Yiyan_Widget extends WP_Widget_Media {
		public function __construct() {
			parent::__construct(
				'oldbook_yiyan',
				__('一言卡片', 'oldbook'),
				array(
					'classname'   => 'oldbook-yiyan-widget',
					'description' => __('调用一言 API 显示短句，可设置背景图片。', 'oldbook'),
					'mime_type'   => 'image',
				)
			);

			$this->l10n = array_merge(
				$this->l10n,
				array(
					'add_to_widget'            => __('使用这张图片', 'oldbook'),
					'add_media'                => __('上传 / 选择图片', 'oldbook'),
					'replace_media'            => __('更换背景图', 'oldbook'),
					'edit_media'               => __('更换背景图', 'oldbook'),
					'media_library_state_multi'  => _n_noop('一言卡片 (%d)', '一言卡片 (%d)', 'oldbook'),
					'media_library_state_single' => __('一言卡片', 'oldbook'),
				)
			);
		}

		/**
		 * Keep only the media properties used by this widget.
		 *
		 * image_id and image_url are retained as hidden compatibility fields for
		 * widget instances created by the previous custom control.
		 */
		public function get_instance_schema() {
			return array(
				'attachment_id' => array(
					'type'        => 'integer',
					'default'     => 0,
					'minimum'     => 0,
					'description' => __('Attachment post ID', 'oldbook'),
					'media_prop'  => 'id',
				),
				'url' => array(
					'type'        => 'string',
					'default'     => '',
					'format'      => 'uri',
					'description' => __('URL to the media file', 'oldbook'),
				),
				'image_id' => array(
					'type'        => 'integer',
					'default'     => 0,
					'minimum'     => 0,
					'description' => __('Legacy attachment ID', 'oldbook'),
				),
				'image_url' => array(
					'type'        => 'string',
					'default'     => '',
					'format'      => 'uri',
					'description' => __('Legacy media URL', 'oldbook'),
				),
				'overlay_color' => array(
					'type'              => 'string',
					'default'           => '#11201a',
					'description'       => __('Overlay color', 'oldbook'),
					'sanitize_callback' => 'sanitize_hex_color',
				),
				'overlay_opacity' => array(
					'type'              => 'integer',
					'default'           => 26,
					'minimum'           => 0,
					'maximum'           => 100,
					'description'       => __('Overlay opacity', 'oldbook'),
					'sanitize_callback' => 'oldbook_sanitize_percentage',
				),
			);
		}

		public function widget($args, $instance) {
			$instance = wp_parse_args($instance, wp_list_pluck($this->get_instance_schema(), 'default'));

			echo $args['before_widget'];
			$this->render_media($instance);
			echo $args['after_widget'];
		}

		public function render_media($instance) {
			$image_id   = isset($instance['attachment_id']) ? absint($instance['attachment_id']) : 0;
			$image_id   = $image_id ? $image_id : (isset($instance['image_id']) ? absint($instance['image_id']) : 0);
			$image_url  = $image_id ? wp_get_attachment_image_url($image_id, 'large') : '';
			$image_url  = $image_url ? $image_url : (isset($instance['url']) ? esc_url_raw($instance['url']) : '');
			$image_url  = $image_url ? $image_url : (isset($instance['image_url']) ? esc_url_raw($instance['image_url']) : '');
			$image_url  = $image_url ? $image_url : oldbook_get_cover_image_url();
			$overlay_color   = sanitize_hex_color(isset($instance['overlay_color']) ? $instance['overlay_color'] : '#11201a');
			$overlay_color   = $overlay_color ? $overlay_color : '#11201a';
			$overlay_opacity = oldbook_sanitize_percentage(isset($instance['overlay_opacity']) ? $instance['overlay_opacity'] : 26);
			$overlay         = oldbook_hex_to_rgba($overlay_color, $overlay_opacity / 100);
			$quote           = oldbook_get_yiyan_quote();
			?>
			<article class="oldbook-yiyan-card" aria-label="<?php esc_attr_e('句子', 'oldbook'); ?>">
				<img class="oldbook-yiyan-card__image" src="<?php echo esc_url($image_url); ?>" alt="" width="900" height="600" loading="lazy">
				<span class="oldbook-yiyan-card__veil" style="<?php echo esc_attr('--oldbook-yiyan-overlay:' . $overlay . ';'); ?>" aria-hidden="true"></span>
				<button class="oldbook-yiyan-card__refresh" type="button" data-oldbook-yiyan-refresh aria-label="<?php esc_attr_e('刷新句子', 'oldbook'); ?>" title="<?php esc_attr_e('刷新句子', 'oldbook'); ?>">
					<?php echo oldbook_iconfont('icon_refresh_linear_light'); ?>
				</button>
				<div class="oldbook-yiyan-card__content">
					<p class="oldbook-yiyan-card__quote"><?php echo esc_html($quote['text']); ?></p>
				</div>
			</article>
			<?php
		}

		/**
		 * Load the core media-widget runtime and this widget's small adapter.
		 */
		public function enqueue_admin_scripts() {
			parent::enqueue_admin_scripts();

			$script_path = get_template_directory() . '/assets/js/oldbook-yiyan-media-widget.js';
			$version     = file_exists($script_path) ? (string) filemtime($script_path) : wp_get_theme()->get('Version');

			wp_enqueue_script(
				'oldbook-yiyan-media-widget',
				get_template_directory_uri() . '/assets/js/oldbook-yiyan-media-widget.js',
				array('media-widgets'),
				$version,
				true
			);
			wp_localize_script(
				'oldbook-yiyan-media-widget',
				'oldbookYiyanWidgetSettings',
				array(
					'addMedia'     => $this->l10n['add_media'],
					'addToWidget'  => $this->l10n['add_to_widget'],
				)
			);
		}

		/**
		 * Render the core-style control template without title/content fields.
		 */
		public function render_control_template_scripts() {
			?>
			<script type="text/html" id="tmpl-widget-media-<?php echo esc_attr($this->id_base); ?>-control">
				<# var elementIdPrefix = 'oldbookYiyan_' + String( Math.random() ).replace('.', '_'); #>
				<div class="oldbook-yiyan-media-control">
					<p class="oldbook-yiyan-media-label"><?php esc_html_e('背景图片（可选）', 'oldbook'); ?></p>
					<div class="media-widget-preview <?php echo esc_attr($this->id_base); ?>">
						<div class="attachment-media-view">
							<button type="button" class="select-media button-add-media not-selected">
								<?php echo esc_html($this->l10n['add_media']); ?>
							</button>
						</div>
					</div>
					<p class="media-widget-buttons">
						<button type="button" class="button change-media select-media selected">
							<?php echo esc_html($this->l10n['replace_media']); ?>
						</button>
						<button type="button" class="button-link-delete clear-media selected">
							<?php esc_html_e('移除', 'oldbook'); ?>
						</button>
					</p>

					<div class="oldbook-yiyan-overlay-settings">
						<div class="oldbook-yiyan-overlay-field">
							<label for="{{ elementIdPrefix }}overlayColor"><?php esc_html_e('蒙层颜色', 'oldbook'); ?></label>
							<div class="oldbook-yiyan-color-control">
								<input id="{{ elementIdPrefix }}overlayColor" class="oldbook-yiyan-overlay-color" type="color" value="{{ data.overlay_color }}">
								<code class="oldbook-yiyan-overlay-color-value">{{ data.overlay_color }}</code>
							</div>
						</div>
						<div class="oldbook-yiyan-overlay-field">
							<label for="{{ elementIdPrefix }}overlayOpacity">
								<?php esc_html_e('蒙层不透明度', 'oldbook'); ?>
								<output class="oldbook-yiyan-overlay-opacity-value">{{ data.overlay_opacity }}%</output>
							</label>
							<input id="{{ elementIdPrefix }}overlayOpacity" class="oldbook-yiyan-overlay-opacity" type="range" value="{{ data.overlay_opacity }}" min="0" max="100" step="1">
						</div>
					</div>
				</div>
			</script>
			<?php
		}

		public function update($new_instance, $old_instance) {
			// Migrate data from the earlier custom control when an old instance is saved.
			if (empty($new_instance['attachment_id']) && ! empty($new_instance['image_id'])) {
				$new_instance['attachment_id'] = $new_instance['image_id'];
			}
			if (empty($new_instance['url']) && ! empty($new_instance['image_url'])) {
				$new_instance['url'] = $new_instance['image_url'];
			}

			$instance              = parent::update($new_instance, $old_instance);
			$instance['image_id']  = isset($instance['attachment_id']) ? absint($instance['attachment_id']) : 0;
			$instance['image_url'] = isset($instance['url']) ? esc_url_raw($instance['url']) : '';

			return $instance;
		}
	}
}

function oldbook_register_custom_widgets() {
	if (class_exists('Oldbook_Yiyan_Widget')) {
		register_widget('Oldbook_Yiyan_Widget');
	}
}
add_action('widgets_init', 'oldbook_register_custom_widgets', 20);

function oldbook_enqueue_widget_assets($hook_suffix) {
	if (! in_array($hook_suffix, array('widgets.php', 'customize.php'), true)) {
		return;
	}

	$theme = wp_get_theme();
	$asset_mtimes = array_filter(
		array_map(
			'filemtime',
			array(
				get_template_directory() . '/assets/css/widgets-admin.css',
				get_template_directory() . '/assets/js/oldbook-yiyan-media-widget.js',
			)
		)
	);
	$asset_version = $asset_mtimes ? (string) max($asset_mtimes) : $theme->get('Version');

	wp_enqueue_style(
		'oldbook-widgets-admin',
		get_template_directory_uri() . '/assets/css/widgets-admin.css',
		array(),
		$asset_version
	);
}
add_action('admin_enqueue_scripts', 'oldbook_enqueue_widget_assets');
