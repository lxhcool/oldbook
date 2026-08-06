<?php
/**
 * The right column for widgets.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}
?>
	<?php
	$article_url = get_post_type_archive_link('post');

	if (! $article_url) {
		$posts_page_id = (int) get_option('page_for_posts');
		$article_url   = $posts_page_id ? get_permalink($posts_page_id) : home_url('/');
	}

	$article_context = (is_home() && ! is_front_page()) || is_singular('post') || is_category() || is_tag() || is_post_type_archive('post');
	$nav_items       = array(
		array(
			'label'   => __('首页', 'oldbook'),
			'url'     => home_url('/'),
			'current' => is_front_page() || (is_home() && ! get_option('page_for_posts')),
		),
		array(
			'label'   => __('文章', 'oldbook'),
			'url'     => $article_url,
			'current' => $article_context,
		),
		array(
			'label'   => __('动态', 'oldbook'),
			'url'     => get_post_type_archive_link('oldbook_update'),
			'current' => is_post_type_archive('oldbook_update') || is_singular('oldbook_update'),
		),
		array(
			'label'   => __('书签', 'oldbook'),
			'url'     => get_post_type_archive_link('oldbook_link'),
			'current' => is_post_type_archive('oldbook_link') || is_singular('oldbook_link'),
		),
	);
	?>

	<aside class="oldbook-sidebar" aria-label="<?php esc_attr_e('侧栏', 'oldbook'); ?>">
		<section class="oldbook-sidebar__intro">
			<p class="oldbook-sidebar__brand">oldbook</p>
			<p class="oldbook-sidebar__tagline"><?php esc_html_e('把值得留下的内容放在这里。', 'oldbook'); ?></p>
		</section>

		<nav class="widget oldbook-sidebar__navigation" aria-label="<?php esc_attr_e('站点导航', 'oldbook'); ?>">
			<h2 class="widget-title"><?php esc_html_e('浏览', 'oldbook'); ?></h2>
			<ul>
				<?php foreach ($nav_items as $nav_item) : ?>
					<?php if (! $nav_item['url']) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<li class="<?php echo $nav_item['current'] ? 'is-current' : ''; ?>">
						<a href="<?php echo esc_url($nav_item['url']); ?>"<?php echo $nav_item['current'] ? ' aria-current="page"' : ''; ?>>
							<span><?php echo esc_html($nav_item['label']); ?></span>
							<span class="oldbook-sidebar__arrow" aria-hidden="true"><?php echo oldbook_icon('arrow-right'); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<?php if (is_active_sidebar('sidebar-1')) : ?>
			<div class="oldbook-sidebar__widgets">
				<?php dynamic_sidebar('sidebar-1'); ?>
			</div>
		<?php else : ?>
			<section class="widget oldbook-sidebar__search">
				<h2 class="widget-title"><?php esc_html_e('搜索', 'oldbook'); ?></h2>
				<?php get_search_form(); ?>
			</section>

			<?php
			$recent_posts = get_posts(
				array(
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'posts_per_page' => 4,
					'orderby'        => 'date',
					'order'          => 'DESC',
				)
			);
			?>

			<section class="widget oldbook-sidebar__recent">
				<h2 class="widget-title"><?php esc_html_e('最近文章', 'oldbook'); ?></h2>
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
			</section>
		<?php endif; ?>
	</aside>
