<?php
/**
 * A single dynamic item.
 *
 * @package oldbook
 */

$post_id       = get_the_ID();
$type          = oldbook_get_update_type($post_id);
$type_data     = oldbook_get_update_types();
$title         = get_the_title();
$default_title = oldbook_default_update_title($type);
$content       = get_post_field('post_content', $post_id);
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('oldbook-update oldbook-update--' . $type); ?>>
	<header class="oldbook-update__header">
		<div class="oldbook-update__meta">
			<time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_date('Y年n月j日 H:i')); ?></time>
			<span class="oldbook-update__type">
				<span class="oldbook-update__type-icon"><?php echo oldbook_icon($type_data[$type]['icon']); ?></span>
				<?php echo esc_html(oldbook_get_update_type_label($type)); ?>
			</span>
		</div>
		<?php if ($title && $title !== $default_title) : ?>
			<h2 class="oldbook-update__title">
				<?php if (is_singular('oldbook_update')) : ?>
					<?php echo esc_html($title); ?>
				<?php else : ?>
					<a href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html($title); ?></a>
				<?php endif; ?>
			</h2>
		<?php endif; ?>
	</header>

	<?php if ('text' === $type) : ?>
		<div class="oldbook-update__text">
			<?php echo oldbook_render_plain_text($content); ?>
		</div>
	<?php elseif (in_array($type, array('music', 'video'), true)) : ?>
		<?php $media_url = oldbook_get_update_media_url($post_id, $type); ?>
		<?php $media_source_url = oldbook_clean_url(get_post_meta($post_id, '_oldbook_media_url', true)); ?>
		<?php if ($media_url) : ?>
			<?php if ('music' === $type) : ?>
				<div class="oldbook-player oldbook-player--audio" data-oldbook-player>
					<audio class="oldbook-player__media" preload="metadata" src="<?php echo esc_url($media_url); ?>"></audio>
					<div class="oldbook-player__row">
						<button class="oldbook-player__toggle" type="button" data-oldbook-player-toggle aria-label="<?php esc_attr_e('播放音频', 'oldbook'); ?>">
							<?php echo oldbook_icon('play'); ?>
						</button>
						<div class="oldbook-player__body">
							<div class="oldbook-player__label">
								<strong><?php echo esc_html($title && $title !== $default_title ? $title : __('音乐动态', 'oldbook')); ?></strong>
								<span data-oldbook-player-state><?php esc_html_e('就绪', 'oldbook'); ?></span>
							</div>
							<input class="oldbook-player__progress" type="range" min="0" max="100" value="0" step="0.1" data-oldbook-player-progress aria-label="<?php esc_attr_e('音频进度', 'oldbook'); ?>">
							<div class="oldbook-player__times"><span data-oldbook-player-current>0:00</span><span data-oldbook-player-duration>--:--</span></div>
						</div>
					</div>
				</div>
			<?php else : ?>
				<div class="oldbook-player oldbook-player--video" data-oldbook-player>
					<div class="oldbook-player__video-frame">
						<video class="oldbook-player__media" preload="metadata" playsinline src="<?php echo esc_url($media_url); ?>"></video>
						<button class="oldbook-player__video-toggle" type="button" data-oldbook-player-toggle aria-label="<?php esc_attr_e('播放视频', 'oldbook'); ?>">
							<?php echo oldbook_icon('play'); ?>
						</button>
					</div>
					<div class="oldbook-player__video-controls">
						<button class="oldbook-player__toggle" type="button" data-oldbook-player-toggle aria-label="<?php esc_attr_e('播放视频', 'oldbook'); ?>">
							<?php echo oldbook_icon('play'); ?>
						</button>
						<input class="oldbook-player__progress" type="range" min="0" max="100" value="0" step="0.1" data-oldbook-player-progress aria-label="<?php esc_attr_e('视频进度', 'oldbook'); ?>">
						<div class="oldbook-player__times"><span data-oldbook-player-current>0:00</span><span data-oldbook-player-duration>--:--</span></div>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>
		<?php if ($media_source_url) : ?>
			<p class="oldbook-player__source"><a href="<?php echo esc_url($media_source_url); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('打开媒体来源', 'oldbook'); ?><?php echo oldbook_icon('external'); ?></a></p>
		<?php endif; ?>
		<?php if (trim($content)) : ?>
			<div class="oldbook-update__note"><?php echo oldbook_render_plain_text($content); ?></div>
		<?php endif; ?>
	<?php elseif ('photo' === $type) : ?>
		<?php $photo_ids = oldbook_get_photo_ids($post_id); ?>
		<?php if ($photo_ids) : ?>
			<div class="oldbook-photo-grid oldbook-photo-grid--count-<?php echo esc_attr(min(9, count($photo_ids))); ?>">
				<?php foreach (array_slice($photo_ids, 0, 9) as $photo_id) : ?>
					<?php $full_url = wp_get_attachment_image_url($photo_id, 'full'); ?>
					<?php if ($full_url) : ?>
						<a class="oldbook-photo-grid__item" href="<?php echo esc_url($full_url); ?>">
							<?php echo wp_get_attachment_image($photo_id, 'large', false, array('loading' => 'lazy', 'alt' => get_post_meta($photo_id, '_wp_attachment_image_alt', true))); ?>
						</a>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php if (trim($content)) : ?>
			<div class="oldbook-update__note"><?php echo oldbook_render_plain_text($content); ?></div>
		<?php endif; ?>
	<?php endif; ?>

	<?php if (! is_singular('oldbook_update')) : ?>
		<footer class="oldbook-update__footer">
			<a href="<?php echo esc_url(get_permalink()); ?>">
				<?php esc_html_e('查看动态', 'oldbook'); ?>
				<?php echo oldbook_icon('arrow-up-right'); ?>
			</a>
		</footer>
	<?php endif; ?>
</article>
