<?php
/**
 * Content shown when a query has no results.
 *
 * @package oldbook
 */
?>

<section class="no-results not-found">
	<header class="entry-header">
		<p class="entry-kicker"><?php esc_html_e('还没有内容', 'oldbook'); ?></p>
		<h1 class="entry-title"><?php esc_html_e('没有找到内容。', 'oldbook'); ?></h1>
	</header>

	<div class="entry-content">
		<?php if (is_search()) : ?>
			<p><?php esc_html_e('换个关键词试试。', 'oldbook'); ?></p>
			<?php get_search_form(); ?>
		<?php else : ?>
			<p><?php esc_html_e('暂时没有可展示的内容。', 'oldbook'); ?></p>
		<?php endif; ?>
	</div>
</section>
