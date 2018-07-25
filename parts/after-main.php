<footer class="site-footer">

	<section>

	<?php
	if (
		spine_get_option( 'contact_department' ) &&
		spine_get_option( 'contact_streetAddress' ) &&
		spine_get_option( 'contact_addressLocality' ) &&
		spine_get_option( 'contact_postalCode' )
	) {
		?>
		<address class="site-footer-contact-information">
			<span><?php echo esc_attr( spine_get_option( 'contact_department' ) ); ?></span>
			<span><?php
				echo esc_attr( spine_get_option( 'contact_streetAddress' ) ) . ', ';
				echo esc_attr( spine_get_option( 'contact_addressLocality' ) ) . ' ';
				echo esc_attr( spine_get_option( 'contact_postalCode' ) );
			?></span>
		</address>
		<?php
	}
	?>

		<nav class="site-footer-global-links">
			<ul>
				<li><a href="https://my.wsu.edu">myWSU</a></li>
				<li><a href="https://accesscenter.wsu.edu/">Accessibility</a></li>
				<li><a href="https://policies.wsu.edu/">Policies</a></li>
				<li><a href="https://ucomm.wsu.edu/wsu-copyright-policy/">©</a></li>
			</ul>
		</nav>

	</section>

</footer>
