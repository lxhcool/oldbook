<?php
/**
 * Theme setup and assets for oldbook.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

function oldbook_setup() {
	load_theme_textdomain('oldbook', get_template_directory() . '/languages');

	add_theme_support('automatic-feed-links');
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('align-wide');
	add_theme_support('editor-styles');
	add_theme_support('responsive-embeds');
	add_theme_support('wp-block-styles');
	add_editor_style('editor-style.css');
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 64,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	register_nav_menus(
		array(
			'primary' => __('Primary Menu', 'oldbook'),
			'footer'  => __('Footer Menu', 'oldbook'),
		)
	);
}
add_action('after_setup_theme', 'oldbook_setup');

function oldbook_set_content_width() {
	$GLOBALS['content_width'] = 760;
}
add_action('after_setup_theme', 'oldbook_set_content_width', 0);

function oldbook_widgets_init() {
	register_sidebar(
		array(
			'name'          => __('Sidebar', 'oldbook'),
			'id'            => 'sidebar-1',
			'description'   => __('Add widgets here to show a sidebar beside the content.', 'oldbook'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action('widgets_init', 'oldbook_widgets_init');

function oldbook_register_pattern_categories() {
	register_block_pattern_category(
		'oldbook',
		array(
			'label' => __('oldbook', 'oldbook'),
		)
	);
}
add_action('init', 'oldbook_register_pattern_categories', 5);

function oldbook_enqueue_styles() {
	$theme = wp_get_theme();

	wp_enqueue_style(
		'oldbook-style',
		get_stylesheet_uri(),
		array(),
		$theme->get('Version')
	);
}
add_action('wp_enqueue_scripts', 'oldbook_enqueue_styles');
