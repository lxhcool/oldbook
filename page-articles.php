<?php
/**
 * Articles route frame. The reading list can be added to the central stage later.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

$layout_settings = oldbook_get_layout_settings();

get_header();
?>
			<main id="primary" class="oldbook-main">
				<section class="oldbook-content-stage oldbook-content-stage--articles" aria-label="<?php esc_attr_e('文章内容', 'oldbook'); ?>">
					<?php if ($layout_settings['show_left_sidebar']) : ?>
						<button class="oldbook-cover-menu" type="button" data-oldbook-menu-toggle aria-controls="oldbook-sidebar" aria-expanded="false" aria-label="<?php esc_attr_e('打开导航', 'oldbook'); ?>">
							<?php echo oldbook_icon('menu'); ?>
						</button>
					<?php endif; ?>
					<div class="oldbook-content-stage__head">
						<span class="oldbook-section-mark" aria-hidden="true"></span>
						<span><?php esc_html_e('文章', 'oldbook'); ?></span>
					</div>
				</section>
			</main>

			<?php if ($layout_settings['show_right_sidebar']) : ?>
			<aside class="oldbook-right-rail" aria-label="<?php esc_attr_e('站点侧栏', 'oldbook'); ?>">
				<div class="oldbook-right-rail__widgets">
					<?php dynamic_sidebar('sidebar-1'); ?>
				</div>
			</aside>
			<?php endif; ?>
<?php

get_footer();
