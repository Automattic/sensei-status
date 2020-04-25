<?php
/**
 * Plugin Name: Sensei LMS Status and Tools
 * Plugin URI: https://senseilms.com
 * Description: Feature plugin that allows administrators to check the status of Sensei LMS and run several tools.
 * Version: 1.0.1
 * License: GPLv3
 * Requires at least: 4.9
 * Tested up to: 5.4
 * Requires PHP: 5.6
 * Author: Automattic
 * Author URI: https://automattic.com
 * Text Domain: sensei-lms-status
 * Domain path: /lang/
 *
 * @package sensei-lms-status
 */

define( 'SENSEI_LMS_STATUS_VERSION', '1.0.1' );
define( 'SENSEI_LMS_STATUS_PLUGIN_FILE', __FILE__ );
define( 'SENSEI_LMS_STATUS_PLUGIN_DIR', __DIR__ );
define( 'SENSEI_LMS_STATUS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once __DIR__ . '/includes/class-sensei-lms-status-dependency-checker.php';

if ( ! Sensei_LMS_Status_Dependency_Checker::are_system_dependencies_met() ) {
	return;
}

require_once __DIR__ . '/includes/class-sensei-lms-status-plugin.php';

Sensei_LMS_Status_Plugin::instance()->init();

