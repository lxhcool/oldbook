<?php
/**
 * Site header: glass top bar, profile banner, and mobile drawer.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

$article_url  = oldbook_get_articles_url();
$article_context = (bool) get_query_var('oldbook_articles') || (is_home() && ! is_front_page()) || is_singular('post') || is_category() || is_tag() || is_post_type_archive('post');
$nav_items    = array(
	array(
		'label'   => __('动态', 'oldbook'),
		'url'     => home_url('/'),
		'current' => is_front_page() || (is_home() && ! get_option('page_for_posts')),
	),
	array(
		'label'   => __('文章', 'oldbook'),
		'url'     => $article_url,
		'current' => $article_context,
	),
	array(
		'label'   => __('书签', 'oldbook'),
		'url'     => get_post_type_archive_link('oldbook_link'),
		'current' => is_post_type_archive('oldbook_link') || is_singular('oldbook_link'),
	),
);

$site_name = get_bloginfo('name');
$site_name = $site_name ? $site_name : 'oldbook';
$site_tagline = get_bloginfo('description');
$site_tagline = $site_tagline ? $site_tagline : __('动态记录', 'oldbook');
$cover_url = get_header_image();

if (! $cover_url) {
	$cover_url = get_template_directory_uri() . '/assets/images/oldbook-profile-cover.jpg';
}

$profile_image_url = oldbook_get_profile_image_url(112);
$site_stats = oldbook_get_site_stats();
?><!doctype html>
<html <?php language_attributes(); ?> data-oldbook-theme="light">
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>
		(function () {
			try {
				var stored = window.localStorage.getItem('oldbook-theme');

				if (stored && ('dark' === stored || 'light' === stored)) {
					document.documentElement.setAttribute('data-oldbook-theme', stored);
				}
			} catch (error) {
				// The page keeps the darkroom theme when storage is unavailable.
			}
		}());
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="oldbook-topbar">
	<div class="oldbook-topbar__inner">
		<a class="oldbook-topbar__brand" href="<?php echo esc_url(home_url('/')); ?>">
			<span class="oldbook-topbar__mark" aria-hidden="true"><?php echo esc_html(mb_substr($site_name, 0, 1)); ?></span>
			<span><?php echo esc_html($site_name); ?></span>
		</a>

		<nav class="oldbook-topbar__nav-wrap" aria-label="<?php esc_attr_e('站点导航', 'oldbook'); ?>">
			<ul class="oldbook-topbar__nav">
				<?php foreach ($nav_items as $nav_item) : ?>
					<?php if (! $nav_item['url']) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<li class="<?php echo $nav_item['current'] ? 'is-current' : ''; ?>">
						<a href="<?php echo esc_url($nav_item['url']); ?>"<?php echo $nav_item['current'] ? ' aria-current="page"' : ''; ?>>
							<?php echo esc_html($nav_item['label']); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<div class="oldbook-topbar__actions">
			<button class="oldbook-topbar__action oldbook-topbar__search" type="button" data-oldbook-search-toggle aria-expanded="false" aria-controls="oldbook-searchbar" aria-label="<?php esc_attr_e('搜索', 'oldbook'); ?>" title="<?php esc_attr_e('搜索', 'oldbook'); ?>">
				<?php echo oldbook_icon('search'); ?>
			</button>
			<button class="oldbook-topbar__action" type="button" data-oldbook-theme-toggle aria-pressed="false" aria-label="<?php esc_attr_e('深色模式', 'oldbook'); ?>" title="<?php esc_attr_e('深色模式', 'oldbook'); ?>">
				<span data-oldbook-theme-icon aria-hidden="true"><?php echo oldbook_icon('moon'); ?></span>
			</button>
			<button class="oldbook-topbar__action oldbook-topbar__menu" type="button" data-oldbook-menu-toggle aria-expanded="false" aria-controls="oldbook-drawer" aria-label="<?php esc_attr_e('菜单', 'oldbook'); ?>" title="<?php esc_attr_e('菜单', 'oldbook'); ?>">
				<?php echo oldbook_icon('menu'); ?>
			</button>
		</div>
	</div>

	<div id="oldbook-searchbar" class="oldbook-searchbar" data-oldbook-searchbar>
		<div class="oldbook-searchbar__inner">
			<?php get_search_form(); ?>
		</div>
	</div>
</header>

<div class="oldbook-overlay" data-oldbook-overlay aria-hidden="true"></div>

<nav id="oldbook-drawer" class="oldbook-drawer" data-oldbook-drawer aria-label="<?php esc_attr_e('站点导航', 'oldbook'); ?>">
	<div class="oldbook-drawer__header">
		<a class="oldbook-topbar__brand" href="<?php echo esc_url(home_url('/')); ?>">
			<span class="oldbook-topbar__mark" aria-hidden="true"><?php echo esc_html(mb_substr($site_name, 0, 1)); ?></span>
			<span><?php echo esc_html($site_name); ?></span>
		</a>
		<button class="oldbook-topbar__action" type="button" data-oldbook-menu-close aria-label="<?php esc_attr_e('关闭菜单', 'oldbook'); ?>" title="<?php esc_attr_e('关闭菜单', 'oldbook'); ?>">
			<?php echo oldbook_icon('x'); ?>
		</button>
	</div>

	<ul class="oldbook-drawer__nav">
		<?php foreach ($nav_items as $nav_item) : ?>
			<?php if (! $nav_item['url']) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<li class="<?php echo $nav_item['current'] ? 'is-current' : ''; ?>">
				<a href="<?php echo esc_url($nav_item['url']); ?>"<?php echo $nav_item['current'] ? ' aria-current="page"' : ''; ?>>
					<span><?php echo esc_html($nav_item['label']); ?></span>
					<?php echo oldbook_icon('arrow-right'); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

	<div class="oldbook-drawer__search">
		<h2 class="oldbook-footer__heading"><?php esc_html_e('搜索', 'oldbook'); ?></h2>
		<?php get_search_form(); ?>
	</div>
</nav>

<div class="oldbook-layout">
	<section class="oldbook-layout__banner">
		<div class="oldbook-banner">
			<div class="oldbook-banner__cover" style="<?php echo esc_attr('background-image: url(' . esc_url($cover_url) . ');'); ?>" aria-hidden="true"></div>
			<div class="oldbook-banner__content">
				<div class="oldbook-banner__avatar" aria-hidden="true">
					<?php if ($profile_image_url) : ?>
						<img src="<?php echo esc_url($profile_image_url); ?>" alt="">
					<?php else : ?>
						<span><?php echo esc_html(mb_substr($site_name, 0, 1)); ?></span>
					<?php endif; ?>
				</div>
				<div class="oldbook-banner__identity">
					<h1><?php echo esc_html($site_name); ?></h1>
					<p><?php echo esc_html($site_tagline); ?></p>
				</div>
			</div>
		</div>
	</section>
