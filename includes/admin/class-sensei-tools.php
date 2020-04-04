<?php
/**
 * File containing Sensei_Tools class.
 *
 * @package sensei-lms-status
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Sensei_Tools class.
 *
 * @since 1.0.0
 */
class Sensei_Tools {
	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Instantiated array of tools.
	 *
	 * @var Sensei_Tool_Interface[]
	 */
	private $tools;

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
		add_action( 'admin_menu', [ $this, 'add_menu_pages' ], 90 );
	}

	/**
	 * Adds admin menu pages.
	 */
	public function add_menu_pages() {
		$title = \esc_html__( 'Tools', 'sensei-lms-status' );
		\add_submenu_page( 'sensei', $title, $title, 'manage_sensei', 'sensei-tools', [ $this, 'output' ] );
	}

	/**
	 * Output the tools page.
	 */
	public function output() {
		include_once __DIR__ . '/views/html-admin-page-tools.php';
	}

	/**
	 * Get the tools.
	 *
	 * @return Sensei_Tool_Interface[]
	 */
	public function get_tools() {
		if ( ! $this->tools ) {
			$tools                          = [];
			$tools['recalculate-enrolment'] = new Sensei_Tool_Recalculate_Enrolment();

			/**
			 * Array of the tools available to Sensei LMS.
			 *
			 * @since 1.0.0
			 *
			 * @param Sensei_Tool_Interface[] $tools Tool objects for Sensei LMS.
			 */
			$this->tools = apply_filters( 'sensei_lms_status_tools', $tools );
		}

		return $this->tools;
	}


}
