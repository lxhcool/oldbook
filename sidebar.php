<?php
/**
 * The right column for widgets.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}
?>
	<div class="oldbook-sidebar">
		<?php if (is_active_sidebar('sidebar-1')) : ?>
			<?php dynamic_sidebar('sidebar-1'); ?>
		<?php endif; ?>
	</div>
