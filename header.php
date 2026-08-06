<?php
/**
 * The header template.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
	<div id="page" class="site">
		<a class="skip-link screen-reader-text" href="#primary">
			<?php esc_html_e('Skip to content', 'oldbook'); ?>
		</a>

		<header class="site-header">
			<div class="site-shell site-header__inner">
				<a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>">
					<span class="site-brand__mark" aria-hidden="true">ob</span>
					<span class="site-brand__text">
						<strong class="site-title"><?php bloginfo('name'); ?></strong>
						<?php if (get_bloginfo('description')) : ?>
							<span class="site-description"><?php bloginfo('description'); ?></span>
						<?php endif; ?>
					</span>
				</a>

				<?php if (has_nav_menu('primary')) : ?>
					<nav class="site-navigation" aria-label="<?php esc_attr_e('Primary menu', 'oldbook'); ?>">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'primary',
								'container'      => false,
							)
						);
						?>
					</nav>
				<?php endif; ?>
			</div>
		</header>

		<div class="site-shell site-layout<?php echo is_active_sidebar('sidebar-1') ? ' has-sidebar' : ''; ?>">
