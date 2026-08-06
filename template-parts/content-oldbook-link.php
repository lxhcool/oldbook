<?php
/**
 * A single bookmark item.
 *
 * @package oldbook
 */

$post_id     = get_the_ID();
$url         = oldbook_get_link_url($post_id);
$icon_url    = oldbook_get_link_icon_url($post_id);
$description = oldbook_get_link_description($post_id);
$host        = wp_parse_url($url, PHP_URL_HOST);
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('oldbook-link-card'); ?>>
	<a class="oldbook-link-card__link" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer">
		<span class="oldbook-link-card__icon">
			<?php if ($icon_url) : ?>
				<img src="<?php echo esc_url($icon_url); ?>" alt="" width="32" height="32" loading="lazy">
			<?php else : ?>
				<?php echo oldbook_icon('link'); ?>
			<?php endif; ?>
		</span>
		<span class="oldbook-link-card__body">
			<strong class="oldbook-link-card__title"><?php the_title(); ?></strong>
			<?php if ($description) : ?>
				<span class="oldbook-link-card__description"><?php echo esc_html($description); ?></span>
			<?php endif; ?>
			<?php if ($host) : ?>
				<span class="oldbook-link-card__host"><?php echo esc_html($host); ?></span>
			<?php endif; ?>
		</span>
		<span class="oldbook-link-card__external"><?php echo oldbook_icon('external'); ?></span>
	</a>
</article>
