<?php
/**
 * The dynamic home feed — the film strip.
 *
 * @package oldbook
 */

get_header();

$updates = new WP_Query(
	array(
		'post_type'           => 'oldbook_update',
		'post_status'         => 'publish',
		'posts_per_page'      => 10,
		'paged'               => max(1, get_query_var('paged')),
		'ignore_sticky_posts' => true,
	)
);
?>

	<main id="primary" class="oldbook-main oldbook-main--home">
		<header class="oldbook-feed-heading">
			<h2><?php esc_html_e('动态', 'oldbook'); ?></h2>
			<span class="oldbook-feed-heading__count"><?php echo esc_html(number_format_i18n($updates->found_posts)); ?></span>
		</header>

	<?php if ($updates->have_posts()) : ?>
		<div class="oldbook-update-list">
			<?php $updates->the_post(); ?>
			<?php get_template_part('template-parts/content', 'oldbook-update', array('variant' => 'featured')); ?>
			<?php while ($updates->have_posts()) : $updates->the_post(); ?>
				<?php get_template_part('template-parts/content', 'oldbook-update', array('variant' => 'compact')); ?>
			<?php endwhile; ?>
		</div>

		<?php
		the_posts_pagination(
			array(
				'total'     => $updates->max_num_pages,
				'current'   => max(1, get_query_var('paged')),
				'prev_text' => __('上一页', 'oldbook'),
				'next_text' => __('下一页', 'oldbook'),
			)
		);
		?>
		<?php else : ?>
		<section class="oldbook-empty-state">
			<h2><?php esc_html_e('还没有动态。', 'oldbook'); ?></h2>
			<p><?php esc_html_e('发布第一条动态，它会出现在这里。', 'oldbook'); ?></p>
		</section>
	<?php endif; ?>
</main>

<?php
wp_reset_postdata();
get_sidebar();
get_footer();
