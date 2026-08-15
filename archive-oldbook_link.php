<?php
/**
 * Bookmarks archive: personal bookmarks and friend links, grouped.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

$layout_settings = oldbook_get_layout_settings();

get_header();
?>
			<main id="primary" class="oldbook-main">
				<?php oldbook_render_identity(); ?>

				<section class="oldbook-content-stage" aria-label="<?php esc_attr_e('书签与友链', 'oldbook'); ?>">
					<?php
					$groups = oldbook_get_link_groups();
					$has_links = false;

					foreach ($groups as $group_slug => $group_label) :
						$group_query = new WP_Query(
							array(
								'post_type'      => 'oldbook_link',
								'post_status'    => 'publish',
								'posts_per_page' => 100,
								'meta_key'       => '_oldbook_link_group',
								'meta_value'     => $group_slug,
							)
						);

						if (! $group_query->have_posts()) {
							continue;
						}

						$has_links = true;
						?>
						<section class="oldbook-link-group" aria-label="<?php echo esc_attr($group_label); ?>">
							<h2 class="oldbook-link-group__title">
								<span class="oldbook-link-group__mark" aria-hidden="true"></span>
								<?php echo esc_html($group_label); ?>
							</h2>
							<div class="oldbook-link-grid">
								<?php
								while ($group_query->have_posts()) :
									$group_query->the_post();
									$link_url   = oldbook_get_link_url(get_the_ID());
									$link_icon  = oldbook_get_link_icon_url(get_the_ID());
									$link_desc  = oldbook_get_link_description(get_the_ID());
									$link_host  = $link_url ? wp_parse_url($link_url, PHP_URL_HOST) : '';
									$link_host  = $link_host ? preg_replace('/^www\./', '', $link_host) : '';
									?>
									<a class="oldbook-link-card" href="<?php echo esc_url($link_url); ?>" target="_blank" rel="noopener nofollow">
										<?php if ($link_icon) : ?>
											<img class="oldbook-link-card__icon" src="<?php echo esc_url($link_icon); ?>" alt="" loading="lazy">
										<?php else : ?>
											<span class="oldbook-link-card__icon oldbook-link-card__icon--fallback" aria-hidden="true"><?php echo esc_html(mb_substr(get_the_title(), 0, 1)); ?></span>
										<?php endif; ?>
										<span class="oldbook-link-card__body">
											<strong class="oldbook-link-card__name"><?php the_title(); ?></strong>
											<?php if ($link_desc) : ?>
												<span class="oldbook-link-card__desc"><?php echo esc_html($link_desc); ?></span>
											<?php endif; ?>
											<?php if ($link_host) : ?>
												<span class="oldbook-link-card__host"><?php echo esc_html($link_host); ?></span>
											<?php endif; ?>
										</span>
									</a>
								<?php endwhile; ?>
							</div>
						</section>
						<?php
						wp_reset_postdata();
					endforeach;

					if (! $has_links) :
						?>
						<p class="oldbook-feed__empty"><?php esc_html_e('还没有书签和友链，去后台添加吧。', 'oldbook'); ?></p>
					<?php endif; ?>
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
