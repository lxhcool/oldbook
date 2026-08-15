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

function oldbook_set_theme_mod($name, $value) {
	if (function_exists('set_theme_mod')) {
		return set_theme_mod($name, $value);
	}

	return update_theme_mod($name, $value);
}

function oldbook_remove_theme_mod($name) {
	if (function_exists('remove_theme_mod')) {
		return remove_theme_mod($name);
	}

	return delete_theme_mod($name);
}

function oldbook_sanitize_percentage($value) {
	return min(100, max(0, absint($value)));
}

function oldbook_sanitize_cover_height($value) {
	return min(520, max(200, absint($value)));
}

function oldbook_sanitize_logo_height($value) {
	$height = absint($value);

	return min(72, max(16, $height ?: 24));
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

	return $tagline ? $tagline : '';
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

function oldbook_get_logo_height() {
	return oldbook_sanitize_logo_height(get_theme_mod('oldbook_site_logo_height', 24));
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
	$cover_height       = oldbook_sanitize_cover_height(get_theme_mod('oldbook_cover_height', 320));
	$overlay_color      = sanitize_hex_color(get_theme_mod('oldbook_cover_overlay_color', '#11201a'));
	$overlay_color      = $overlay_color ? $overlay_color : '#11201a';
	$overlay_opacity    = oldbook_sanitize_percentage(get_theme_mod('oldbook_cover_overlay_opacity', 26));
	$overlay_alpha      = $overlay_opacity / 100;
	$gradient_enabled   = '1' === (string) get_theme_mod('oldbook_cover_gradient_enabled', false);
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
		'height'            => $cover_height,
		'overlay_color'      => $overlay_color,
		'overlay_opacity'    => $overlay_opacity,
		'gradient_enabled'   => $gradient_enabled,
		'gradient_start'     => $gradient_start ? $gradient_start : '#11201a',
		'gradient_end'       => $gradient_end ? $gradient_end : '#1d7a55',
		'gradient_direction' => $gradient_direction,
		'overlay'            => $cover_overlay,
	);
}

function oldbook_get_layout_settings() {
	return array(
		'show_left_sidebar'  => (bool) get_theme_mod('oldbook_show_left_sidebar', true),
		'show_right_sidebar' => (bool) get_theme_mod('oldbook_show_right_sidebar', true),
		'mini_left_sidebar'  => (bool) get_theme_mod('oldbook_mini_left_sidebar', false),
	);
}

function oldbook_get_home_content() {
	$mode = sanitize_key((string) get_theme_mod('oldbook_home_content', 'articles'));

	return in_array($mode, array('articles', 'updates'), true) ? $mode : 'articles';
}

function oldbook_time_ago($timestamp) {
	$timestamp = absint($timestamp);
	$diff      = time() - $timestamp;

	if ($diff < 60) {
		return __('刚刚', 'oldbook');
	}

	if ($diff < 3600) {
		return sprintf(
			/* translators: %d is the number of minutes. */
			__('%d 分钟前', 'oldbook'),
			floor($diff / 60)
		);
	}

	if (date('Ymd', $timestamp) === date('Ymd', time())) {
		return __('今天', 'oldbook');
	}

	$days = floor($diff / 86400);

	if ($days <= 1) {
		return __('昨天', 'oldbook');
	}

	if ($days < 7) {
		return sprintf(
			/* translators: %d is the number of days. */
			__('%d 天前', 'oldbook'),
			$days
		);
	}

	if ($days < 30) {
		return sprintf(
			/* translators: %d is the number of weeks. */
			__('%d 周前', 'oldbook'),
			floor($days / 7)
		);
	}

	return get_the_date('Y.m.d', $timestamp);
}

function oldbook_get_update_categories($post_id = 0) {
	$post_id = $post_id ? absint($post_id) : get_the_ID();
	$terms   = get_the_terms($post_id, 'update_category');

	return is_wp_error($terms) || ! $terms ? array() : $terms;
}

function oldbook_get_post_categories($post_id = 0) {
	$post_id = $post_id ? absint($post_id) : get_the_ID();
	$terms   = get_the_terms($post_id, 'category');

	return is_wp_error($terms) || ! $terms ? array() : $terms;
}

