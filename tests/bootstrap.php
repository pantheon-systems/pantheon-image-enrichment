<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Pantheon_Image_Enrichment
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/pantheon-image-enrichment.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

/**
 * Filter API requests to Google Cloud Vision.
 *
 * @param mixed  $pre A potentially short-circuited request.
 * @param array  $r   Original request arguments.
 * @param string $url Request URL.
 * @return mixed
 */
function _filter_api_request( $pre, $r, $url ) {
	if ( 'vision.googleapis.com' !== parse_url( $url, PHP_URL_HOST ) ) {
		return $pre;
	}
	$file_path = dirname( __FILE__ ) . '/data/gcv-' . $r['headers']['X-PIE-Request-Signature'] . '.json';
	if ( file_exists( $file_path ) ) {
		return array(
			'response' => array(
				'code' => 200,
			),
			'body'     => file_get_contents( $file_path ),
		);
	}
	return array(
		'response' => array(
			'code' => 404,
		),
		'body'     => '',
	);
}
tests_add_filter( 'pre_http_request', '_filter_api_request', 10, 3 );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

require dirname( __FILE__ ) . '/class-pantheon-image-enrichment-testcase.php';
