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
		<th scope="row"><?php esc_html_e( 'Enrollment status', 'sensei-lms-status' ); ?></th>
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
		<th scope="row"><?php esc_html_e( 'Course progress status', 'sensei-lms-status' ); ?></th>
		<td>
			<?php
			if ( $results['progress'] ) {
				echo '<div class="info info-positive">';
				echo esc_html( $results['progress']['status'] );
				echo ' (' . $results['progress']['percent_complete'] . '%)';
				echo '</div>';

				echo '<div class="info info-neutral">';
				// translators: %s placeholder is datetime progress was started.
				echo esc_html( sprintf( __( 'Started on %s', 'sensei-lms-status' ), $results['progress']['start_date'] ) );
				echo '</div>';
			} else {
				echo '<div class="info info-negative">';
				echo esc_html__( 'No Progress', 'sensei-lms-status' );
				echo '</div>';
			}
			?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Cached enrollment status', 'sensei-lms-status' ); ?></th>
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

			echo '<div class="info info-neutral">';
			// translators: %s placeholder is datetime results were last calculated
			echo esc_html( sprintf( __( 'Last calculated on %s', 'sensei-lms-status' ), $results['results_time'] ) );
			echo '</div>';
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
					$columns = [];
					if ( ! empty( $provider['debug'] ) ) {
						$column = [];
						$column[] = '<h4>' . __( 'Information', 'sensei-status' ) . '</h4>';
						$column[] = '<div class="debug">';
						foreach ( $provider['debug'] as $message ) {
							$column[] = '<div class="message">';
							$column[] = wp_kses( $message, $allowed_debug_html );
							$column[] = '</div>';
						}
						$column[] = '</div>';

						$columns['info'] = $column;
					}

					if ( ! empty( $provider['logs'] ) ) {
						$column = [];
						$column[] = '<div class="logs">';
						$column[] = '<h4>' . __( 'Logs', 'sensei-status' ) . '</h4>';
						foreach ( $provider['logs'] as $message ) {
							$column[] = '<div class="message">';
							$column[] = '<span class="time">' . Sensei_Tool_Enrolment_Debug::format_date( $message['timestamp'] ) . '</span>';
							$column[] = '<span class="content">' . esc_html( $message['message'] ) . '</span>';
							$column[] = '</div>';
						}
						$column[] = '</div>';

						$columns['logs'] = $column;
					}

					if ( ! empty( $provider['history'] ) ) {
						$column = [];
						$column[] = '<div class="history">';
						$column[] = '<h4>' . __( 'History', 'sensei-status' ) . '</h4>';
						foreach ( $provider['history'] as $history ) {
							$item_class = 'neutral';
							if ( null === $history['enrolment_status'] ) {
								$description = __( 'Stopped handling', 'sensei-status' );
							} elseif ( $history['enrolment_status'] ) {
								$description = __( 'Provided', 'sensei-status' );
								$item_class = 'positive';
							} else {
								$description = __( 'Withdrawn', 'sensei-status' );
								$item_class = 'negative';
							}

							$column[] = '<div class="history-item ' . esc_attr( $item_class ) . '">';
							$column[] = '<span class="time">' . Sensei_Tool_Enrolment_Debug::format_date( $history['timestamp'] ). '</span>';

							$column[] = '<span class="content">';
							$column[] = esc_html( $description );
							$column[] = '</span>';
							$column[] = '</div>';
						}
						$column[] = '</div>';

						$columns['history'] = $column;
					}


					if ( ! empty( $columns ) ) {
						echo '<div class="provider-details">';
						foreach( $columns as $id => $column ) {
							echo '<div class="column column-' . esc_attr( $id ). '">';
							echo implode( $column );
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
