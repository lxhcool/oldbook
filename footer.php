<?php
/**
 * The footer template.
 *
 * @package oldbook
 */

if (! defined('ABSPATH')) {
	exit;
}
?>
		</div>

		<footer class="site-footer">
			<div class="site-shell site-footer__inner">
				<div>
					<p class="site-footer__title"><?php bloginfo('name'); ?></p>
					<p class="site-footer__meta">
						<?php
						printf(
							esc_html__('A quiet place for good things. %s', 'oldbook'),
							esc_html(wp_date('Y'))
						);
						?>
					</p>
				</div>

				<?php if (has_nav_menu('footer')) : ?>
					<nav class="footer-navigation" aria-label="<?php esc_attr_e('Footer menu', 'oldbook'); ?>">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'footer',
								'container'      => false,
							)
						);
						?>
					</nav>
				<?php endif; ?>
			</div>
		</footer>
	</div>
	<?php wp_footer(); ?>
</body>
</html>
