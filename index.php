<?php
/**
 * Front page entry — main frame only.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

	<!-- ===== 头部(可自填) ===== -->

	<!-- ===== 主体框架(在这里写你的 HTML) ===== -->
	<main id="primary">
	
	</main>

	<!-- ===== 底部(可自填) ===== -->

	<?php wp_footer(); ?>
</body>
</html>
