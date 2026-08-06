<?php
/**
 * Shared oldbook helpers.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

function oldbook_get_update_types() {
	return array(
		'text' => array(
			'label' => __('文字', 'oldbook'),
			'icon'  => 'text',
		),
		'music' => array(
			'label' => __('音乐', 'oldbook'),
			'icon'  => 'music',
		),
		'video' => array(
			'label' => __('视频', 'oldbook'),
			'icon'  => 'video',
		),
		'photo' => array(
			'label' => __('图片', 'oldbook'),
			'icon'  => 'photo',
		),
	);
}

function oldbook_get_link_groups() {
	return array(
		'bookmark' => __('个人收藏', 'oldbook'),
		'friend'   => __('友链', 'oldbook'),
	);
}

function oldbook_get_update_type($post_id = 0) {
	$post_id = $post_id ? absint($post_id) : get_the_ID();
	$type    = sanitize_key((string) get_post_meta($post_id, '_oldbook_update_type', true));

	return array_key_exists($type, oldbook_get_update_types()) ? $type : 'text';
}

function oldbook_get_update_type_label($type) {
	$types = oldbook_get_update_types();

	return isset($types[$type]['label']) ? $types[$type]['label'] : $types['text']['label'];
}

function oldbook_get_update_media_url($post_id, $type = '') {
	$post_id = absint($post_id);
	$type    = $type ? $type : oldbook_get_update_type($post_id);

	if (! in_array($type, array('music', 'video'), true)) {
		return '';
	}

	$attachment_id = absint(get_post_meta($post_id, '_oldbook_media_attachment_id', true));
	$url           = $attachment_id ? wp_get_attachment_url($attachment_id) : '';

	if (! $url) {
		$url = get_post_meta($post_id, '_oldbook_media_url', true);
	}

	if ('music' === $type) {
		$host = wp_parse_url($url, PHP_URL_HOST);
		$query = wp_parse_url($url, PHP_URL_QUERY);
		parse_str((string) $query, $query_args);

		if ($host && false !== strpos($host, 'music.163.com') && ! empty($query_args['id'])) {
			$url = 'https://music.163.com/song/media/outer/url?id=' . rawurlencode(absint($query_args['id'])) . '.mp3';
		}
	}

	return oldbook_clean_url($url);
}

function oldbook_get_photo_ids($post_id) {
	$ids = get_post_meta(absint($post_id), '_oldbook_photo_ids', true);

	if (! is_array($ids)) {
		return array();
	}

	return array_values(array_filter(array_map('absint', $ids)));
}

function oldbook_get_link_group($post_id = 0) {
	$post_id = $post_id ? absint($post_id) : get_the_ID();
	$group   = sanitize_key((string) get_post_meta($post_id, '_oldbook_link_group', true));

	return array_key_exists($group, oldbook_get_link_groups()) ? $group : 'bookmark';
}

function oldbook_get_link_url($post_id = 0) {
	$post_id = $post_id ? absint($post_id) : get_the_ID();

	return oldbook_clean_url(get_post_meta($post_id, '_oldbook_link_url', true));
}

function oldbook_get_link_description($post_id = 0) {
	$post_id = $post_id ? absint($post_id) : get_the_ID();

	return (string) get_post_meta($post_id, '_oldbook_link_description', true);
}

function oldbook_get_link_icon_url($post_id = 0) {
	$post_id      = $post_id ? absint($post_id) : get_the_ID();
	$attachment_id = absint(get_post_meta($post_id, '_oldbook_link_icon_attachment_id', true));
	$custom_url    = $attachment_id ? wp_get_attachment_image_url($attachment_id, 'thumbnail') : '';

	if (! $custom_url) {
		$custom_url = oldbook_clean_url(get_post_meta($post_id, '_oldbook_link_icon_url', true));
	}

	if ($custom_url) {
		return $custom_url;
	}

	$url  = oldbook_get_link_url($post_id);
	$host = wp_parse_url($url, PHP_URL_HOST);

	if (! $host) {
		return '';
	}

	return 'https://www.google.com/s2/favicons?sz=64&domain=' . rawurlencode($host);
}

function oldbook_clean_url($url) {
	$url = esc_url_raw((string) $url, array('http', 'https'));

	if (! $url || ! wp_http_validate_url($url)) {
		return '';
	}

	return $url;
}

function oldbook_default_update_title($type) {
	$label = oldbook_get_update_type_label($type);

	return sprintf(
		/* translators: %s is the dynamic type. */
		__('%s动态', 'oldbook'),
		$label
	);
}

function oldbook_get_update_preview($post_id) {
	$type = oldbook_get_update_type($post_id);

	if ('text' === $type) {
		$content = get_post_field('post_content', $post_id);
		$content = wp_strip_all_tags($content);
		$content = preg_replace('/\s+/', ' ', $content);

		return wp_trim_words($content, 18, '...');
	}

	if ('photo' === $type) {
		$count = count(oldbook_get_photo_ids($post_id));

		return sprintf(
			/* translators: %d is the number of photos. */
			_n('%d 张图片', '%d 张图片', $count, 'oldbook'),
			$count
		);
	}

	return oldbook_get_update_type_label($type);
}

function oldbook_icon($name, $class = '') {
	$paths = array(
		'arrow-up-right' => '<path d="M7 17 17 7M8 7h9v9"/>',
		'chevron-down'   => '<path d="m6 9 6 6 6-6"/>',
		'edit'           => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/>',
		'external'       => '<path d="M14 3h7v7"/><path d="M10 14 21 3"/><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/>',
		'link'           => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
		'music'          => '<path d="M9 18V5l11-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="17" cy="16" r="3"/>',
		'pause'          => '<path d="M7 4v16M17 4v16"/>',
		'photo'          => '<rect x="3" y="4" width="18" height="16" rx="1"/><circle cx="8.5" cy="9" r="1.5"/><path d="m21 15-5-5L5 20"/>',
		'play'           => '<path d="m8 5 11 7-11 7Z"/>',
		'plus'           => '<path d="M12 5v14M5 12h14"/>',
		'search'         => '<circle cx="11" cy="11" r="6"/><path d="m16 16 4.5 4.5"/>',
		'text'           => '<path d="M4 6h16M4 12h16M4 18h10"/>',
		'trash'          => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v5M14 11v5"/>',
		'video'          => '<rect x="3" y="5" width="13" height="14" rx="1"/><path d="m16 10 5-3v10l-5-3Z"/>',
		'volume'         => '<path d="M11 5 6 9H3v6h3l5 4Z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M18 6a9 9 0 0 1 0 12"/>',
	);

	if (! isset($paths[$name])) {
		return '';
	}

	$class = $class ? ' ' . sanitize_html_class($class) : '';

	return '<svg class="oldbook-icon' . esc_attr($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[$name] . '</svg>';
}

function oldbook_render_plain_text($text) {
	return wpautop(esc_html((string) $text));
}