function oldbook_render_identity() {
	$cover_url       = oldbook_get_cover_image_url();
	$cover_settings  = oldbook_get_cover_settings();
	$site_title      = oldbook_get_site_title();
	$layout_settings = oldbook_get_layout_settings();
	?>
	<section class="oldbook-identity" aria-labelledby="oldbook-identity-title">
		<div class="oldbook-identity__media" style="<?php echo esc_attr('--oldbook-cover-height:' . $cover_settings['height'] . 'px;--oldbook-cover-overlay:' . $cover_settings['overlay'] . ';'); ?>">
			<?php if ($layout_settings['show_left_sidebar']) : ?>
				<button class="oldbook-cover-menu" type="button" data-oldbook-menu-toggle aria-controls="oldbook-sidebar" aria-expanded="false" aria-label="<?php esc_attr_e('打开导航', 'oldbook'); ?>">
					<?php echo oldbook_icon('menu'); ?>
				</button>
			<?php endif; ?>
			<img src="<?php echo esc_url($cover_url); ?>" alt="" width="1600" height="900">
			<span class="oldbook-identity__veil" aria-hidden="true"></span>
			<div class="oldbook-identity__copy">
				<span class="oldbook-kicker"><?php esc_html_e('个人档案', 'oldbook'); ?></span>
				<h1 id="oldbook-identity-title"><?php echo esc_html($site_title); ?></h1>
			</div>
		</div>
	</section>
	<?php
}

