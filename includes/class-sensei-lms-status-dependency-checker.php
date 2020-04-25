<?php
/**
 * File containing the class Sensei_LMS_Status_Dependency_Checker.
 *
 * @package sensei-lms-status
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei Status Extension Dependencies Check
 *
 * @since 1.0.0
 */
class Sensei_LMS_Status_Dependency_Checker {
	const MINIMUM_PHP_VERSION    = '5.6';
	const MINIMUM_SENSEI_VERSION = '3.0.0-dev';

	/**
	 * Checks if system dependencies are met.
	 *
	 * @return bool
	 */
	public static function are_system_dependencies_met() {
		$are_met = true;
		if ( ! self::check_php() ) {
			add_action( 'admin_notices', array( __CLASS__, 'add_php_notice' ) );
			$are_met = false;
		}
		if ( ! $are_met ) {
			add_action( 'admin_init', array( __CLASS__, 'deactivate_self' ) );
		}

		return $are_met;
	}

	/**
	 * Checks if all plugin dependencies are met.
	 *
	 * @return bool
	 */
	public static function are_plugin_dependencies_met() {
		$are_met = true;
		if ( ! self::check_sensei() ) {
			$are_met = false;

			add_action( 'admin_notices', array( __CLASS__, 'add_sensei_notice' ) );
		}

		if ( class_exists( 'Sensei_Status' ) || class_exists( 'Sensei_Tools' ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'add_double_active_notice' ) );
			$are_met = false;
		}

		return $are_met;
	}

	/**
	 * Checks for our PHP version requirement.
	 *
	 * @return bool
	 */
	private static function check_php() {
		return version_compare( phpversion(), self::MINIMUM_PHP_VERSION, '>=' );
	}

	/**
	 * Deactivate self.
	 */
	public static function deactivate_self() {
		deactivate_plugins( SENSEI_LMS_STATUS_PLUGIN_BASENAME );
	}

	/**
	 * Checks for our Sensei dependency.
	 *
	 * @param string $minimum_version Check for a specific minimum version. If not set, it will use the constant.
	 * @return bool
	 */
	private static function check_sensei( $minimum_version = null ) {
		if ( ! class_exists( 'Sensei_Main' ) ) {
			return false;
		}

		if ( ! $minimum_version ) {
			$minimum_version = self::MINIMUM_SENSEI_VERSION;
		}

		return version_compare( $minimum_version, get_option( 'sensei-version' ), '<=' );
	}

	/**
	 * Adds notice in WP Admin that minimum version of PHP is not met.
	 *
	 * @access private
	 */
	public static function add_php_notice() {
		$screen        = get_current_screen();
		$valid_screens = array( 'dashboard', 'plugins', 'plugins-network' );

		if ( ! current_user_can( 'activate_plugins' ) || ! in_array( $screen->id, $valid_screens, true ) ) {
			return;
		}

		// translators: %1$s is version of PHP that this plugin requires; %2$s is the version of PHP WordPress is running on.
		$message = sprintf( __( '<strong>Sensei LMS Status and Tools</strong> requires a minimum PHP version of %1$s, but you are running %2$s.', 'sensei-lms-status' ), self::MINIMUM_PHP_VERSION, phpversion() );
		echo '<div class="error"><p>';
		echo wp_kses( $message, array( 'strong' => array() ) );
		$php_update_url = 'https://wordpress.org/support/update-php/';
		if ( function_exists( 'wp_get_update_php_url' ) ) {
			$php_update_url = wp_get_update_php_url();
		}
		printf(
			'<p><a class="button button-primary" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
			esc_url( $php_update_url ),
			esc_html__( 'Learn more about updating PHP', 'sensei-lms-status' ),
			/* translators: accessibility text */
			esc_html__( '(opens in a new tab)', 'sensei-lms-status' )
		);
		echo '</p></div>';
	}

	/**
	 * Adds the notice in WP Admin that Sensei is required.
	 *
	 * @access private
	 */
	public static function add_sensei_notice() {
		$screen        = get_current_screen();
		$valid_screens = array( 'dashboard', 'plugins', 'plugins-network' );

		if ( ! current_user_can( 'activate_plugins' ) || ! in_array( $screen->id, $valid_screens, true ) ) {
			return;
		}

		// translators: %1$s is the minimum version number of Sensei that is required.
		$message = sprintf( __( '<strong>Sensei LMS Status and Tools</strong> requires that the plugin <strong>Sensei LMS</strong> (minimum version: <strong>%1$s</strong>) is installed and activated.', 'sensei-lms-status' ), self::MINIMUM_SENSEI_VERSION );
		echo '<div class="error"><p>';
		echo wp_kses( $message, array( 'strong' => array() ) );
		echo '</p></div>';
	}

	/**
	 * Adds the notice in WP Admin when it detects this plugin's classes elsewhere.
	 *
	 * @access private
	 */
	public static function add_double_active_notice() {
		$screen        = get_current_screen();
		$valid_screens = array( 'dashboard', 'plugins', 'plugins-network' );

		if ( ! current_user_can( 'activate_plugins' ) || ! in_array( $screen->id, $valid_screens, true ) ) {
			return;
		}

		$message = __( '<strong>Sensei LMS Status and Tools</strong> is no longer required as its functionality has been implemented in Sensei LMS. Please deactivate and uninstall the plugin.', 'sensei-lms-status' );
		echo '<div class="error"><p>';
		echo wp_kses( $message, array( 'strong' => array() ) );
		echo '</p></div>';
	}
}
