<?php
/**
 * Content shown when a query has no results.
 *
 * @package oldbook
 */
?>

<section class="no-results not-found">
	<header class="entry-header">
		<p class="entry-kicker"><?php esc_html_e('Nothing here yet', 'oldbook'); ?></p>
		<h1 class="entry-title"><?php esc_html_e('No entries found.', 'oldbook'); ?></h1>
	</header>

	<div class="entry-content">
		<?php if (is_search()) : ?>
			<p><?php esc_html_e('Try a different search term.', 'oldbook'); ?></p>
			<?php get_search_form(); ?>
		<?php else : ?>
			<p><?php esc_html_e('There is no content to display yet.', 'oldbook'); ?></p>
		<?php endif; ?>
	</div>
</section>
