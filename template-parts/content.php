<?php
/**
 * Default post content.
 *
 * @package oldbook
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('entry' . (is_singular() ? ' entry--single' : '')); ?>>
	<?php if (! is_singular()) : ?>
		<div class="entry-marker" aria-hidden="true">
			<span class="entry-marker__month"><?php echo esc_html(get_the_date('m')); ?></span>
			<strong><?php echo esc_html(get_the_date('d')); ?></strong>
		</div>
	<?php endif; ?>
	<div class="entry-body">
	<header class="entry-header">
		<?php if (! is_singular()) : ?>
			<p class="entry-kicker"><?php esc_html_e('文章', 'oldbook'); ?></p>
		<?php endif; ?>

		<?php if (is_singular()) : ?>
			<?php the_title('<h1 class="entry-title">', '</h1>'); ?>
		<?php else : ?>
			<?php the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '">', '</a></h2>'); ?>
		<?php endif; ?>

		<?php if ('post' === get_post_type()) : ?>
			<div class="entry-meta">
				<time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>">
					<?php echo esc_html(get_the_date()); ?>
				</time>
				<span class="meta-divider" aria-hidden="true">/</span>
				<span><?php echo esc_html(get_the_author()); ?></span>
			</div>
		<?php endif; ?>
	</header>

	<?php if (has_post_thumbnail()) : ?>
		<a class="entry-thumbnail" href="<?php echo esc_url(get_permalink()); ?>" aria-label="<?php the_title_attribute(); ?>">
			<?php the_post_thumbnail('large', array('loading' => 'lazy')); ?>
		</a>
	<?php endif; ?>

	<div class="entry-content">
		<?php if (is_singular()) : ?>
			<?php the_content(); ?>

			<?php
			wp_link_pages(
				array(
					'before' => '<nav class="post-pages" aria-label="' . esc_attr__('页码', 'oldbook') . '">',
					'after'  => '</nav>',
				)
			);
			?>
		<?php else : ?>
			<?php the_excerpt(); ?>
		<?php endif; ?>
	</div>

	<footer class="entry-footer">
		<?php if ('post' === get_post_type()) : ?>
			<span class="entry-taxonomy"><?php the_category(', '); ?></span>
		<?php endif; ?>

		<?php if (! is_singular()) : ?>
			<a class="read-more" href="<?php echo esc_url(get_permalink()); ?>">
				<?php esc_html_e('阅读全文', 'oldbook'); ?>
				<?php echo oldbook_icon('arrow-right'); ?>
			</a>
		<?php endif; ?>
	</footer>
	</div>
</article>
