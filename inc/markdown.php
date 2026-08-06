<?php
/**
 * Markdown rendering for article detail pages.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

require_once get_template_directory() . '/vendor/Parsedown.php';

function oldbook_get_markdown_parser() {
	static $parser;

	if (! $parser) {
		$parser = new Parsedown();
		$parser->setBreaksEnabled(false);
	}

	return $parser;
}

function oldbook_render_markdown($content) {
	if (! is_string($content) || '' === trim($content)) {
		return '';
	}

	return oldbook_get_markdown_parser()->text($content);
}

function oldbook_filter_markdown_content($content) {
	if (is_admin() || ! is_singular('post') || has_blocks($content)) {
		return $content;
	}

	remove_filter('the_content', 'wpautop');
	remove_filter('the_content', 'shortcode_unautop');

	return oldbook_render_markdown($content);
}
add_filter('the_content', 'oldbook_filter_markdown_content', 9);
