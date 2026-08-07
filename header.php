<?php
/**
 * Site header: moment-style cover, floating bottom navigation, and search.
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

$site_name           = oldbook_get_site_title();
$site_tagline        = oldbook_get_site_tagline();
$cover_url           = oldbook_get_cover_image_url();
$cover_settings      = oldbook_get_cover_settings();
$profile_signature   = sanitize_textarea_field(get_theme_mod('oldbook_profile_signature', ''));
$cover_overlay_style = '--oldbook-cover-overlay:' . $cover_settings['overlay'] . ';';

$profile_image_url = oldbook_get_profile_image_url(160);
$logo_image_url    = oldbook_get_site_logo_url('thumbnail');

if (! $logo_image_url) {
	$logo_image_url = $profile_image_url;
}
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
				// The page keeps the light theme when storage is unavailable.
			}
		}());
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="oldbook-layout">
	<section class="oldbook-layout__banner">
		<div class="oldbook-banner" style="<?php echo esc_attr($cover_overlay_style); ?>">
			<div class="oldbook-banner__cover" style="<?php echo esc_attr('background-image: url(' . esc_url($cover_url) . ');'); ?>" aria-hidden="true"></div>
			<div class="oldbook-banner__identity">
				<h1><?php echo esc_html($site_name); ?></h1>
				<p><?php echo esc_html($site_tagline); ?></p>
			</div>
			<div class="oldbook-banner__avatar" aria-hidden="true">
				<?php if ($profile_image_url) : ?>
					<img src="<?php echo esc_url($profile_image_url); ?>" alt="">
				<?php else : ?>
					<span><?php echo esc_html(mb_substr($site_name, 0, 1)); ?></span>
				<?php endif; ?>
			</div>
		</div>

		<?php if ($profile_signature) : ?>
			<p class="oldbook-banner__signature"><?php echo esc_html($profile_signature); ?></p>
		<?php endif; ?>

		<nav class="oldbook-bottomnav" aria-label="<?php esc_attr_e('站点导航', 'oldbook'); ?>">
			<a class="oldbook-bottomnav__brand" href="<?php echo esc_url(home_url('/')); ?>">
				<span class="oldbook-bottomnav__logo" aria-hidden="true">
					<?php if ($logo_image_url) : ?>
						<img src="<?php echo esc_url($logo_image_url); ?>" alt="">
					<?php else : ?>
						<span><?php echo esc_html(mb_substr($site_name, 0, 1)); ?></span>
					<?php endif; ?>
				</span>
				<span class="oldbook-bottomnav__brand-name"><?php echo esc_html($site_name); ?></span>
			</a>

			<ul class="oldbook-bottomnav__links">
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

			<div class="oldbook-bottomnav__actions">
				<button class="oldbook-bottomnav__action" type="button" data-oldbook-search-toggle aria-expanded="false" aria-controls="oldbook-searchbar" aria-label="<?php esc_attr_e('搜索', 'oldbook'); ?>" title="<?php esc_attr_e('搜索', 'oldbook'); ?>">
					<?php echo oldbook_icon('search'); ?>
				</button>
				<button class="oldbook-bottomnav__action" type="button" data-oldbook-theme-toggle aria-pressed="false" aria-label="<?php esc_attr_e('深色模式', 'oldbook'); ?>" title="<?php esc_attr_e('深色模式', 'oldbook'); ?>">
					<span data-oldbook-theme-icon aria-hidden="true"><?php echo oldbook_icon('moon'); ?></span>
				</button>
				<a class="oldbook-bottomnav__avatar" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr($site_name); ?>" title="<?php echo esc_attr($site_name); ?>">
					<?php if ($profile_image_url) : ?>
						<img src="<?php echo esc_url($profile_image_url); ?>" alt="">
					<?php else : ?>
						<span><?php echo esc_html(mb_substr($site_name, 0, 1)); ?></span>
					<?php endif; ?>
				</a>
			</div>
		</nav>

		<div id="oldbook-searchbar" class="oldbook-searchbar" data-oldbook-searchbar>
			<div class="oldbook-searchbar__inner">
				<?php get_search_form(); ?>
			</div>
		</div>
	</section>
