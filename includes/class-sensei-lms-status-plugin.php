<?php
/**
 * File containing Sensei_LMS_Status_Plugin class.
 *
 * @package sensei-lms-status
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Sensei_LMS_Status_Plugin class.
 *
 * @since 1.0.0
 */
class Sensei_LMS_Status_Plugin {
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
		add_action( 'init', [ $this, 'load_plugin' ] );
	}

	/**
	 * Initialize the plugin.
	 *
	 * @access private
	 */
	public function load_plugin() {
		if ( ! Sensei_LMS_Status_Dependency_Checker::are_plugin_dependencies_met() ) {
			return;
		}

		$this->include_files();

		Sensei_Status::instance()->init();
		Sensei_Tools::instance()->init();

		add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ] );
	}

	/**
	 * Include files used by this plugin.
	 *
	 * @access private
	 */
	private function include_files() {
		include_once __DIR__ . '/admin/class-sensei-status.php';
		include_once __DIR__ . '/admin/class-sensei-tools.php';
		include_once __DIR__ . '/admin/tools/class-sensei-tool-interface.php';
		include_once __DIR__ . '/admin/tools/class-sensei-tool-recalculate-enrolment.php';
		include_once __DIR__ . '/admin/tools/class-sensei-tool-recalculate-course-enrolment.php';
		include_once __DIR__ . '/admin/tools/class-sensei-tool-enrolment-debug.php';
	}

	/**
	 * Register assets for use within plugin.
	 */
	public function register_assets() {
		$plugin_url = trailingslashit( plugins_url( '', SENSEI_LMS_STATUS_PLUGIN_FILE ) );

		wp_register_style( 'sensei-lms-tools', $plugin_url . 'assets/css/admin/tools.css', '', SENSEI_LMS_STATUS_VERSION, 'screen' );
	}
}
