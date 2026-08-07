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
		<h1 class="page-title"><?php the_archive_title(); ?></h1>
		<?php the_archive_description('<p class="archive-description">', '</p>'); ?>
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
				'prev_text' => __('更早内容', 'oldbook'),
				'next_text' => __('更新内容', 'oldbook'),
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
