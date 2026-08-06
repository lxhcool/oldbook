<?php
/**
 * Independent oldbook management screens.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

function oldbook_register_admin_pages() {
	add_menu_page(
		__('oldbook', 'oldbook'),
		__('oldbook', 'oldbook'),
		'edit_posts',
		'oldbook',
		'oldbook_render_updates_admin',
		'dashicons-book-alt',
		25
	);

	add_submenu_page(
		'oldbook',
		__('全部动态', 'oldbook'),
		__('动态', 'oldbook'),
		'edit_posts',
		'oldbook',
		'oldbook_render_updates_admin'
	);

	add_submenu_page(
		'oldbook',
		__('发布动态', 'oldbook'),
		__('发布动态', 'oldbook'),
		'edit_posts',
		'oldbook-publish',
		'oldbook_render_update_form'
	);

	add_submenu_page(
		'oldbook',
		__('全部链接', 'oldbook'),
		__('链接', 'oldbook'),
		'edit_posts',
		'oldbook-links',
		'oldbook_render_links_admin'
	);

	add_submenu_page(
		'oldbook',
		__('添加链接', 'oldbook'),
		__('添加链接', 'oldbook'),
		'edit_posts',
		'oldbook-link-add',
		'oldbook_render_link_form'
	);
}
add_action('admin_menu', 'oldbook_register_admin_pages');

function oldbook_enqueue_admin_assets($hook_suffix) {
	$page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

	if (0 !== strpos($page, 'oldbook')) {
		return;
	}

	$theme = wp_get_theme();

	wp_enqueue_style(
		'oldbook-admin',
		get_template_directory_uri() . '/assets/css/admin.css',
		array(),
		$theme->get('Version')
	);
	wp_enqueue_script(
		'oldbook-admin',
		get_template_directory_uri() . '/assets/js/admin.js',
		array(),
		$theme->get('Version'),
		true
	);

	if (in_array($page, array('oldbook-publish', 'oldbook-link-add'), true)) {
		wp_enqueue_media();
	}
}
add_action('admin_enqueue_scripts', 'oldbook_enqueue_admin_assets');

function oldbook_admin_page_url($page, $args = array()) {
	$args['page'] = $page;

	return add_query_arg($args, admin_url('admin.php'));
}

function oldbook_admin_redirect($page, $args = array()) {
	wp_safe_redirect(oldbook_admin_page_url($page, $args));
	exit;
}

function oldbook_admin_notice() {
	$notice = isset($_GET['oldbook_notice']) ? sanitize_key(wp_unslash($_GET['oldbook_notice'])) : '';
	$error  = isset($_GET['oldbook_error']) ? sanitize_text_field(wp_unslash($_GET['oldbook_error'])) : '';

	$messages = array(
		'saved'   => __('已保存。', 'oldbook'),
		'deleted' => __('已删除。', 'oldbook'),
	);

	if ($notice && isset($messages[$notice])) {
		printf('<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html($messages[$notice]));
	}

	if ($error) {
		printf('<div class="notice notice-error"><p>%s</p></div>', esc_html($error));
	}
}

function oldbook_admin_header($title, $description = '') {
	?>
	<div class="wrap oldbook-admin-wrap">
		<h1><?php echo esc_html($title); ?></h1>
		<?php if ($description) : ?>
			<p class="oldbook-admin-lede"><?php echo esc_html($description); ?></p>
		<?php endif; ?>
		<?php oldbook_admin_notice(); ?>
	<?php
}

function oldbook_admin_footer() {
	?></div><?php
}

function oldbook_render_updates_admin() {
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('你没有管理 oldbook 内容的权限。', 'oldbook'));
	}

	$updates = get_posts(
		array(
			'post_type'      => 'oldbook_update',
			'post_status'    => array('publish', 'draft', 'pending', 'private'),
			'posts_per_page' => 50,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	oldbook_admin_header(
		__('动态', 'oldbook'),
		__('在一个专注的页面中发布文字、音乐、视频和图片动态。', 'oldbook')
	);
	?>
	<div class="oldbook-admin-toolbar">
		<a class="button button-primary" href="<?php echo esc_url(oldbook_admin_page_url('oldbook-publish')); ?>">
			<?php echo oldbook_icon('plus'); ?>
			<?php esc_html_e('发布动态', 'oldbook'); ?>
		</a>
	</div>

	<?php if ($updates) : ?>
		<table class="widefat fixed striped oldbook-admin-table">
			<thead>
				<tr>
					<th><?php esc_html_e('标题', 'oldbook'); ?></th>
					<th><?php esc_html_e('类型', 'oldbook'); ?></th>
					<th><?php esc_html_e('状态', 'oldbook'); ?></th>
					<th><?php esc_html_e('日期', 'oldbook'); ?></th>
					<th class="oldbook-admin-table__actions"><?php esc_html_e('操作', 'oldbook'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($updates as $update) : ?>
					<?php $type = oldbook_get_update_type($update->ID); ?>
					<tr>
						<td>
							<strong><?php echo esc_html(get_the_title($update)); ?></strong>
							<p class="oldbook-admin-table__preview"><?php echo esc_html(oldbook_get_update_preview($update->ID)); ?></p>
						</td>
						<td><span class="oldbook-admin-type"><span class="oldbook-admin-type__icon"><?php echo oldbook_icon(oldbook_get_update_types()[$type]['icon']); ?></span><?php echo esc_html(oldbook_get_update_type_label($type)); ?></span></td>
						<td><?php echo esc_html(get_post_status_object($update->post_status)->label); ?></td>
						<td><?php echo esc_html(get_the_date('Y-m-d H:i', $update)); ?></td>
						<td class="oldbook-admin-table__actions">
							<a class="button button-small" href="<?php echo esc_url(oldbook_admin_page_url('oldbook-publish', array('post_id' => $update->ID))); ?>">
								<?php echo oldbook_icon('edit'); ?>
								<?php esc_html_e('编辑', 'oldbook'); ?>
							</a>
							<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
								<input type="hidden" name="action" value="oldbook_delete_content">
								<input type="hidden" name="oldbook_post_id" value="<?php echo esc_attr($update->ID); ?>">
								<?php wp_nonce_field('oldbook_delete_content_' . $update->ID); ?>
								<button class="button button-small oldbook-admin-danger" type="submit">
									<?php echo oldbook_icon('trash'); ?>
									<?php esc_html_e('删除', 'oldbook'); ?>
								</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else : ?>
		<div class="oldbook-admin-empty">
			<?php esc_html_e('还没有动态。', 'oldbook'); ?>
		</div>
	<?php endif; ?>
	<?php
	oldbook_admin_footer();
}

function oldbook_render_links_admin() {
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('你没有管理 oldbook 内容的权限。', 'oldbook'));
	}

	$links = get_posts(
		array(
			'post_type'      => 'oldbook_link',
			'post_status'    => array('publish', 'draft', 'pending', 'private'),
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	oldbook_admin_header(
		__('链接', 'oldbook'),
		__('在一个轻量的目录中管理个人收藏和友链。', 'oldbook')
	);
	?>
	<div class="oldbook-admin-toolbar">
		<a class="button button-primary" href="<?php echo esc_url(oldbook_admin_page_url('oldbook-link-add')); ?>">
			<?php echo oldbook_icon('plus'); ?>
			<?php esc_html_e('添加链接', 'oldbook'); ?>
		</a>
	</div>

	<?php if ($links) : ?>
		<table class="widefat fixed striped oldbook-admin-table">
			<thead>
				<tr>
					<th><?php esc_html_e('网站', 'oldbook'); ?></th>
					<th><?php esc_html_e('分组', 'oldbook'); ?></th>
					<th><?php esc_html_e('网址', 'oldbook'); ?></th>
					<th><?php esc_html_e('日期', 'oldbook'); ?></th>
					<th class="oldbook-admin-table__actions"><?php esc_html_e('操作', 'oldbook'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($links as $link) : ?>
					<?php $group = oldbook_get_link_group($link->ID); ?>
					<tr>
						<td>
							<strong><?php echo esc_html(get_the_title($link)); ?></strong>
							<p class="oldbook-admin-table__preview"><?php echo esc_html(oldbook_get_link_description($link->ID)); ?></p>
						</td>
						<td><?php echo esc_html(oldbook_get_link_groups()[$group]); ?></td>
						<td><a href="<?php echo esc_url(oldbook_get_link_url($link->ID)); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html(oldbook_get_link_url($link->ID)); ?></a></td>
						<td><?php echo esc_html(get_the_date('Y-m-d H:i', $link)); ?></td>
						<td class="oldbook-admin-table__actions">
							<a class="button button-small" href="<?php echo esc_url(oldbook_admin_page_url('oldbook-link-add', array('post_id' => $link->ID))); ?>">
								<?php echo oldbook_icon('edit'); ?>
								<?php esc_html_e('编辑', 'oldbook'); ?>
							</a>
							<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
								<input type="hidden" name="action" value="oldbook_delete_content">
								<input type="hidden" name="oldbook_post_id" value="<?php echo esc_attr($link->ID); ?>">
								<?php wp_nonce_field('oldbook_delete_content_' . $link->ID); ?>
								<button class="button button-small oldbook-admin-danger" type="submit">
									<?php echo oldbook_icon('trash'); ?>
									<?php esc_html_e('删除', 'oldbook'); ?>
								</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else : ?>
		<div class="oldbook-admin-empty">
			<?php esc_html_e('还没有链接。', 'oldbook'); ?>
		</div>
	<?php endif; ?>
	<?php
	oldbook_admin_footer();
}

function oldbook_render_update_form() {
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('你没有管理 oldbook 内容的权限。', 'oldbook'));
	}

	$post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
	$post    = $post_id ? get_post($post_id) : null;

	if ($post_id && (! $post || 'oldbook_update' !== $post->post_type)) {
		oldbook_admin_redirect('oldbook-publish', array('oldbook_error' => __('找不到这条动态。', 'oldbook')));
	}

	if ($post_id && ! current_user_can('edit_post', $post_id)) {
		wp_die(esc_html__('你没有编辑这条动态的权限。', 'oldbook'));
	}

	$type         = $post ? oldbook_get_update_type($post_id) : 'text';
	$title        = $post ? $post->post_title : '';
	$content      = $post ? $post->post_content : '';
	$media_source = $post ? sanitize_key((string) get_post_meta($post_id, '_oldbook_media_source', true)) : 'external';
	$media_source = in_array($media_source, array('local', 'external'), true) ? $media_source : 'external';
	$media_url    = $post ? get_post_meta($post_id, '_oldbook_media_url', true) : '';
	$photo_ids    = $post ? oldbook_get_photo_ids($post_id) : array();
	$types        = oldbook_get_update_types();

	oldbook_admin_header(
		$post ? __('编辑动态', 'oldbook') : __('发布动态', 'oldbook'),
		__('选择一种类型，填写内容，无需打开默认文章编辑器即可发布。', 'oldbook')
	);
	?>
	<form class="oldbook-admin-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
		<input type="hidden" name="action" value="oldbook_save_update">
		<input type="hidden" name="oldbook_post_id" value="<?php echo esc_attr($post_id); ?>">
		<input type="hidden" name="oldbook_type" value="<?php echo esc_attr($type); ?>" data-oldbook-picker-input="update-type">
		<?php wp_nonce_field('oldbook_save_update'); ?>

		<div class="oldbook-admin-form__section">
		<h2><?php esc_html_e('动态类型', 'oldbook'); ?></h2>
		<div class="oldbook-picker" role="radiogroup" aria-label="<?php esc_attr_e('动态类型', 'oldbook'); ?>">
				<?php foreach ($types as $value => $item) : ?>
					<button type="button" class="oldbook-picker__option<?php echo $type === $value ? ' is-selected' : ''; ?>" role="radio" aria-checked="<?php echo $type === $value ? 'true' : 'false'; ?>" data-oldbook-picker="update-type" data-value="<?php echo esc_attr($value); ?>">
						<span class="oldbook-picker__icon"><?php echo oldbook_icon($item['icon']); ?></span>
						<span><?php echo esc_html($item['label']); ?></span>
					</button>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="oldbook-admin-form__section">
		<label class="oldbook-admin-label" for="oldbook-title"><?php esc_html_e('标题', 'oldbook'); ?><span><?php esc_html_e('媒体动态可不填写', 'oldbook'); ?></span></label>
			<input type="text" id="oldbook-title" name="oldbook_title" value="<?php echo esc_attr($title); ?>" maxlength="120">
		</div>

		<div class="oldbook-admin-form__section">
		<label class="oldbook-admin-label" for="oldbook-content"><?php esc_html_e('文字内容', 'oldbook'); ?><span><?php esc_html_e('支持换行，也可以为媒体动态添加简短说明。', 'oldbook'); ?></span></label>
			<textarea id="oldbook-content" name="oldbook_content" rows="8"><?php echo esc_textarea($content); ?></textarea>
		</div>

		<div class="oldbook-admin-form__section oldbook-admin-conditional" data-oldbook-type="music video">
		<h2><?php esc_html_e('媒体来源', 'oldbook'); ?></h2>
			<input type="hidden" name="oldbook_media_source" value="<?php echo esc_attr($media_source); ?>" data-oldbook-picker-input="media-source">
		<div class="oldbook-picker oldbook-picker--compact" role="radiogroup" aria-label="<?php esc_attr_e('媒体来源', 'oldbook'); ?>">
			<button type="button" class="oldbook-picker__option<?php echo 'local' === $media_source ? ' is-selected' : ''; ?>" role="radio" aria-checked="<?php echo 'local' === $media_source ? 'true' : 'false'; ?>" data-oldbook-picker="media-source" data-value="local"><?php esc_html_e('上传文件', 'oldbook'); ?></button>
			<button type="button" class="oldbook-picker__option<?php echo 'external' === $media_source ? ' is-selected' : ''; ?>" role="radio" aria-checked="<?php echo 'external' === $media_source ? 'true' : 'false'; ?>" data-oldbook-picker="media-source" data-value="external"><?php esc_html_e('外部网址', 'oldbook'); ?></button>
			</div>
			<div class="oldbook-admin-media-source" data-oldbook-source="local">
			<label class="oldbook-admin-label" for="oldbook-media-file"><?php esc_html_e('音频或视频文件', 'oldbook'); ?></label>
				<input type="file" id="oldbook-media-file" name="oldbook_media_file" accept="audio/*,video/*">
				<?php if ($post_id && oldbook_get_update_media_url($post_id, $type) && 'local' === $media_source) : ?>
				<p class="description"><?php esc_html_e('当前已有文件，重新上传可以替换它。', 'oldbook'); ?></p>
				<?php endif; ?>
			</div>
			<div class="oldbook-admin-media-source" data-oldbook-source="external">
			<label class="oldbook-admin-label" for="oldbook-media-url"><?php esc_html_e('媒体网址', 'oldbook'); ?></label>
				<input type="url" id="oldbook-media-url" name="oldbook_media_url" value="<?php echo esc_attr($media_url); ?>" placeholder="https://">
			</div>
		</div>

		<div class="oldbook-admin-form__section oldbook-admin-conditional" data-oldbook-type="photo">
		<label class="oldbook-admin-label" for="oldbook-photos"><?php esc_html_e('图片', 'oldbook'); ?><span><?php esc_html_e('最多选择 9 张本地图片。', 'oldbook'); ?></span></label>
			<input type="file" id="oldbook-photos" name="oldbook_photos[]" accept="image/*" multiple>
			<?php if ($photo_ids) : ?>
				<div class="oldbook-admin-photo-list">
					<?php foreach ($photo_ids as $photo_id) : ?>
						<?php echo wp_get_attachment_image($photo_id, 'thumbnail', false, array('loading' => 'lazy', 'alt' => '')); ?>
						<input type="hidden" name="oldbook_existing_photo_ids[]" value="<?php echo esc_attr($photo_id); ?>">
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="oldbook-admin-form__actions">
		<button type="submit" class="button button-primary button-large"><?php echo $post ? esc_html__('更新动态', 'oldbook') : esc_html__('发布动态', 'oldbook'); ?></button>
		<a class="button button-large" href="<?php echo esc_url(oldbook_admin_page_url('oldbook')); ?>"><?php esc_html_e('取消', 'oldbook'); ?></a>
		</div>
	</form>
	<?php
	oldbook_admin_footer();
}

function oldbook_render_link_form() {
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('你没有管理 oldbook 内容的权限。', 'oldbook'));
	}

	$post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
	$post    = $post_id ? get_post($post_id) : null;

	if ($post_id && (! $post || 'oldbook_link' !== $post->post_type)) {
		oldbook_admin_redirect('oldbook-link-add', array('oldbook_error' => __('找不到这个链接。', 'oldbook')));
	}

	if ($post_id && ! current_user_can('edit_post', $post_id)) {
		wp_die(esc_html__('你没有编辑这个链接的权限。', 'oldbook'));
	}

	$group      = $post ? oldbook_get_link_group($post_id) : 'bookmark';
	$url        = $post ? oldbook_get_link_url($post_id) : '';
	$description = $post ? oldbook_get_link_description($post_id) : '';
	$icon_url   = $post ? get_post_meta($post_id, '_oldbook_link_icon_url', true) : '';
	$title      = $post ? $post->post_title : '';

	oldbook_admin_header(
		$post ? __('编辑链接', 'oldbook') : __('添加链接', 'oldbook'),
		__('填写网址、简短介绍和可选的自定义图标。', 'oldbook')
	);
	?>
	<form class="oldbook-admin-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
		<input type="hidden" name="action" value="oldbook_save_link">
		<input type="hidden" name="oldbook_post_id" value="<?php echo esc_attr($post_id); ?>">
		<input type="hidden" name="oldbook_link_group" value="<?php echo esc_attr($group); ?>" data-oldbook-picker-input="link-group">
		<?php wp_nonce_field('oldbook_save_link'); ?>

		<div class="oldbook-admin-form__section">
		<h2><?php esc_html_e('分组', 'oldbook'); ?></h2>
		<div class="oldbook-picker oldbook-picker--compact" role="radiogroup" aria-label="<?php esc_attr_e('链接分组', 'oldbook'); ?>">
				<?php foreach (oldbook_get_link_groups() as $value => $label) : ?>
					<button type="button" class="oldbook-picker__option<?php echo $group === $value ? ' is-selected' : ''; ?>" role="radio" aria-checked="<?php echo $group === $value ? 'true' : 'false'; ?>" data-oldbook-picker="link-group" data-value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></button>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="oldbook-admin-form__section">
		<label class="oldbook-admin-label" for="oldbook-link-title"><?php esc_html_e('标题', 'oldbook'); ?></label>
			<input type="text" id="oldbook-link-title" name="oldbook_link_title" value="<?php echo esc_attr($title); ?>" maxlength="120" required>
		</div>

		<div class="oldbook-admin-form__section">
		<label class="oldbook-admin-label" for="oldbook-link-url"><?php esc_html_e('网址', 'oldbook'); ?></label>
			<input type="url" id="oldbook-link-url" name="oldbook_link_url" value="<?php echo esc_attr($url); ?>" placeholder="https://" required>
		</div>

		<div class="oldbook-admin-form__section">
		<label class="oldbook-admin-label" for="oldbook-link-description"><?php esc_html_e('描述', 'oldbook'); ?></label>
			<textarea id="oldbook-link-description" name="oldbook_link_description" rows="4"><?php echo esc_textarea($description); ?></textarea>
		</div>

		<div class="oldbook-admin-form__section">
		<h2><?php esc_html_e('图标', 'oldbook'); ?></h2>
		<p class="description"><?php esc_html_e('两个字段都留空时，将使用网站图标。', 'oldbook'); ?></p>
		<label class="oldbook-admin-label" for="oldbook-link-icon-file"><?php esc_html_e('上传自定义图标', 'oldbook'); ?></label>
			<input type="file" id="oldbook-link-icon-file" name="oldbook_link_icon_file" accept="image/*">
		<label class="oldbook-admin-label" for="oldbook-link-icon-url"><?php esc_html_e('自定义图标网址', 'oldbook'); ?></label>
			<input type="url" id="oldbook-link-icon-url" name="oldbook_link_icon_url" value="<?php echo esc_attr($icon_url); ?>" placeholder="https://">
		</div>

		<div class="oldbook-admin-form__actions">
		<button type="submit" class="button button-primary button-large"><?php echo $post ? esc_html__('更新链接', 'oldbook') : esc_html__('保存链接', 'oldbook'); ?></button>
		<a class="button button-large" href="<?php echo esc_url(oldbook_admin_page_url('oldbook-links')); ?>"><?php esc_html_e('取消', 'oldbook'); ?></a>
		</div>
	</form>
	<?php
	oldbook_admin_footer();
}

function oldbook_get_upload_mimes($kind) {
	$mimes = array(
		'image' => array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'png'          => 'image/png',
			'gif'          => 'image/gif',
			'webp'         => 'image/webp',
		),
		'audio' => array(
			'mp3'  => 'audio/mpeg',
			'm4a'  => 'audio/mp4',
			'ogg'  => 'audio/ogg',
			'wav'  => 'audio/wav',
			'flac' => 'audio/flac',
		),
		'video' => array(
			'mp4|m4v' => 'video/mp4',
			'webm'    => 'video/webm',
			'ogv'     => 'video/ogg',
			'mov'     => 'video/quicktime',
		),
	);

	return isset($mimes[$kind]) ? $mimes[$kind] : $mimes['image'];
}

function oldbook_handle_upload($file_key, $post_id, $kind) {
	if (empty($_FILES[$file_key]) || empty($_FILES[$file_key]['name'])) {
		return 0;
	}

	$file  = $_FILES[$file_key];
	$mimes = oldbook_get_upload_mimes($kind);

	if (! empty($file['error']) && UPLOAD_ERR_OK !== (int) $file['error']) {
		return new WP_Error('oldbook_upload_error', __('文件上传没有完成，请重试。', 'oldbook'));
	}

	$type  = wp_check_filetype_and_ext($file['tmp_name'], $file['name'], $mimes);

	if (empty($type['type'])) {
		return new WP_Error('oldbook_invalid_upload', __('不允许上传这种文件类型。', 'oldbook'));
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	return media_handle_upload(
		$file_key,
		$post_id,
		array(),
		array(
			'test_form' => false,
			'mimes'    => $mimes,
		)
	);
}

function oldbook_handle_photo_uploads($post_id, $existing_ids) {
	if (empty($_FILES['oldbook_photos']) || ! is_array($_FILES['oldbook_photos']['name'])) {
		return $existing_ids;
	}

	$files = $_FILES['oldbook_photos'];
	$count = 0;

	foreach ($files['name'] as $name) {
		if ($name) {
			$count++;
		}
	}

	if (count($existing_ids) + $count > 9) {
		return new WP_Error('oldbook_photo_limit', __('图片动态最多只能包含 9 张图片。', 'oldbook'));
	}

	foreach ($files['name'] as $index => $name) {
		if (! $name) {
			continue;
		}

		$_FILES['oldbook_photo'] = array(
			'name'     => $files['name'][$index],
			'type'     => $files['type'][$index],
			'tmp_name' => $files['tmp_name'][$index],
			'error'    => $files['error'][$index],
			'size'     => $files['size'][$index],
		);

		$attachment_id = oldbook_handle_upload('oldbook_photo', $post_id, 'image');
		unset($_FILES['oldbook_photo']);

		if (is_wp_error($attachment_id)) {
			return $attachment_id;
		}

		$existing_ids[] = absint($attachment_id);
	}

	return $existing_ids;
}

function oldbook_redirect_save_error($page, $message, $post_id = 0) {
	$args = array('oldbook_error' => wp_strip_all_tags($message));

	if ($post_id) {
		$args['post_id'] = absint($post_id);
	}

	oldbook_admin_redirect($page, $args);
}

function oldbook_handle_save_update() {
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('你没有管理 oldbook 内容的权限。', 'oldbook'));
	}

	check_admin_referer('oldbook_save_update');

	$post_id = isset($_POST['oldbook_post_id']) ? absint($_POST['oldbook_post_id']) : 0;
	$type    = isset($_POST['oldbook_type']) ? sanitize_key(wp_unslash($_POST['oldbook_type'])) : 'text';
	$types   = oldbook_get_update_types();

	if (! isset($types[$type])) {
		oldbook_redirect_save_error('oldbook-publish', __('请选择有效的动态类型。', 'oldbook'), $post_id);
	}

	if ($post_id && ('oldbook_update' !== get_post_type($post_id) || ! current_user_can('edit_post', $post_id))) {
		oldbook_redirect_save_error('oldbook-publish', __('找不到这条动态。', 'oldbook'));
	}

	$title   = isset($_POST['oldbook_title']) ? sanitize_text_field(wp_unslash($_POST['oldbook_title'])) : '';
	$content = isset($_POST['oldbook_content']) ? sanitize_textarea_field(wp_unslash($_POST['oldbook_content'])) : '';
	$title   = $title ? $title : oldbook_default_update_title($type);

	if ('text' === $type && ! trim($content)) {
		oldbook_redirect_save_error('oldbook-publish', __('文字动态需要填写内容。', 'oldbook'), $post_id);
	}

	$media_source = isset($_POST['oldbook_media_source']) ? sanitize_key(wp_unslash($_POST['oldbook_media_source'])) : 'external';
	$media_source = in_array($media_source, array('local', 'external'), true) ? $media_source : 'external';
	$media_url    = isset($_POST['oldbook_media_url']) ? oldbook_clean_url(wp_unslash($_POST['oldbook_media_url'])) : '';
	$old_media_id = $post_id ? absint(get_post_meta($post_id, '_oldbook_media_attachment_id', true)) : 0;
	$old_media_url = $post_id ? oldbook_get_update_media_url($post_id, $type) : '';

	if (in_array($type, array('music', 'video'), true) && 'external' === $media_source && ! $media_url) {
		oldbook_redirect_save_error('oldbook-publish', __('请填写有效的外部媒体网址。', 'oldbook'), $post_id);
	}

	if (in_array($type, array('music', 'video'), true) && 'local' === $media_source && empty($_FILES['oldbook_media_file']['name']) && ! $old_media_id) {
		oldbook_redirect_save_error('oldbook-publish', __('请上传媒体文件，或选择外部媒体网址。', 'oldbook'), $post_id);
	}

	$existing_photo_ids = isset($_POST['oldbook_existing_photo_ids']) ? array_values(array_filter(array_map('absint', (array) wp_unslash($_POST['oldbook_existing_photo_ids'])))) : array();
	if ('photo' === $type && ! $existing_photo_ids && empty($_FILES['oldbook_photos']['name'][0])) {
		oldbook_redirect_save_error('oldbook-publish', __('图片动态至少需要选择一张图片。', 'oldbook'), $post_id);
	}

	$post_data = array(
		'post_type'    => 'oldbook_update',
		'post_status'  => $post_id ? get_post_status($post_id) : 'draft',
		'post_title'   => $title,
		'post_content' => $content,
		'post_author'  => get_current_user_id(),
	);

	if ($post_id) {
		$post_data['ID'] = $post_id;
		$saved_id       = wp_update_post($post_data, true);
	} else {
		$saved_id = wp_insert_post($post_data, true);
	}

	if (is_wp_error($saved_id)) {
		oldbook_redirect_save_error('oldbook-publish', $saved_id->get_error_message(), $post_id);
	}

	$saved_id = absint($saved_id);
	update_post_meta($saved_id, '_oldbook_update_type', $type);

	if ('text' === $type) {
		delete_post_meta($saved_id, '_oldbook_media_source');
		delete_post_meta($saved_id, '_oldbook_media_url');
		delete_post_meta($saved_id, '_oldbook_media_attachment_id');
		delete_post_meta($saved_id, '_oldbook_photo_ids');
	}

	if (in_array($type, array('music', 'video'), true)) {
		if ('external' === $media_source) {
			update_post_meta($saved_id, '_oldbook_media_source', 'external');
			update_post_meta($saved_id, '_oldbook_media_url', $media_url);
			delete_post_meta($saved_id, '_oldbook_media_attachment_id');
		} else {
			$attachment_id = oldbook_handle_upload('oldbook_media_file', $saved_id, $type === 'music' ? 'audio' : 'video');

			if (is_wp_error($attachment_id)) {
				oldbook_redirect_save_error('oldbook-publish', $attachment_id->get_error_message(), $post_id);
			}

			if ($attachment_id) {
				update_post_meta($saved_id, '_oldbook_media_attachment_id', absint($attachment_id));
				delete_post_meta($saved_id, '_oldbook_media_url');
			}

			update_post_meta($saved_id, '_oldbook_media_source', 'local');
		}

		delete_post_meta($saved_id, '_oldbook_photo_ids');
	}

	if ('photo' === $type) {
		$photo_ids = oldbook_handle_photo_uploads($saved_id, $existing_photo_ids);

		if (is_wp_error($photo_ids)) {
			oldbook_redirect_save_error('oldbook-publish', $photo_ids->get_error_message(), $post_id);
		}

		update_post_meta($saved_id, '_oldbook_photo_ids', $photo_ids);
		delete_post_meta($saved_id, '_oldbook_media_source');
		delete_post_meta($saved_id, '_oldbook_media_url');
		delete_post_meta($saved_id, '_oldbook_media_attachment_id');
	}

	wp_update_post(
		array(
			'ID'          => $saved_id,
			'post_status' => 'publish',
		)
	);

	oldbook_admin_redirect('oldbook', array('oldbook_notice' => 'saved'));
}
add_action('admin_post_oldbook_save_update', 'oldbook_handle_save_update');

function oldbook_handle_save_link() {
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('你没有管理 oldbook 内容的权限。', 'oldbook'));
	}

	check_admin_referer('oldbook_save_link');

	$post_id     = isset($_POST['oldbook_post_id']) ? absint($_POST['oldbook_post_id']) : 0;
	$title       = isset($_POST['oldbook_link_title']) ? sanitize_text_field(wp_unslash($_POST['oldbook_link_title'])) : '';
	$url         = isset($_POST['oldbook_link_url']) ? oldbook_clean_url(wp_unslash($_POST['oldbook_link_url'])) : '';
	$description = isset($_POST['oldbook_link_description']) ? sanitize_textarea_field(wp_unslash($_POST['oldbook_link_description'])) : '';
	$icon_url    = isset($_POST['oldbook_link_icon_url']) ? oldbook_clean_url(wp_unslash($_POST['oldbook_link_icon_url'])) : '';
	$group       = isset($_POST['oldbook_link_group']) ? sanitize_key(wp_unslash($_POST['oldbook_link_group'])) : 'bookmark';

	if ($post_id && ('oldbook_link' !== get_post_type($post_id) || ! current_user_can('edit_post', $post_id))) {
		oldbook_redirect_save_error('oldbook-link-add', __('找不到这个链接。', 'oldbook'));
	}

	if (! $title || ! $url) {
		oldbook_redirect_save_error('oldbook-link-add', __('链接需要填写标题和有效网址。', 'oldbook'), $post_id);
	}

	if (! array_key_exists($group, oldbook_get_link_groups())) {
		$group = 'bookmark';
	}

	$post_data = array(
		'post_type'    => 'oldbook_link',
		'post_status'  => $post_id ? get_post_status($post_id) : 'draft',
		'post_title'   => $title,
		'post_content' => $description,
		'post_author'  => get_current_user_id(),
	);

	if ($post_id) {
		$post_data['ID'] = $post_id;
		$saved_id       = wp_update_post($post_data, true);
	} else {
		$saved_id = wp_insert_post($post_data, true);
	}

	if (is_wp_error($saved_id)) {
		oldbook_redirect_save_error('oldbook-link-add', $saved_id->get_error_message(), $post_id);
	}

	$saved_id = absint($saved_id);
	update_post_meta($saved_id, '_oldbook_link_group', $group);
	update_post_meta($saved_id, '_oldbook_link_url', $url);
	update_post_meta($saved_id, '_oldbook_link_description', $description);

	$icon_attachment_id = oldbook_handle_upload('oldbook_link_icon_file', $saved_id, 'image');

	if (is_wp_error($icon_attachment_id)) {
		oldbook_redirect_save_error('oldbook-link-add', $icon_attachment_id->get_error_message(), $post_id);
	}

	if ($icon_attachment_id) {
		update_post_meta($saved_id, '_oldbook_link_icon_attachment_id', absint($icon_attachment_id));
		delete_post_meta($saved_id, '_oldbook_link_icon_url');
	} elseif ($icon_url) {
		update_post_meta($saved_id, '_oldbook_link_icon_url', $icon_url);
	}

	wp_update_post(
		array(
			'ID'          => $saved_id,
			'post_status' => 'publish',
		)
	);

	oldbook_admin_redirect('oldbook-links', array('oldbook_notice' => 'saved'));
}
add_action('admin_post_oldbook_save_link', 'oldbook_handle_save_link');

function oldbook_handle_delete_content() {
	$post_id = isset($_POST['oldbook_post_id']) ? absint($_POST['oldbook_post_id']) : 0;

	if (! current_user_can('edit_posts') || ! $post_id) {
		wp_die(esc_html__('你没有删除这项内容的权限。', 'oldbook'));
	}

	check_admin_referer('oldbook_delete_content_' . $post_id);

	$post_type = get_post_type($post_id);
	$page      = 'oldbook';

	if ('oldbook_link' === $post_type) {
		$page = 'oldbook-links';
	}

	if (! in_array($post_type, array('oldbook_update', 'oldbook_link'), true)) {
		oldbook_admin_redirect($page, array('oldbook_error' => __('无法删除这项内容。', 'oldbook')));
	}

	if (! current_user_can('delete_post', $post_id)) {
		wp_die(esc_html__('你没有删除这项内容的权限。', 'oldbook'));
	}

	wp_delete_post($post_id, true);
	oldbook_admin_redirect($page, array('oldbook_notice' => 'deleted'));
}
add_action('admin_post_oldbook_delete_content', 'oldbook_handle_delete_content');
