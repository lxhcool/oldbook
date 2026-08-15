<?php
/**
 * Template Name: 文章列表
 *
 * Articles route frame. The reading list can be added to the central stage later.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

$layout_settings = oldbook_get_layout_settings();
$article_cat = absint(get_query_var('article_cat'));
$articles_url = get_permalink(get_queried_object_id());
$articles_url = $articles_url ? $articles_url : home_url('/');

get_header();
?>
			<main id="primary" class="oldbook-main">
				<?php oldbook_render_identity(); ?>

				<?php oldbook_render_category_nav('category', $article_cat, $articles_url); ?>

				<section class="oldbook-content-stage oldbook-content-stage--articles" aria-label="<?php esc_attr_e('文章内容', 'oldbook'); ?>">
					<div class="oldbook-feed">
						<?php
						$feed_args = array(
							'post_type'           => 'post',
							'post_status'         => 'publish',
							'posts_per_page'      => 12,
							'ignore_sticky_posts' => false,
						);

						if ($article_cat) {
							$feed_args['cat'] = $article_cat;
						}

						$feed_query = new WP_Query($feed_args);

						if ($feed_query->have_posts()) :
							while ($feed_query->have_posts()) :
								$feed_query->the_post();
								oldbook_render_article_card();
							endwhile;
							wp_reset_postdata();
						else :
							?>
							<p class="oldbook-feed__empty"><?php esc_html_e('还没有文章。', 'oldbook'); ?></p>
						<?php endif; ?>
					</div>
				</section>
			</main>

			<?php if ($layout_settings['show_right_sidebar']) : ?>
			<aside class="oldbook-right-rail" aria-label="<?php esc_attr_e('站点侧栏', 'oldbook'); ?>">
				<div class="oldbook-right-rail__top">
					<?php if (is_user_logged_in()) : ?>
					<div class="oldbook-account-pop" x-data="{ open: false }">
						<button type="button" class="oldbook-account" x-on:click="open = !open" x-on:click.outside="open = false" x-on:keydown.escape.window="open = false" aria-haspopup="true" x-bind:aria-expanded="open ? 'true' : 'false'" aria-label="<?php esc_attr_e('账户菜单', 'oldbook'); ?>">
							<img src="<?php echo esc_url(oldbook_get_user_avatar_url(40)); ?>" alt="">
							<span class="oldbook-account__dot" aria-hidden="true"></span>
						</button>
						<div class="oldbook-account-menu" x-cloak x-show="open" x-transition:enter="oldbook-pop-enter" x-transition:enter-start="oldbook-pop-enter-from" x-transition:enter-end="oldbook-pop-enter-to" x-transition:leave="oldbook-pop-leave" x-transition:leave-start="oldbook-pop-leave-from" x-transition:leave-end="oldbook-pop-leave-to" role="menu">
							<div class="oldbook-account-menu__head">
								<span class="oldbook-account-menu__name"><?php echo esc_html(wp_get_current_user()->display_name); ?></span>
								<span class="oldbook-account-menu__mail"><?php echo esc_html(wp_get_current_user()->user_email); ?></span>
							</div>
							<a href="<?php echo esc_url(admin_url()); ?>" role="menuitem">
								<?php echo oldbook_icon('dashboard'); ?>
								<span><?php esc_html_e('进入后台', 'oldbook'); ?></span>
							</a>
							<a class="oldbook-account-menu__logout" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" role="menuitem">
								<?php echo oldbook_icon('log-out'); ?>
								<span><?php esc_html_e('退出登录', 'oldbook'); ?></span>
							</a>
						</div>
					</div>
					<?php else : ?>
					<a class="oldbook-account oldbook-account--out" href="<?php echo esc_url(wp_login_url(home_url('/'))); ?>" aria-label="<?php esc_attr_e('登录', 'oldbook'); ?>" title="<?php esc_attr_e('登录', 'oldbook'); ?>">
						<img src="<?php echo esc_url(oldbook_get_user_avatar_url(40)); ?>" alt="">
						<span class="oldbook-account__dot" aria-hidden="true"></span>
					</a>
					<?php endif; ?>
				</div>
				<div class="oldbook-right-rail__widgets">
					<?php dynamic_sidebar('sidebar-1'); ?>
				</div>
			</aside>
			<?php endif; ?>
<?php

get_footer();
