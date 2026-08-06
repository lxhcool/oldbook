<?php
/**
 * The 404 template.
 *
 * @package oldbook
 */

get_header();
?>

<div id="primary" class="oldbook-main">
	<section class="not-found">
		<p class="eyebrow"><?php esc_html_e('Page not found', 'oldbook'); ?></p>
		<h1 class="page-title"><?php esc_html_e('This page has gone missing.', 'oldbook'); ?></h1>
		<p><?php esc_html_e('Try searching the site or return to the front page.', 'oldbook'); ?></p>
		<?php get_search_form(); ?>
		<a class="button-link" href="<?php echo esc_url(home_url('/')); ?>">
			<?php esc_html_e('Back to the front page', 'oldbook'); ?>
		</a>
	</section>
</div>

<?php
get_sidebar();
get_footer();
