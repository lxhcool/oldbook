<?php
/**
 * Shared oldbook helpers.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

function oldbook_sanitize_checkbox($value) {
	return (bool) $value;
}

function oldbook_sanitize_percentage($value) {
	return min(100, max(0, absint($value)));
}

function oldbook_sanitize_cover_direction($value) {
	$directions = array(
		'to bottom',
		'to right',
		'135deg',
		'to bottom right',
	);

	return in_array($value, $directions, true) ? $value : 'to bottom';
}

function oldbook_hex_to_rgba($color, $alpha = 1) {
	$color = sanitize_hex_color($color);

	if (! $color) {
		return 'rgba(17, 32, 26, 0.26)';
	}

	$hex = ltrim($color, '#');

	if (3 === strlen($hex)) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	$alpha = max(0, min(1, (float) $alpha));

	return sprintf(
		'rgba(%d, %d, %d, %.3f)',
		hexdec(substr($hex, 0, 2)),
		hexdec(substr($hex, 2, 2)),
		hexdec(substr($hex, 4, 2)),
		$alpha
	);
}

function oldbook_get_site_title() {
	$title = trim((string) get_theme_mod('oldbook_site_title', ''));

	if ($title) {
		return $title;
	}

	$title = trim((string) get_bloginfo('name'));

	return $title ? $title : 'oldbook';
}

function oldbook_get_site_tagline() {
	$tagline = trim((string) get_theme_mod('oldbook_site_tagline', ''));

	if ($tagline) {
		return $tagline;
	}

	$tagline = trim((string) get_bloginfo('description'));

	return $tagline ? $tagline : __('动态记录', 'oldbook');
}

function oldbook_get_site_logo_url($size = 'thumbnail') {
	$site_logo_id = absint(get_theme_mod('oldbook_site_logo_id', 0));
	$image_url    = $site_logo_id ? wp_get_attachment_image_url($site_logo_id, $size) : '';

	if (! $image_url && $site_logo_id) {
		$image_url = wp_get_attachment_url($site_logo_id);
	}

	if (! $image_url) {
		$image_url = esc_url_raw((string) get_theme_mod('oldbook_site_logo', ''));
	}

	if (! $image_url) {
		$legacy_logo_id = (int) get_theme_mod('custom_logo');
		$image_url      = $legacy_logo_id ? wp_get_attachment_image_url($legacy_logo_id, $size) : '';

		if (! $image_url && $legacy_logo_id) {
			$image_url = wp_get_attachment_url($legacy_logo_id);
		}
	}

	return $image_url ? $image_url : '';
}

function oldbook_get_cover_image_url() {
	$cover_id  = absint(get_theme_mod('oldbook_cover_image_id', 0));
	$cover_url = $cover_id ? wp_get_attachment_url($cover_id) : '';

	if (! $cover_url) {
		$cover_url = esc_url_raw((string) get_theme_mod('oldbook_cover_image', ''));
	}

	if (! $cover_url && function_exists('get_header_image')) {
		$cover_url = get_header_image();
	}

	if (! $cover_url) {
		$cover_url = get_template_directory_uri() . '/assets/images/oldbook-profile-cover.jpg';
	}

	return $cover_url;
}

function oldbook_get_cover_settings() {
	$overlay_color      = sanitize_hex_color(get_theme_mod('oldbook_cover_overlay_color', '#11201a'));
	$overlay_color      = $overlay_color ? $overlay_color : '#11201a';
	$overlay_opacity    = oldbook_sanitize_percentage(get_theme_mod('oldbook_cover_overlay_opacity', 26));
	$overlay_alpha      = $overlay_opacity / 100;
	$gradient_enabled   = (bool) get_theme_mod('oldbook_cover_gradient_enabled', false);
	$gradient_start     = sanitize_hex_color(get_theme_mod('oldbook_cover_gradient_start', '#11201a'));
	$gradient_end       = sanitize_hex_color(get_theme_mod('oldbook_cover_gradient_end', '#1d7a55'));
	$gradient_direction = oldbook_sanitize_cover_direction(get_theme_mod('oldbook_cover_gradient_direction', 'to bottom'));
	$cover_overlay      = oldbook_hex_to_rgba($overlay_color, $overlay_alpha);

	if ($gradient_enabled && $gradient_start && $gradient_end) {
		$cover_overlay = sprintf(
			'linear-gradient(%s, %s, %s)',
			$gradient_direction,
			oldbook_hex_to_rgba($gradient_start, $overlay_alpha),
			oldbook_hex_to_rgba($gradient_end, $overlay_alpha)
		);
	}

	return array(
		'overlay_color'      => $overlay_color,
		'overlay_opacity'    => $overlay_opacity,
		'gradient_enabled'   => $gradient_enabled,
		'gradient_start'     => $gradient_start ? $gradient_start : '#11201a',
		'gradient_end'       => $gradient_end ? $gradient_end : '#1d7a55',
		'gradient_direction' => $gradient_direction,
		'overlay'            => $cover_overlay,
	);
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

function oldbook_get_articles_url() {
	$posts_page_id = (int) get_option('page_for_posts');

	if ($posts_page_id) {
		return get_permalink($posts_page_id);
	}

	return home_url('/articles/');
}

function oldbook_get_profile_image_url($size = 96) {
	$image_url = oldbook_get_site_logo_url('thumbnail');

	if (! $image_url && function_exists('get_site_icon_url')) {
		$image_url = get_site_icon_url(absint($size));
	}

	return $image_url ? $image_url : '';
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
		'arrow-right'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'arrow-up-right' => '<path d="M7 17 17 7M8 7h9v9"/>',
		'chevron-down'   => '<path d="m6 9 6 6 6-6"/>',
		'edit'           => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/>',
		'external'       => '<path d="M14 3h7v7"/><path d="M10 14 21 3"/><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/>',
		'heart'          => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"/>',
		'link'           => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
		'menu'           => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'message-circle' => '<path d="M21 11.5a8.38 8.38 0 0 1-9 8.5 8.5 8.5 0 0 1-3.7-.84L3 21l1.84-4.3A8.5 8.5 0 1 1 21 11.5Z"/><path d="M8 12h.01M12 12h.01M16 12h.01"/>',
		'moon'           => '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/>',
		'music'          => '<path d="M9 18V5l11-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="17" cy="16" r="3"/>',
		'pause'          => '<path d="M7 4v16M17 4v16"/>',
		'photo'          => '<rect x="3" y="4" width="18" height="16" rx="1"/><circle cx="8.5" cy="9" r="1.5"/><path d="m21 15-5-5L5 20"/>',
		'play'           => '<path d="m8 5 11 7-11 7Z"/>',
		'plus'           => '<path d="M12 5v14M5 12h14"/>',
		'search'         => '<circle cx="11" cy="11" r="6"/><path d="m16 16 4.5 4.5"/>',
		'send'           => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
		'sun'            => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>',
		'text'           => '<path d="M4 6h16M4 12h16M4 18h10"/>',
		'trash'          => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v5M14 11v5"/>',
		'video'          => '<rect x="3" y="5" width="13" height="14" rx="1"/><path d="m16 10 5-3v10l-5-3Z"/>',
		'volume'         => '<path d="M11 5 6 9H3v6h3l5 4Z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M18 6a9 9 0 0 1 0 12"/>',
		'x'              => '<path d="M18 6 6 18M6 6l12 12"/>',
	);

	if (! isset($paths[$name])) {
		return '';
	}

	$class = $class ? ' ' . sanitize_html_class($class) : '';

	return '<svg class="oldbook-icon' . esc_attr($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[$name] . '</svg>';
}

function oldbook_get_site_stats() {
	$counts = wp_count_posts('post');
	$updates = wp_count_posts('oldbook_update');
	$links = wp_count_posts('oldbook_link');

	return array(
		'updates' => absint(isset($updates->publish) ? $updates->publish : 0),
		'posts'   => absint(isset($counts->publish) ? $counts->publish : 0),
		'links'   => absint(isset($links->publish) ? $links->publish : 0),
	);
}

function oldbook_next_stagger() {
	if (! isset($GLOBALS['oldbook_stagger'])) {
		$GLOBALS['oldbook_stagger'] = 0;
	}

	$index = min($GLOBALS['oldbook_stagger'], 8);
	$GLOBALS['oldbook_stagger']++;

	return $index;
}

function oldbook_render_plain_text($text) {
	return wpautop(esc_html((string) $text));
}
