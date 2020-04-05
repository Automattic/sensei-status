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
 * Sensei_Tool_Interface interface.
 *
 * @since 1.0.0
 */
interface Sensei_Tool_Interface {
	/**
	 * Get the ID of the tool.
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Get the name of the tool.
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Get the description of the tool.
	 *
	 * @return string
	 */
	public function get_description();

	/**
	 * Is the tool a single action?
	 *
	 * @return bool
	 */
	public function is_single_action();

	/**
	 * Run the tool.
	 */
	public function run();
}
