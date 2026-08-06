<?php
/**
 * The single post template.
 *
 * @package oldbook
 */

get_header();
?>

<div id="primary" class="oldbook-main">
	<?php while (have_posts()) : the_post(); ?>
		<?php get_template_part('template-parts/content', get_post_type()); ?>

		<?php
		the_post_navigation(
			array(
				'prev_text' => '<span class="nav-subtitle">' . esc_html__('上一篇', 'oldbook') . '</span><span class="nav-title">%title</span>',
				'next_text' => '<span class="nav-subtitle">' . esc_html__('下一篇', 'oldbook') . '</span><span class="nav-title">%title</span>',
			)
		);
		?>

		<?php if (comments_open() || get_comments_number()) : ?>
			<?php comments_template(); ?>
		<?php endif; ?>
	<?php endwhile; ?>
</div>

<?php
get_sidebar();
get_footer();
