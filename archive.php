<?php
/**
 * The archive template.
 *
 * @package oldbook
 */

get_header();
?>

<div id="primary" class="oldbook-main">
	<header class="page-header">
		<p class="eyebrow"><?php esc_html_e('Archive', 'oldbook'); ?></p>
		<?php the_archive_title('<h1 class="page-title">', '</h1>'); ?>
		<?php the_archive_description('<div class="archive-description">', '</div>'); ?>
	</header>

	<?php if (have_posts()) : ?>
		<div class="content-list">
			<?php while (have_posts()) : the_post(); ?>
				<?php get_template_part('template-parts/content', get_post_type()); ?>
			<?php endwhile; ?>
		</div>

		<?php
		the_posts_navigation(
			array(
				'prev_text' => __('Older entries', 'oldbook'),
				'next_text' => __('Newer entries', 'oldbook'),
			)
		);
		?>
	<?php else : ?>
		<?php get_template_part('template-parts/content', 'none'); ?>
	<?php endif; ?>
</div>

<?php
get_sidebar();
get_footer();
