<?php
/**
 * File containing Sensei_Tool_Recalculate_Enrolment class.
 *
 * @package sensei-lms-status
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Sensei_Tool_Recalculate_Enrolment interface.
 *
 * @since 1.0.0
 */
class Sensei_Tool_Recalculate_Enrolment implements Sensei_Tool_Interface {
	/**
	 * Get the ID of the tool.
	 *
	 * @return string
	 */
	public function get_id() {
		return 'recalculate-enrolment';
	}

	/**
	 * Get the name of the tool.
	 *
	 * @return string
	 */
	public function get_name() {
		return __( 'Recalculate Enrolments', 'sensei-lms-status' );
	}

	/**
	 * Get the description of the tool.
	 *
	 * @return string
	 */
	public function get_description() {
		return __( 'Invalidate the cached enrollment and trigger recalculation for all users and courses.', 'sensei-lms-status' );
	}

	/**
	 * Is the tool a single action?
	 *
	 * @return bool
	 */
	public function is_single_action() {
		return true;
	}

	/**
	 * Run the tool.
	 */
	public function run() {
		$enrolment_manager = Sensei_Course_Enrolment_Manager::instance();
		$enrolment_manager->reset_site_salt();

		Sensei_Tools::instance()->add_user_message( __( 'Course enrollment cache has been invalidated and is being recalculated.', 'sensei-lms-status' ) );
	}
}
