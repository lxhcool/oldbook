<?php
/**
 * Site header and three-column shell.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

$site_title = oldbook_get_site_title();
$logo_url   = oldbook_get_site_logo_url('full');
$logo_height = oldbook_get_logo_height();
$menu_url   = home_url('/');
$layout_settings = isset($layout_settings) && is_array($layout_settings) ? $layout_settings : oldbook_get_layout_settings();
$app_classes = 'oldbook-app';

if (! $layout_settings['show_left_sidebar']) {
	$app_classes .= ' oldbook-app--no-left-sidebar';
}

if (! $layout_settings['show_right_sidebar']) {
	$app_classes .= ' oldbook-app--no-right-sidebar';
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo('charset'); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php wp_head(); ?>
	</head>
	<body <?php body_class('oldbook-site'); ?>>
		<?php wp_body_open(); ?>
		<a class="oldbook-skip-link" href="#primary"><?php esc_html_e('跳转到主要内容', 'oldbook'); ?></a>

		<div class="<?php echo esc_attr($app_classes); ?>" data-oldbook-app>
			<button class="oldbook-mobile-backdrop" type="button" data-oldbook-menu-close aria-label="<?php esc_attr_e('关闭导航', 'oldbook'); ?>" aria-hidden="true"></button>

			<?php if ($layout_settings['show_left_sidebar']) : ?>
			<aside id="oldbook-sidebar" class="oldbook-sidebar" data-oldbook-sidebar aria-label="<?php esc_attr_e('站点导航', 'oldbook'); ?>">
				<div class="oldbook-sidebar__top">
					<a class="oldbook-brand" href="<?php echo esc_url($menu_url); ?>" rel="home" aria-label="<?php echo esc_attr($site_title); ?>" style="<?php echo esc_attr('--oldbook-logo-height:' . $logo_height . 'px;'); ?>">
						<?php if ($logo_url) : ?>
							<img class="oldbook-brand__logo" src="<?php echo esc_url($logo_url); ?>" alt="">
						<?php else : ?>
							<span class="oldbook-brand__mark" aria-hidden="true"><i></i><i></i></span>
						<?php endif; ?>
					</a>

					<nav class="oldbook-primary-nav">
						<?php
						$locations = get_nav_menu_locations();
						if (! empty($locations['primary'])) :
							wp_nav_menu(
								array(
									'theme_location' => 'primary',
									'container'      => false,
									'menu_class'     => 'oldbook-primary-nav__list',
									'fallback_cb'    => false,
								)
							);
						else :
							?>
							<ul class="oldbook-primary-nav__list">
								<li class="current-menu-item">
									<a href="<?php echo esc_url(home_url('/')); ?>" aria-current="page">
										<span class="oldbook-nav-icon" aria-hidden="true"><?php echo oldbook_icon('activity'); ?></span>
										<span><?php esc_html_e('首页', 'oldbook'); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url(oldbook_get_articles_url()); ?>">
										<span class="oldbook-nav-icon" aria-hidden="true"><?php echo oldbook_icon('edit'); ?></span>
										<span><?php esc_html_e('文章', 'oldbook'); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url(home_url('/dynamics/')); ?>">
										<span class="oldbook-nav-icon" aria-hidden="true"><?php echo oldbook_icon('message-circle'); ?></span>
										<span><?php esc_html_e('动态', 'oldbook'); ?></span>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url(home_url('/bookmarks/')); ?>">
										<span class="oldbook-nav-icon" aria-hidden="true"><?php echo oldbook_icon('link'); ?></span>
										<span><?php esc_html_e('书签', 'oldbook'); ?></span>
									</a>
								</li>
							</ul>
						<?php endif; ?>
					</nav>
				</div>

				<?php if (is_active_sidebar('sidebar-left')) : ?>
					<div class="oldbook-sidebar__widgets">
						<?php dynamic_sidebar('sidebar-left'); ?>
					</div>
				<?php endif; ?>

				<div class="oldbook-sidebar__bottom">
					<div class="oldbook-sidebar__rule" aria-hidden="true"></div>
					<div class="oldbook-sidebar__profile">
						<img src="<?php echo esc_url(oldbook_get_user_avatar_url(48)); ?>" alt="">
						<span class="oldbook-sidebar__profile-dot" aria-hidden="true"></span>
					</div>
					<button class="oldbook-theme-toggle" type="button" data-oldbook-theme-toggle aria-pressed="false" aria-label="<?php esc_attr_e('切换深色模式', 'oldbook'); ?>" title="<?php esc_attr_e('切换深色模式', 'oldbook'); ?>">
						<span data-oldbook-theme-icon="light" aria-hidden="true"><?php echo oldbook_icon('sun'); ?></span>
						<span data-oldbook-theme-icon="dark" aria-hidden="true"><?php echo oldbook_icon('moon'); ?></span>
					</button>
				</div>
			</aside>
			<?php endif; ?>
