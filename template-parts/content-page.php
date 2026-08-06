<?php
/**
 * Page content.
 *
 * @package oldbook
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('entry entry--page'); ?>>
	<header class="entry-header">
		<?php the_title('<h1 class="entry-title">', '</h1>'); ?>
	</header>

	<?php if (has_post_thumbnail()) : ?>
		<div class="entry-thumbnail entry-thumbnail--static">
			<?php the_post_thumbnail('large', array('loading' => 'lazy')); ?>
		</div>
	<?php endif; ?>

	<div class="entry-content">
		<?php the_content(); ?>

		<?php
		wp_link_pages(
			array(
				'before' => '<nav class="post-pages" aria-label="' . esc_attr__('Page', 'oldbook') . '">',
				'after'  => '</nav>',
			)
		);
		?>
	</div>
</article>
