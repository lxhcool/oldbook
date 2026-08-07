<?php
/**
 * The right rail: widget area with curated fallbacks.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

$recent_posts = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 4,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

$categories = get_categories(
	array(
		'hide_empty' => true,
		'number'     => 8,
	)
);

$recent_links = get_posts(
	array(
		'post_type'      => 'oldbook_link',
		'post_status'    => 'publish',
		'posts_per_page' => 4,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

$site_name    = get_bloginfo('name');
$site_name    = $site_name ? $site_name : 'oldbook';
$site_tagline = get_bloginfo('description');
$site_tagline = $site_tagline ? $site_tagline : __('动态记录', 'oldbook');
$profile_image_url = oldbook_get_profile_image_url(128);
$site_stats   = oldbook_get_site_stats();
?>

		<?php if (is_active_sidebar('sidebar-1')) : ?>
			<aside class="oldbook-sidebar" aria-label="<?php esc_attr_e('侧栏', 'oldbook'); ?>">
				<?php dynamic_sidebar('sidebar-1'); ?>
			</aside>
		<?php else : ?>
			<aside class="oldbook-sidebar" aria-label="<?php esc_attr_e('侧栏', 'oldbook'); ?>">
				<section class="oldbook-sidebar__panel oldbook-sidebar__identity">
					<div class="oldbook-sidebar__identity-top">
						<div class="oldbook-sidebar__avatar" aria-hidden="true">
							<?php if ($profile_image_url) : ?>
								<img src="<?php echo esc_url($profile_image_url); ?>" alt="">
							<?php else : ?>
								<span><?php echo esc_html(mb_substr($site_name, 0, 1)); ?></span>
							<?php endif; ?>
						</div>
						<div>
							<h2 class="oldbook-sidebar__name"><?php echo esc_html($site_name); ?></h2>
							<p class="oldbook-sidebar__tagline"><?php echo esc_html($site_tagline); ?></p>
						</div>
					</div>
					<dl class="oldbook-sidebar__stats">
						<div>
							<dt><?php esc_html_e('动态', 'oldbook'); ?></dt>
							<dd><?php echo esc_html(number_format_i18n($site_stats['updates'])); ?></dd>
						</div>
						<div>
							<dt><?php esc_html_e('文章', 'oldbook'); ?></dt>
							<dd><?php echo esc_html(number_format_i18n($site_stats['posts'])); ?></dd>
						</div>
						<div>
							<dt><?php esc_html_e('书签', 'oldbook'); ?></dt>
							<dd><?php echo esc_html(number_format_i18n($site_stats['links'])); ?></dd>
						</div>
					</dl>
				</section>
				<section class="oldbook-sidebar__panel">
					<h2 class="oldbook-sidebar__title"><?php esc_html_e('文章目录', 'oldbook'); ?></h2>
					<?php if ($recent_posts) : ?>
						<ol class="oldbook-sidebar__recent">
							<?php $index = 0; ?>
							<?php foreach ($recent_posts as $recent_post) : ?>
								<?php $index++; ?>
								<li>
									<a href="<?php echo esc_url(get_permalink($recent_post)); ?>">
										<span class="oldbook-index" aria-hidden="true"><?php echo esc_html(str_pad((string) $index, 3, '0', STR_PAD_LEFT)); ?></span>
										<span class="oldbook-sidebar__recent-body">
											<?php echo esc_html(get_the_title($recent_post)); ?>
											<time datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $recent_post)); ?>"><?php echo esc_html(get_the_date('Y.m.d', $recent_post)); ?></time>
										</span>
									</a>
								</li>
							<?php endforeach; ?>
						</ol>
					<?php else : ?>
						<p class="oldbook-empty-inline"><?php esc_html_e('还没有文章。', 'oldbook'); ?></p>
					<?php endif; ?>
				</section>

				<?php if ($categories) : ?>
					<section class="oldbook-sidebar__panel">
						<h2 class="oldbook-sidebar__title"><?php esc_html_e('文章分类', 'oldbook'); ?></h2>
						<ul class="oldbook-sidebar__cats">
							<?php foreach ($categories as $category) : ?>
								<li>
									<a href="<?php echo esc_url(get_category_link($category)); ?>">
										<?php echo esc_html($category->name); ?>
										<span><?php echo esc_html(number_format_i18n($category->count)); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>

				<?php if ($recent_links) : ?>
					<section class="oldbook-sidebar__panel">
						<h2 class="oldbook-sidebar__title"><?php esc_html_e('新收录书签', 'oldbook'); ?></h2>
						<ul class="oldbook-sidebar__links">
							<?php foreach ($recent_links as $link) : ?>
								<?php
								$link_url      = oldbook_get_link_url($link->ID);
								$link_icon_url = oldbook_get_link_icon_url($link->ID);
								$link_host     = $link_url ? wp_parse_url($link_url, PHP_URL_HOST) : '';
								?>
								<?php if (! $link_url) : ?>
									<?php continue; ?>
								<?php endif; ?>
								<li>
									<a href="<?php echo esc_url($link_url); ?>" target="_blank" rel="noopener noreferrer">
										<span class="oldbook-link-favicon">
											<?php if ($link_icon_url) : ?>
												<img src="<?php echo esc_url($link_icon_url); ?>" alt="" width="30" height="30" loading="lazy">
											<?php else : ?>
												<?php echo oldbook_icon('link'); ?>
											<?php endif; ?>
										</span>
										<span>
											<?php echo esc_html(get_the_title($link)); ?>
											<small><?php echo esc_html($link_host); ?></small>
										</span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>
			</aside>
		<?php endif; ?>