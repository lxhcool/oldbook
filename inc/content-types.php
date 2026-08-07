<?php
/**
 * Public content types used by oldbook.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

function oldbook_register_content_types() {
	register_post_type(
		'oldbook_update',
		array(
			'labels' => array(
				'name'          => __('动态', 'oldbook'),
				'singular_name' => __('动态', 'oldbook'),
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'show_in_rest'        => false,
			'has_archive'         => 'dynamics',
			'rewrite'             => array(
				'slug'       => 'dynamics',
				'with_front' => false,
			),
			'menu_icon'           => 'dashicons-format-status',
			'supports'            => array('title', 'comments'),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'query_var'           => true,
		)
	);

	register_post_type(
		'oldbook_link',
		array(
			'labels' => array(
				'name'          => __('链接', 'oldbook'),
				'singular_name' => __('链接', 'oldbook'),
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'show_in_rest'        => false,
			'has_archive'         => 'bookmarks',
			'rewrite'             => array(
				'slug'       => 'bookmarks',
				'with_front' => false,
			),
			'menu_icon'           => 'dashicons-admin-links',
			'supports'            => array('title'),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'query_var'           => true,
		)
	);
}
add_action('init', 'oldbook_register_content_types');

function oldbook_maybe_flush_rewrites() {
	$version = '2';

	if ($version !== get_option('oldbook_rewrite_version')) {
		flush_rewrite_rules();
		update_option('oldbook_rewrite_version', $version);
	}
}
add_action('init', 'oldbook_maybe_flush_rewrites', 99);

function oldbook_activate_content_types() {
	oldbook_register_content_types();
	flush_rewrite_rules();
}
add_action('after_switch_theme', 'oldbook_activate_content_types');

function oldbook_deactivate_content_types() {
	flush_rewrite_rules();
}
add_action('switch_theme', 'oldbook_deactivate_content_types');

function oldbook_register_article_route() {
	add_rewrite_rule('^articles/?$', 'index.php?oldbook_articles=1', 'top');
}
add_action('init', 'oldbook_register_article_route', 20);

function oldbook_add_article_query_var($vars) {
	$vars[] = 'oldbook_articles';

	return $vars;
}
add_filter('query_vars', 'oldbook_add_article_query_var');

function oldbook_load_article_template($template) {
	if (get_query_var('oldbook_articles')) {
		return get_template_directory() . '/page-articles.php';
	}

	return $template;
}
add_filter('template_include', 'oldbook_load_article_template');

function oldbook_redirect_update_single() {
	if (is_singular('oldbook_update') || is_post_type_archive('oldbook_update')) {
		wp_safe_redirect(home_url('/'), 302);
		exit;
	}
}
add_action('template_redirect', 'oldbook_redirect_update_single');
