<?php
/**
 * Site footer: identity, navigation, recent posts, and colophon.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

$site_name     = get_bloginfo('name');
$site_name     = $site_name ? $site_name : 'oldbook';
$site_tagline  = get_bloginfo('description');
$site_tagline  = $site_tagline ? $site_tagline : __('动态记录', 'oldbook');
$footer_items  = array(
	array(
		'label' => __('动态', 'oldbook'),
		'url'   => home_url('/'),
	),
	array(
		'label' => __('文章', 'oldbook'),
		'url'   => oldbook_get_articles_url(),
	),
	array(
		'label' => __('书签', 'oldbook'),
		'url'   => get_post_type_archive_link('oldbook_link'),
	),
);
$recent_posts = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 3,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);
?>
	</div>

	<footer class="oldbook-footer">
		<div class="oldbook-footer__inner">
			<div class="oldbook-footer__identity">
				<a class="oldbook-topbar__brand" href="<?php echo esc_url(home_url('/')); ?>">
					<span class="oldbook-topbar__mark" aria-hidden="true"><?php echo esc_html(mb_substr($site_name, 0, 1)); ?></span>
					<span><?php echo esc_html($site_name); ?></span>
				</a>
				<p><?php echo esc_html($site_tagline); ?></p>
			</div>

			<nav class="oldbook-footer__nav" aria-label="<?php esc_attr_e('页脚导航', 'oldbook'); ?>">
				<h2 class="oldbook-footer__heading"><?php esc_html_e('浏览', 'oldbook'); ?></h2>
				<ul>
					<?php foreach ($footer_items as $footer_item) : ?>
						<?php if (! $footer_item['url']) : ?>
							<?php continue; ?>
						<?php endif; ?>
						<li><a href="<?php echo esc_url($footer_item['url']); ?>"><?php echo esc_html($footer_item['label']); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>

			<div class="oldbook-footer__recent">
				<h2 class="oldbook-footer__heading"><?php esc_html_e('最近文章', 'oldbook'); ?></h2>
				<?php if ($recent_posts) : ?>
					<ul>
						<?php foreach ($recent_posts as $recent_post) : ?>
							<li>
								<a href="<?php echo esc_url(get_permalink($recent_post)); ?>"><?php echo esc_html(get_the_title($recent_post)); ?></a>
								<time datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $recent_post)); ?>"><?php echo esc_html(get_the_date('Y.m.d', $recent_post)); ?></time>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p class="oldbook-empty-inline"><?php esc_html_e('还没有文章。', 'oldbook'); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<div class="oldbook-footer__bottom">
			<div class="oldbook-footer__bottom-inner">
				<span>&copy; <?php echo esc_html(date_i18n('Y')); ?> <?php echo esc_html($site_name); ?></span>
				<span><?php esc_html_e('基于 WordPress', 'oldbook'); ?></span>
			</div>
		</div>
	</footer>

	<?php wp_footer(); ?>
</body>
</html>
