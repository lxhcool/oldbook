<?php
/**
 * The single post template.
 *
 * @package oldbook
 */

get_header();
?>

<main id="primary" class="site-main">
	<?php while (have_posts()) : the_post(); ?>
		<?php get_template_part('template-parts/content', get_post_type()); ?>

		<?php
		the_post_navigation(
			array(
				'prev_text' => '<span class="nav-subtitle">' . esc_html__('Previous entry', 'oldbook') . '</span><span class="nav-title">%title</span>',
				'next_text' => '<span class="nav-subtitle">' . esc_html__('Next entry', 'oldbook') . '</span><span class="nav-title">%title</span>',
			)
		);
		?>

		<?php if (comments_open() || get_comments_number()) : ?>
			<?php comments_template(); ?>
		<?php endif; ?>
	<?php endwhile; ?>
</main>

<?php
get_sidebar();
get_footer();
