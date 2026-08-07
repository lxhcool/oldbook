<?php
/**
 * Theme setup and assets for oldbook.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/content-types.php';
require_once get_template_directory() . '/inc/markdown.php';
require_once get_template_directory() . '/inc/interactions.php';
require_once get_template_directory() . '/inc/admin.php';

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
			'primary' => __('主菜单', 'oldbook'),
			'footer'  => __('底部菜单', 'oldbook'),
		)
	);
}
add_action('after_setup_theme', 'oldbook_setup');

function oldbook_set_content_width() {
	$GLOBALS['content_width'] = 660;
}
add_action('after_setup_theme', 'oldbook_set_content_width', 0);

function oldbook_widgets_init() {
	register_sidebar(
		array(
			'name'          => __('侧栏', 'oldbook'),
			'id'            => 'sidebar-1',
			'description'   => __('添加小工具，它们会显示在右侧栏。', 'oldbook'),
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
			'label' => __('旧书主题', 'oldbook'),
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

	if (is_singular('post')) {
		wp_enqueue_style(
			'oldbook-highlight',
			get_template_directory_uri() . '/assets/css/highlight.css',
			array(),
			$theme->get('Version')
		);
		wp_enqueue_script(
			'oldbook-highlight',
			get_template_directory_uri() . '/assets/js/highlight.min.js',
			array(),
			'11.11.1',
			true
		);
	}

	wp_enqueue_script(
		'oldbook-script',
		get_template_directory_uri() . '/assets/js/oldbook.js',
		is_singular('post') ? array('oldbook-highlight') : array(),
		$theme->get('Version'),
		true
	);

	wp_localize_script(
		'oldbook-script',
		'oldbookInteractions',
		array(
			'ajaxUrl'  => admin_url('admin-ajax.php'),
			'likeNonce' => wp_create_nonce('oldbook_like'),
			'commentNonce' => wp_create_nonce('oldbook_comment'),
		)
	);
}
add_action('wp_enqueue_scripts', 'oldbook_enqueue_styles');
