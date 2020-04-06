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
	const MESSAGES_TRANSIENT_PREFIX  = 'sensei-lms-tools-messages-';
	const MESSAGES_TRANSIENT_TIMEOUT = HOUR_IN_SECONDS;

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

		add_filter( 'sensei_learners_main_column_data', [ $this, 'add_debug_action' ], 10, 3 );
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
		wp_enqueue_style( 'sensei-lms-tools' );

		$tools = $this->get_tools();

		if ( ! empty( $_GET['tool'] ) ) {
			$tool_id  = sanitize_text_field( wp_unslash( $_GET['tool'] ) );
			if ( ! isset( $tools[ $tool_id ] ) ) {
				wp_die( __( 'Invalid tool', 'sensei-lms-status' ) );
			}

			$tool = $tools[ $tool_id ];

			if ( $tool->is_single_action() ) {
				if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'sensei-tool-' . $tool_id ) ) {
					wp_die( __( 'Invalid nonce', 'sensei-lms-status' ) );
				}

				$tool->run();
				wp_safe_redirect( admin_url( 'admin.php?page=sensei-tools' ) );

				exit;
			}

			ob_start();
			$tool->run();
			$output = ob_get_clean();

			$messages = $this->get_user_messages( true );

			include __DIR__ . '/views/html-admin-page-tools-header.php';
			echo $output;
			include __DIR__ . '/views/html-admin-page-tools-footer.php';
		} else {
			$messages = $this->get_user_messages( true );

			include __DIR__ . '/views/html-admin-page-tools.php';
		}
	}

	/**
	 * Get the tool URL.
	 *
	 * @param Sensei_Tool_Interface $tool Tool object.
	 */
	public function get_tool_url( Sensei_Tool_Interface $tool ) {
		$id  = $tool->get_id();
		$url = sprintf( admin_url( 'admin.php?page=sensei-tools&tool=%s' ), $id );
		if ( $tool->is_single_action() ) {
			$url = wp_nonce_url( $url, 'sensei-tool-' . $id );
		}

		return $url;
	}

	/**
	 * Get the user messages.
	 *
	 * @param bool $flush Flush the user messages at the same time.
	 *
	 * @return array
	 */
	private function get_user_messages( $flush = false ) {
		$messages_key = $this->get_user_message_transient_name();
		$messages     = get_transient( $messages_key );

		if ( empty( $messages ) ) {
			$messages = [];
		} else {
			$messages = json_decode( $messages, true );
		}

		if ( $flush ) {
			delete_transient( $messages_key );
		}

		return $messages;
	}

	/**
	 * Add a user message to display on the tools page.
	 *
	 * @param string $message  User message to display.
	 * @param bool   $is_error True this message is an error.
	 *
	 * @return bool
	 */
	public function add_user_message( $message, $is_error = false ) {
		$messages_key = $this->get_user_message_transient_name();
		$messages     = $this->get_user_messages( false );

		$messages[] = [
			'message'  => $message,
			'is_error' => $is_error,
		];

		set_transient( $messages_key, wp_json_encode( $messages ), self::MESSAGES_TRANSIENT_TIMEOUT );

		return true;
	}

	/**
	 * Get the name of the transient that stores user messages.
	 *
	 * @return string
	 */
	private function get_user_message_transient_name() {
		return self::MESSAGES_TRANSIENT_PREFIX . get_current_user_id();
	}

	/**
	 * Get the tools.
	 *
	 * @return Sensei_Tool_Interface[]
	 */
	public function get_tools() {
		if ( ! $this->tools ) {
			$tools   = [];
			$tools[] = new Sensei_Tool_Recalculate_Enrolment();
			$tools[] = new Sensei_Tool_Enrolment_Debug();

			/**
			 * Array of the tools available to Sensei LMS.
			 *
			 * @since 1.0.0
			 *
			 * @param Sensei_Tool_Interface[] $tools Tool objects for Sensei LMS.
			 */
			$tools = apply_filters( 'sensei_lms_status_tools', $tools );

			$this->tools = [];
			foreach ( $tools as $tool ) {
				$this->tools[ $tool->get_id() ] = $tool;
			}
		}

		return $this->tools;
	}

	/**
	 * Add debug button to row.
	 *
	 * @param array $row_data  Row data for learner management.
	 * @param array $item      Activity information for row.
	 * @param int   $course_id Course post ID.
	 *
	 * @return array
	 */
	public function add_debug_action( $row_data, $item, $course_id = null ) {
		/**
		 * Show the enrolment debug button on learner management.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $show_button Whether to show the button.
		 * @param int  $user_id     User ID.
		 * @param int  $course_id   Course ID.
		 */
		$show_button = apply_filters( 'sensei_lms_status_show_enrolment_debug_button', false, $item->user_id, $course_id );
		if (
			! $course_id
			|| ! $show_button
			|| 'course' !== get_post_type( $course_id )
		) {
			return $row_data;
		}

		$button_url = Sensei_Tool_Enrolment_Debug::get_debug_url( $item->user_id, $course_id );
		$row_data['actions'] .= ' <a class="button" href="' . esc_url( $button_url ) . '">' . esc_html__( 'Debug Enrollment', 'sensei-lms-status' ) . '</a>';

		return $row_data;
	}

}
