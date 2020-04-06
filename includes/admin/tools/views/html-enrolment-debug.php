<?php
/**
 * File containing enrolment debug tool.
 *
 * @package sensei-lms-status
 * @since 1.0.0
 *
 * @global array $results Processed result.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$allowed_debug_html = [
	'a' => [
		'href' => true,
	],
	'strong' => true,
	'em'     => true,
	'span'   => [
		'style' => true,
		'class' => true,
	]
];
?>
<div class="sensei-lms-enrolment-debug">
<table class="form-table sensei-lms-tool-info" role="presentation">
	<tr>
		<th scope="row"><?php esc_html_e( 'User', 'sensei-lms-status' ); ?></th>
		<td>
			<div class="info">
			<?php echo esc_html( $results['user'] ); ?>
			</div>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Course', 'sensei-lms-status' ); ?></th>
		<td>
			<div class="info">
			<?php echo esc_html( $results['course'] ); ?>
			</div>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Is enrolled?', 'sensei-lms-status' ); ?></th>
		<td>
			<?php
			if ( $results['is_enrolled'] ) {
				echo '<div class="info info-positive">';
				echo esc_html__( 'Enrolled', 'sensei-lms-status' );
				echo '</div>';
			} else {
				echo '<div class="info info-negative">';
				echo esc_html__( 'Not Enrolled', 'sensei-lms-status' );
				echo '</div>';
			}
			?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Cached Enrollment Status', 'sensei-lms-status' ); ?></th>
		<td>
			<?php
			if ( $results['results_match'] ) {
				echo '<div class="info info-positive">';
				echo esc_html__( 'Matches Calculated Enrollment', 'sensei-lms-status' );
				echo '</div>';
			} else {
				echo '<div class="info info-negative">';
				echo esc_html__( 'Does Not Match Calculated Enrollment', 'sensei-lms-status' );
				echo '</div>';
			}
			?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Providers', 'sensei-lms-status' ); ?></th>
		<td>
			<?php
			foreach ( $results['providers'] as $provider ) {
				?>
				<div class="provider<?php
				if ( $provider['handles_course'] ) {
					echo ' handles';
				} else {
					echo ' does-not-handle';
				}

				if ( $provider['is_enrolled'] ) {
					echo ' enrolled';
				} else {
					echo ' is-not-enrolled';
				}
				?>">
					<div class="name">
						<?php
						echo esc_html( $provider['name'] );

						if ( ! $provider['handles_course'] ) {
							echo '<div class="tag">';
							echo esc_html__( 'Does Not Handle Course', 'sensei-lms-status' );
							echo '</div>';
						} elseif ( $provider['is_enrolled'] ) {
							echo '<div class="tag">';
							echo esc_html__( 'Enrolls Learner', 'sensei-lms-status' );
							echo '</div>';
						} else {
							echo '<div class="tag">';
							echo esc_html__( 'Does Not Enroll Learner', 'sensei-lms-status' );
							echo '</div>';
						}
						?>
					</div>
					<?php
					if ( ! empty( $provider['debug'] ) ) {
						echo '<div class="debug">';
						foreach ( $provider['debug'] as $message ) {
							echo '<div class="message">';
							echo wp_kses( $message, $allowed_debug_html );
							echo '</div>';
						}
						echo '</div>';
					}
					?>
				</div>
				<?php
			}
			?>
		</td>
	</tr>
</table>
</div>
<hr />