function oldbook_render_article_card() {
	$post_cats = oldbook_get_post_categories();
	$feature   = get_the_post_thumbnail_url(get_the_ID(), 'large');

	$excerpt = has_excerpt() ? get_the_excerpt() : get_the_content();
	$excerpt = wp_strip_all_tags((string) $excerpt);
	$excerpt = preg_replace('/\s+/', ' ', $excerpt);

	if (mb_strlen($excerpt) < 40) {
		$content = get_the_content();
		$content = wp_strip_all_tags((string) $content);
		$content = preg_replace('/\s+/', ' ', $content);
		$excerpt = $content ? $content : $excerpt;
	}

	$excerpt = mb_substr($excerpt, 0, 100) . (mb_strlen($excerpt) > 100 ? '…' : '');
	?>
	<article class="oldbook-feed-card oldbook-feed-card--article">
		<?php if ($feature) : ?>
			<a class="oldbook-article__cover" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
				<img src="<?php echo esc_url($feature); ?>" alt="" loading="lazy">
			</a>
		<?php endif; ?>
		<div class="oldbook-article__body">
			<div class="oldbook-article__title-row">
				<h2 class="oldbook-article__title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>
				<span class="oldbook-article__cat"><?php echo $post_cats ? esc_html($post_cats[0]->name) : esc_html__('未分类', 'oldbook'); ?></span>
			</div>
			<div class="oldbook-article__meta">
				<span class="oldbook-article__author">@<?php the_author(); ?></span>
				<span class="oldbook-article__meta-dot" aria-hidden="true"></span>
				<time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(oldbook_time_ago(get_the_date('U'))); ?></time>
			</div>
			<?php if ($excerpt) : ?>
				<p class="oldbook-article__excerpt"><?php echo esc_html($excerpt); ?></p>
			<?php endif; ?>

			<div class="oldbook-article__actions">
				<div class="oldbook-article__actions-left">
					<?php
					$post_id    = get_the_ID();
					$like_count = oldbook_get_like_count($post_id);
					$liked      = oldbook_has_liked($post_id);
					?>
					<button class="oldbook-article__action oldbook-article__like<?php echo $liked ? ' is-liked' : ''; ?>" type="button" data-oldbook-like data-post-id="<?php echo esc_attr($post_id); ?>" aria-pressed="<?php echo $liked ? 'true' : 'false'; ?>" aria-label="<?php echo $liked ? esc_attr__('取消点赞', 'oldbook') : esc_attr__('点赞', 'oldbook'); ?>">
						<span class="oldbook-article__action-icon" aria-hidden="true"><?php echo oldbook_icon('thumbs-up'); ?></span>
						<span data-oldbook-like-count><?php echo esc_html(number_format_i18n($like_count)); ?></span>
					</button>
					<span class="oldbook-article__action oldbook-article__comments">
						<span class="oldbook-article__action-icon" aria-hidden="true"><?php echo oldbook_icon('message-square'); ?></span>
						<?php echo esc_html(number_format_i18n(get_comments_number())); ?>
					</span>
				</div>
				<div class="oldbook-article__share" x-data="{ open: false, copied: false }">
					<button class="oldbook-article__action oldbook-article__share-btn" type="button" x-on:click="open = !open" x-on:click.outside="open = false" x-on:keydown.escape.window="open = false" aria-haspopup="true" x-bind:aria-expanded="open ? 'true' : 'false'">
						<span class="oldbook-article__action-icon" aria-hidden="true"><?php echo oldbook_icon('share'); ?></span>
						<span><?php esc_html_e('分享', 'oldbook'); ?></span>
					</button>
					<div class="oldbook-article__share-menu" x-cloak x-show="open" x-transition:enter="oldbook-pop-enter" x-transition:enter-start="oldbook-pop-enter-from" x-transition:enter-end="oldbook-pop-enter-to" x-transition:leave="oldbook-pop-leave" x-transition:leave-start="oldbook-pop-leave-from" x-transition:leave-end="oldbook-pop-leave-to">
						<a class="oldbook-article__share-item" href="#" x-on:click.prevent="if (navigator.clipboard) { navigator.clipboard.writeText(window.location.href); } else { var input = document.createElement('input'); input.value = window.location.href; document.body.appendChild(input); input.select(); document.execCommand('copy'); input.remove(); } copied = true; window.setTimeout(function () { copied = false; }, 1500)">
							<span><?php esc_html_e('复制链接', 'oldbook'); ?></span>
							<span class="oldbook-article__share-status" x-show="copied" x-cloak><?php esc_html_e('已复制', 'oldbook'); ?></span>
						</a>
						<a class="oldbook-article__share-item" href="<?php echo esc_url('https://service.weibo.com/share/share.php?url=' . rawurlencode(get_permalink()) . '&title=' . rawurlencode(get_the_title())); ?>" target="_blank" rel="noopener nofollow">
							<span><?php esc_html_e('分享到微博', 'oldbook'); ?></span>
						</a>
						<a class="oldbook-article__share-item" href="<?php echo esc_url('https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=' . rawurlencode(get_permalink()) . '&title=' . rawurlencode(get_the_title())); ?>" target="_blank" rel="noopener nofollow">
							<span><?php esc_html_e('分享到 QQ 空间', 'oldbook'); ?></span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</article>
	<?php
}
function oldbook_render_category_nav($taxonomy, $current_id = 0, $base_url = '') {
	if (! taxonomy_exists($taxonomy)) {
		return;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);

	if (is_wp_error($terms) || ! $terms) {
		return;
	}

	$base_url  = $base_url ? $base_url : home_url('/');
	$query_key = 'update_category' === $taxonomy ? 'update_cat' : 'article_cat';
	?>
	<nav class="oldbook-cat-nav" aria-label="<?php echo 'update_category' === $taxonomy ? esc_attr__('动态分类', 'oldbook') : esc_attr__('文章分类', 'oldbook'); ?>">
		<ul>
			<li>
				<a class="oldbook-cat-nav__link<?php echo $current_id ? '' : ' is-active'; ?>" href="<?php echo esc_url($base_url); ?>"><?php esc_html_e('全部', 'oldbook'); ?></a>
			</li>
			<?php foreach ($terms as $term) : ?>
				<li>
					<a class="oldbook-cat-nav__link<?php echo (int) $current_id === (int) $term->term_id ? ' is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg($query_key, $term->term_id, $base_url)); ?>"><?php echo esc_html($term->name); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
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

function oldbook_get_default_avatar_url() {
	$default_file = get_template_directory() . '/assets/images/oldbook-default-avatar.png';

	if (file_exists($default_file)) {
		return get_template_directory_uri() . '/assets/images/oldbook-default-avatar.png';
	}

	return '';
}

function oldbook_get_user_avatar_url($size = 96) {
	$size = absint($size);

	if (is_user_logged_in()) {
		$url = get_avatar_url(
			get_current_user_id(),
			array(
				'size'    => $size,
				'default' => oldbook_get_default_avatar_url(),
			)
		);

		if ($url) {
			return $url;
		}
	}

	return oldbook_get_default_avatar_url();
}

function oldbook_proxy_avatar_url($url, $id_or_email, $args) {
	if (! $url || false === strpos($url, 'gravatar.com')) {
		return $url;
	}

	$mirror = 'https://cravatar.cn/avatar/';

	return $mirror . basename($url);
}
add_filter('get_avatar_url', 'oldbook_proxy_avatar_url', 10, 3);

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
		$host  = wp_parse_url($url, PHP_URL_HOST);
		$query = wp_parse_url($url, PHP_URL_QUERY);
		parse_str((string) $query, $query_args);
		$song_id = ! empty($query_args['id']) ? absint($query_args['id']) : 0;

		if ($host && false !== strpos($host, 'music.163.com') && ! $song_id) {
			$fragment = wp_parse_url($url, PHP_URL_FRAGMENT);
			$fragment = strpos((string) $fragment, '?') ? substr((string) $fragment, strpos((string) $fragment, '?') + 1) : '';
			parse_str($fragment, $fragment_args);
			$song_id = ! empty($fragment_args['id']) ? absint($fragment_args['id']) : 0;
		}

		if ($host && false !== strpos($host, 'music.163.com') && $song_id) {
			$url = 'https://music.163.com/song/media/outer/url?id=' . rawurlencode($song_id) . '.mp3';
		}
	}

	return oldbook_clean_url($url);
}

