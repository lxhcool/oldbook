<?php
/**
 * The search results template.
 *
 * @package oldbook
 */

get_header();
?>

<main id="primary" class="site-main">
	<header class="page-header">
		<p class="eyebrow"><?php esc_html_e('Search', 'oldbook'); ?></p>
		<h1 class="page-title">
			<?php
			printf(
				esc_html__('Results for %s', 'oldbook'),
				'<span>' . esc_html(get_search_query()) . '</span>'
			);
			?>
		</h1>
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
</main>

<?php
get_sidebar();
get_footer();
