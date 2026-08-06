<?php
/**
 * The main template file.
 *
 * @package oldbook
 */

get_header();
?>

<div id="primary" class="oldbook-main">
	<?php if (is_home() && ! is_front_page()) : ?>
		<header class="page-header">
			<p class="eyebrow"><?php esc_html_e('Notebook', 'oldbook'); ?></p>
			<h1 class="page-title"><?php single_post_title(); ?></h1>
		</header>
	<?php endif; ?>

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