function oldbook_get_update_song_id($post_id = 0) {
	$post_id = $post_id ? absint($post_id) : get_the_ID();

	if (oldbook_get_update_type($post_id) !== 'music') {
		return 0;
	}

	$url = (string) get_post_meta($post_id, '_oldbook_media_url', true);
	$url = oldbook_clean_url($url);

	if (! $url) {
		return 0;
	}

	$host = wp_parse_url($url, PHP_URL_HOST);

	if (! $host || false === strpos($host, 'music.163.com')) {
		return 0;
	}

	$query = wp_parse_url($url, PHP_URL_QUERY);
	parse_str((string) $query, $query_args);
	$song_id = ! empty($query_args['id']) ? absint($query_args['id']) : 0;

	if (! $song_id) {
		$fragment = wp_parse_url($url, PHP_URL_FRAGMENT);
		$fragment = strpos((string) $fragment, '?') ? substr((string) $fragment, strpos((string) $fragment, '?') + 1) : '';
		parse_str($fragment, $fragment_args);
		$song_id = ! empty($fragment_args['id']) ? absint($fragment_args['id']) : 0;
	}

	return $song_id;
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

function oldbook_icon_map() {
	return array(
		'activity'       => 'shouye',
		'arrow-right'    => 'a-icon_arrowright_linear_light',
		'arrow-up-right' => 'arrow-up-right',
		'chevron-down'   => 'xiala',
		'dashboard'      => 'icon_dashboard_linear_light',
		'edit'           => 'icon_renwul_xian_light',
		'external'       => 'external',
		'heart'          => 'heart',
		'link'           => 'icon_link_linear_light1',
		'log-out'        => 'a-icon_logout_linear_light',
		'menu'           => 'menu',
		'message-circle' => 'icon_message_linear_light',
		'message-square' => 'icon_comment_linear_light',
		'moon'           => 'moon',
		'music'          => 'music',
		'pause'          => 'icon_pause_linear_light',
		'photo'          => 'photo',
		'play'           => 'icon_play_linear_light',
		'plus'           => 'icon_add_linear_light',
		'search'         => 'icon_search_linear_light',
		'send'           => 'send',
		'settings'       => 'shezhi',
		'share'          => 'icon_transfer_linear_light',
		'system'         => 'icon_system_linear_light',
		'sun'            => 'sun',
		'text'           => 'text',
		'thumbs-up'      => 'dianzan2',
		'trash'          => 'icon_delete_linear_light',
		'upload'         => 'icon_upload_linear_light',
		'video'          => 'video',
		'volume'         => 'sound',
		'x'              => 'icon_close_linear_light',
	);
}

function oldbook_icon($name, $class = '') {
	$name = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $name);

	if (! $name) {
		return '';
	}

	$map  = oldbook_icon_map();
	$icon = isset($map[$name]) && $map[$name] ? $map[$name] : $name;

	return oldbook_iconfont($icon, $class);
}

function oldbook_iconfont($name, $class = '') {
	$name = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $name);

	if (! $name) {
		return '';
	}

	$classes = 'oldbook-icon oldbook-iconfont';
	if ($class) {
		$classes .= ' ' . sanitize_html_class($class);
	}

	$symbol = 'icon-' . $name;

	return '<svg class="' . esc_attr($classes) . '" viewBox="0 0 1024 1024" aria-hidden="true" focusable="false"><use href="#' . esc_attr($symbol) . '" xlink:href="#' . esc_attr($symbol) . '"></use></svg>';
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
