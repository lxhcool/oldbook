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
			'supports'            => array('title'),
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
	$version = '1';

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
