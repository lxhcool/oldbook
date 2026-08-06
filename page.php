<?php
/**
 * The page template.
 *
 * @package oldbook
 */

get_header();
?>

<div id="primary" class="oldbook-main">
	<?php while (have_posts()) : the_post(); ?>
		<?php get_template_part('template-parts/content', 'page'); ?>

		<?php if (comments_open() || get_comments_number()) : ?>
			<?php comments_template(); ?>
		<?php endif; ?>
	<?php endwhile; ?>
</div>

<?php
get_sidebar();
get_footer();
