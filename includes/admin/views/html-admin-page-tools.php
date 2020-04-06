<?php
/**
 * File containing view for the tools page.
 *
 * @package sensei-lms-status
 * @since 1.0.0
 *
 * @global Sensei_Tool_Interface[] $tools Array of the tools.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

include __DIR__ . '/html-admin-page-tools-header.php';
?>
<table id="sensei-tools" class="widefat striped" cellspacing="0">
	<tbody>
	<?php
	foreach ( $tools as $id => $tool ) {
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
						$label = __( 'Visit Tool', 'sensei-lms-status' );
						$url   = Sensei_Tools::instance()->get_tool_url( $tool );
						if ( $tool->is_single_action() ) {
							$label = __( 'Run Action', 'sensei-lms-status' );
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
<?php
include __DIR__ . '/html-admin-page-tools-footer.php';
