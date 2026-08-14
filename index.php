<?php
/**
 * Main site frame. Content templates can plug into the central column later.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}

$cover_url = oldbook_get_cover_image_url();
$cover_settings = oldbook_get_cover_settings();
$site_title = oldbook_get_site_title();
$layout_settings = oldbook_get_layout_settings();

get_header();
?>
			<main id="primary" class="oldbook-main">
				<section class="oldbook-identity" aria-labelledby="oldbook-identity-title">
					<div class="oldbook-identity__media" style="<?php echo esc_attr('--oldbook-cover-height:' . $cover_settings['height'] . 'px;--oldbook-cover-overlay:' . $cover_settings['overlay'] . ';'); ?>">
						<?php if ($layout_settings['show_left_sidebar']) : ?>
							<button class="oldbook-cover-menu" type="button" data-oldbook-menu-toggle aria-controls="oldbook-sidebar" aria-expanded="false" aria-label="<?php esc_attr_e('打开导航', 'oldbook'); ?>">
								<?php echo oldbook_icon('menu'); ?>
							</button>
						<?php endif; ?>
						<img src="<?php echo esc_url($cover_url); ?>" alt="" width="1600" height="900">
						<span class="oldbook-identity__veil" aria-hidden="true"></span>
						<div class="oldbook-identity__copy">
							<span class="oldbook-kicker"><?php esc_html_e('个人档案', 'oldbook'); ?></span>
							<h1 id="oldbook-identity-title"><?php echo esc_html($site_title); ?></h1>
						</div>
					</div>
				</section>

				<section class="oldbook-content-stage" aria-label="<?php esc_attr_e('主要内容', 'oldbook'); ?>">
					<div class="oldbook-content-stage__head">
						<span class="oldbook-section-mark" aria-hidden="true"></span>
						<span><?php esc_html_e('内容流', 'oldbook'); ?></span>
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
