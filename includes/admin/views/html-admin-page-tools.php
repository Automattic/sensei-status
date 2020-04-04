<div class="wrap">
	<h1><?php esc_html_e( 'Tools', 'sensei-lms' ); ?></h1>

	<table id="sensei-tools" class="widefat striped" cellspacing="0">
		<tbody>
		<?php
		foreach ( Sensei_Tools::instance()->get_tools() as $id => $tool ) {
				?>
				<tr>
					<th>
						<p>
							<div class="name"><strong><?php echo esc_html( $tool->get_name() ); ?></strong></div>
							<div class="description"><?php echo esc_html( $tool->get_description() ); ?></div>
						</p>
					</th>
					<td>
						<p>
							<?php
							$label = __( 'Visit', 'sensei-lms-status' );
							$url = sprintf( admin_url( 'admin.php?page=sensei-tools&tool=%s' ), esc_url( $id ) );
							if ( $tool->is_single_action() ) {
								$label = __( 'Run', 'sensei-lms-status' );
								$url = wp_nonce_url( $url, 'sensei-tool-' . $id );
							}
							?>
							<a href="<?php echo esc_url( $url ); ?>" class="button button-large"><?php echo esc_html( $label ); ?></a>
						</p>
					</td>
				</tr>
				<?php
		}
		?>
		</tbody>
	</table>
</div>
