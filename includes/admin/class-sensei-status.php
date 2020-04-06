<?php
/**
 * File containing Sensei_Status class.
 *
 * @package sensei-lms-status
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Sensei_Status class.
 *
 * @since 1.0.0
 */
class Sensei_Status {
	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Courses constructor. Prevents other instances from being created outside of `self::instance()`.
	 */
	private function __construct() {}

	/**
	 * Fetches an instance of the class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Adds all filters and actions.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_filter( 'debug_information', [ $this, 'add_sensei_debug_info' ] );
		add_filter( 'site_status_tests', [ $this, 'add_sensei_tests' ] );
	}

	/**
	 * Adds Sensei Debug information to the Site Health > Info screen.
	 *
	 * @param array $info Information to show.
	 *
	 * @return array
	 */
	public function add_sensei_debug_info( $info ) {
		$section = [
			'label'  => __( 'Sensei LMS', 'sensei-lms-status' ),
			'fields' => [],
		];

		$section['fields']['version'] = [
			'label' => __( 'Installed Version', 'sensei-lms-status' ),
			'value' => Sensei()->version,
		];

		$is_legacy_enrolment_set       = get_option( 'sensei_enrolment_legacy' );
		$value_is_legacy_enrolment_set = __( 'No, instance never used Sensei LMS v2 or below.', 'sensei-lms-status' );
		if ( $is_legacy_enrolment_set ) {
			$installed_time = gmdate( 'Y-m-d H:i:s', $is_legacy_enrolment_set );
			// translators: Placeholder is datetime for when the instance was upgraded.
			$value_is_legacy_enrolment_set = sprintf( __( 'Yes, instance was upgraded from a pre-v3 version at %s UTC', 'sensei-lms-status' ), $installed_time );
		}

		$section['fields']['legacy_enrolment'] = [
			'label' => __( 'Migrate Legacy Enrollment', 'sensei-lms-status' ),
			'value' => $value_is_legacy_enrolment_set,
			'debug' => $is_legacy_enrolment_set,
		];

		$info['sensei-lms'] = $section;

		return $info;
	}

	/**
	 * Adds Sensei related tests to the Site Health section.
	 *
	 * @param array $tests Tests to calculate.
	 *
	 * @return array
	 */
	public function add_sensei_tests( $tests ) {
		if ( ! isset( $tests['direct'] ) ) {
			$tests['direct'] = [];
		}

		$tests['direct']['calculated_enrollments'] = [
			'label' => __( 'Calculated enrollments', 'sensei-lms-status' ),
			'test'  => [ $this, 'test_calculated_enrollments' ],
		];

		return $tests;
	}

	/**
	 * Tests for calculated enrollments.
	 *
	 * @return array
	 */
	public function test_calculated_enrollments() {
		$result = [
			'label'       => __( 'Learner enrollment has been calculated', 'sensei-lms-status' ),
			'status'      => 'good',
			'badge'       => [
				'label' => __( 'Sensei LMS', 'sensei-lms-status' ),
				'color' => 'blue',
			],
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Sensei LMS attempts to calculate whether learners are enrolled in all courses ahead of time to speed up loading. If this has not been calculated, it could be in-process. Check back later to ensure this has been fixed.', 'sensei-lms-status' )
			),
			'actions'     => '',
			'test'        => 'calculated_enrollments',
		];

		$enrolment_manager = Sensei_Course_Enrolment_Manager::instance();
		if ( get_option( Sensei_Enrolment_Job_Scheduler::CALCULATION_VERSION_OPTION_NAME ) !== $enrolment_manager->get_enrolment_calculation_version() ) {
			$result['status'] = 'recommended';
			$result['label']  = __( 'Learner enrollment has not been calculated', 'sensei-lms-status' );
		}

		return $result;
	}
}
