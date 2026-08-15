<?php
/**
 * Single post template.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

$layout_settings = oldbook_get_layout_settings();

get_header();

while (have_posts()) :
	the_post();
	?>
			<main id="primary" class="oldbook-main">
				<?php oldbook_render_identity(); ?>

				<section class="oldbook-content-stage" aria-label="<?php esc_attr_e('文章内容', 'oldbook'); ?>">
					<article class="oldbook-single">
						<header class="oldbook-single__head">
							<?php $post_cats = oldbook_get_post_categories(); ?>
							<?php if ($post_cats) : ?>
								<div class="oldbook-single__cats">
									<?php foreach ($post_cats as $cat) : ?>
										<a class="oldbook-single__cat" href="<?php echo esc_url(get_term_link($cat)); ?>"># <?php echo esc_html($cat->name); ?></a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
							<h1 class="oldbook-single__title"><?php the_title(); ?></h1>
							<div class="oldbook-article__meta">
								<span class="oldbook-article__author">@<?php the_author(); ?></span>
								<time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(oldbook_time_ago(get_the_date('U'))); ?></time>
							</div>
						</header>

						<div class="oldbook-single__content">
							<?php the_content(); ?>
						</div>

						<?php if (comments_open() || get_comments_number()) : ?>
							<div class="oldbook-single__comments">
								<?php comments_template(); ?>
							</div>
						<?php endif; ?>
					</article>
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
endwhile;

get_footer();
