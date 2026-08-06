<?php
/**
 * Single dynamic view.
 *
 * @package oldbook
 */

get_header();
?>

<main id="primary" class="oldbook-main oldbook-page oldbook-page--dynamic-single">
	<?php while (have_posts()) : the_post(); ?>
		<?php get_template_part('template-parts/content', 'oldbook-update'); ?>
	<?php endwhile; ?>
</main>

<?php
get_sidebar();
get_footer();
