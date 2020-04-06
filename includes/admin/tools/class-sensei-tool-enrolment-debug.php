<?php
/**
 * File containing Sensei_Tool_Interface interface.
 *
 * @package sensei-lms-status
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Sensei_Tool_Enrolment_Debug interface.
 *
 * @since 1.0.0
 */
class Sensei_Tool_Enrolment_Debug implements Sensei_Tool_Interface {
	const NONCE_ACTION = 'enrolment-debug';

	/**
	 * Get the ID of the tool.
	 *
	 * @return string
	 */
	public function get_id() {
		return 'enrolment-debug';
	}

	/**
	 * Get the name of the tool.
	 *
	 * @return string
	 */
	public function get_name() {
		return __( 'Debug Course Enrollment', 'sensei-lms-status' );
	}

	/**
	 * Get the description of the tool.
	 *
	 * @return string
	 */
	public function get_description() {
		return __( 'Check what the enrollment status is between a course and learner.', 'sensei-lms-status' );
	}

	/**
	 * Is the tool a single action?
	 *
	 * @return bool
	 */
	public function is_single_action() {
		return false;
	}

	/**
	 * Run the tool.
	 */
	public function run() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce checked in `process_input`.
		if ( ! empty( $_GET['course_id'] ) || ! empty( $_GET['user_id'] ) ) {
			$results = $this->process_input();

			// If there was an error, go back to the tool page.
			if ( ! $results ) {
				wp_safe_redirect( Sensei_Tools::instance()->get_tool_url( $this ) );

				exit;
			}

			include_once __DIR__ . '/views/html-enrolment-debug.php';
		}

		$user_query_args = [
			'number'  => 100,
			'orderby' => 'display_name',
		];
		$user_search     = new WP_User_Query( $user_query_args );

		// phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- Variable used in view.
		$users = false;
		if ( $user_search->get_total() < 100 ) {
			$users = $user_search->get_results();
		}

		$course_query_args = [
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'post_type'      => 'course',
			'post_status'    => 'any',
		];
		$course_search     = new WP_Query( $course_query_args );

		// phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- Variable used in view.
		$courses = false;
		if ( $course_search->found_posts < 100 ) {
			$courses = $course_search->get_posts();
		}

		// phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- Variable used in view.
		$tool_id = $this->get_id();

		include_once __DIR__ . '/views/html-enrolment-debug-form.php';
	}

	/**
	 * Process form input.
	 *
	 * @return false|array
	 */
	private function process_input() {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Don't modify the nonce.
		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), self::NONCE_ACTION ) ) {
			Sensei_Tools::instance()->add_user_message( __( 'Please try again. There was a problem validating your request.', 'sensei-lms-status' ), true );

			return false;
		}

		if ( empty( $_GET['user_id'] ) ) {
			Sensei_Tools::instance()->add_user_message( __( 'Please select a user.', 'sensei-lms-status' ), true );

			return false;
		}

		if ( empty( $_GET['course_id'] ) ) {
			Sensei_Tools::instance()->add_user_message( __( 'Please select a course ID.', 'sensei-lms-status' ), true );

			return false;
		}

		$user_id   = intval( $_GET['user_id'] );
		$course_id = intval( $_GET['course_id'] );

		$user = get_user_by( 'ID', $user_id );
		if ( ! $user ) {
			Sensei_Tools::instance()->add_user_message( __( 'Invalid user ID selected.', 'sensei-lms-status' ), true );

			return false;
		}

		$course = get_post( $course_id );
		if ( ! $course || 'course' !== get_post_type( $course ) ) {
			Sensei_Tools::instance()->add_user_message( __( 'Invalid course ID selected.', 'sensei-lms-status' ), true );

			return false;
		}

		return $this->get_debug_results( $user, $course );
	}

	/**
	 * Get the debug results for a user/course.
	 *
	 * @param WP_User $user   User object.
	 * @param WP_Post $course Course post object.
	 *
	 * @return array
	 */
	private function get_debug_results( WP_User $user, WP_Post $course ) {
		$enrolment_manager = Sensei_Course_Enrolment_Manager::instance();
		$course_enrolment  = Sensei_Course_Enrolment::get_course_instance( $course->ID );
		$provider_results  = $course_enrolment->get_enrolment_check_results( $user->ID );
		$is_enrolled       = $course_enrolment->is_enrolled( $user->ID );

		$results_stale = false;
		if ( ! $provider_results || $provider_results->get_version_hash() !== $course_enrolment->get_current_enrolment_result_version() ) {
			$results_stale    = true;
			$provider_results = $course_enrolment->get_enrolment_check_results( $user->ID );
		}

		$provider_results = $provider_results->get_provider_results();

		$debug_results = [
			'course'        => $course->post_title,
			'user'          => $user->display_name . ' (' . $user->ID . ')',
			'is_enrolled'   => $is_enrolled,
			'results_stale' => $results_stale,
			'results_match' => true,
			'providers'     => [],
		];

		$providers = $enrolment_manager->get_all_enrolment_providers();
		foreach ( $providers as $provider ) {
			$provider_info = [
				'id'             => $provider->get_id(),
				'name'           => $provider->get_name(),
				'handles_course' => $provider->handles_enrolment( $course->ID ),
				'is_enrolled'    => null,
				'debug'          => false,
			];

			if ( $provider_info['handles_course'] ) {
				$provider_info['is_enrolled'] = $provider->is_enrolled( $user->ID, $course->ID );
				if (
					! isset( $provider_results[ $provider->get_id() ] )
					|| $provider_results[ $provider->get_id() ] !== $provider_info['is_enrolled']
				) {
					$debug_results['results_match'] = false;
				}
			} else {
				if ( isset( $provider_results[ $provider->get_id() ] ) ) {
					$debug_results['results_match'] = false;
				}
			}

			if (
				interface_exists( 'Sensei_Course_Enrolment_Provider_Debug_Interface' )
				&& $provider instanceof Sensei_Course_Enrolment_Provider_Debug_Interface
			) {
				$provider_info['debug'] = $provider->debug( $user->ID, $course->ID );
			}

			$debug_results['providers'][ $provider_info['id'] ] = $provider_info;
		}

		return $debug_results;
	}

	/**
	 * Get the URL for the enrolment debug tool for a user/course.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 *
	 * @return string
	 */
	public static function get_enrolment_debug_url( $user_id, $course_id ) {
		return wp_nonce_url(
			add_query_arg(
				[
					'course_id' => $course_id,
					'user_id'   => $user_id,
				],
				Sensei_Tools::instance()->get_tool_url( new self() )
			),
			self::NONCE_ACTION
		);
	}
}
