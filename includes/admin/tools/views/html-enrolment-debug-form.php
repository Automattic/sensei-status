<?php
/**
 * File containing enrolment debug tool form.
 *
 * @package sensei-lms-status
 * @since 1.0.0
 *
 * @global false|WP_User $users   List of all users or false if too big.
 * @global false|WP_Post $courses List of all courses or false if too big.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<form method="post" action="">
	<?php wp_nonce_field( Sensei_Tool_Enrolment_Debug::NONCE_ACTION ); ?>
	<table class="form-table" role="presentation">
	<tr class="form-field form-required">
		<th scope="row"><label for="user_id"><?php esc_html_e( 'User', 'sensei-lms-status' ); ?> <span class="description">(<?php esc_html_e( 'Required', 'sensei-lms-status' ); ?>)</span></label></th>
		<td>
		<?php
		$user_value = isset( $_REQUEST['user_id'] ) ? intval( $_REQUEST['user_id'] ) : null;
		if ( $users) {
			?>
			<select name="user_id" id="user_id" class="input">
				<option value="""></option>
				<?php
					foreach ( $users as $user ) {
						echo '<option value="' . intval( $user->ID ) . '"';
						if ( $user_value === $user->ID ) {
							echo ' selected="selected"';
						}
						echo '>';
						echo esc_html( $user->display_name ) .' (' . intval( $user->ID ) . ')';
						echo '</option>';
					}
				?>
			</select>
			<?php
		} else {
			echo '<input type="number" name="user_id" class="input" value="' . esc_attr( $user_value ) . '" size="20" placeholder="' . esc_attr__( 'User ID', 'sensei-lms-status' ) . '">';
		}
		?>
		</td>
	</tr>
		<tr class="form-field form-required">
			<th scope="row"><label for="course_id"><?php esc_html_e( 'Course', 'sensei-lms-status' ); ?> <span class="description">(<?php esc_html_e( 'Required', 'sensei-lms-status' ); ?>)</span></label></th>
			<td>
			<?php
			$course_value = isset( $_REQUEST['course_id'] ) ? intval( $_REQUEST['course_id'] ) : null;
			if ( $users) {
				?>
				<select name="course_id" id="course_id" class="input">
					<option value="""></option>
					<?php
					foreach ( $courses as $course ) {
						echo '<option value="' . intval( $course->ID ) . '"';
						if ( $course_value === $course->ID ) {
							echo ' selected="selected"';
						}
						echo '>';
						echo esc_html( $course->post_title ) .' (' . intval( $course->ID ) . ')';
						echo '</option>';
					}
					?>
				</select>
				<?php
			} else {
				echo '<input type="number" name="course_id" class="input" value="' . esc_attr( $course_value ) . '" size="20" placeholder="' . esc_attr__( 'Course ID', 'sensei-lms-status' ) . '">';
			}
			?>
			</td>
		</tr>
	</table>
	<p class="submit">
		<input type="submit" class="button button-primary" name="submit" value="<?php esc_attr_e( 'View Enrollment Inforation', 'sensei-lms-status' ); ?>" />
	</p>
</form>
