<?php

$wp_tests_dir     = getenv( 'WP_TESTS_DIR' ) ? getenv( 'WP_TESTS_DIR' ) : '/tmp/wordpress-tests-lib';
$sensei_dir       = getenv( 'SENSEI_PLUGIN_DIR' ) ? getenv( 'SENSEI_PLUGIN_DIR' ) : '/tmp/sensei-master';
$sensei_tests_dir = $sensei_dir . '/tests';

if ( ! is_dir( $sensei_tests_dir ) ) {
	die( "Unable to find $wp_tests_dir testing library. Please set the `WP_TESTS_DIR` to the path of the WordPress testing library" );
}

if ( ! is_dir( $sensei_tests_dir ) ) {
	die( "Unable to find Sensei. Please set the `SENSEI_PLUGIN_DIR` to the path of the plugin" );
}

require_once $wp_tests_dir . '/includes/functions.php';

// Load this plugin.
tests_add_filter(
	'muplugins_loaded',
	function() {
		require_once dirname( __DIR__ ) . '/sensei-lms-status.php';
	},
	20
);

require_once $sensei_tests_dir . '/bootstrap.php';
