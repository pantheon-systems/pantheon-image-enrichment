<?php
/**
 * Plugin Name:     Pantheon Image Enrichment
 * Plugin URI:      https://pantheon.io
 * Description:     Generate default alt text, block unsafe uploads and more.
 * Author:          Pantheon
 * Author URI:      https://pantheon.io
 * Text Domain:     pantheon-image-enrichment
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Pantheon_Image_Enrichment
 */

/**
 * All of the integration points between the plugin and WordPress.
 */
add_action(
	'add_attachment',
	array(
		'Pantheon_Image_Enrichment\Hooks',
		'action_add_attachment',
	)
);
add_action(
	'updated_post_meta',
	array(
		'Pantheon_Image_Enrichment\Hooks',
		'action_updated_post_meta_remove_key',
	),
	10,
	3
);
add_filter(
	'wp_handle_upload_prefilter',
	array(
		'Pantheon_Image_Enrichment\Hooks',
		'filter_wp_handle_upload_prefilter',
	)
);
add_filter(
	'wp_handle_sideload_prefilter',
	array(
		'Pantheon_Image_Enrichment\Hooks',
		'filter_wp_handle_upload_prefilter',
	)
);
add_filter(
	'wp_handle_mock_upload_prefilter',
	array(
		'Pantheon_Image_Enrichment\Hooks',
		'filter_wp_handle_upload_prefilter',
	)
);
add_filter(
	'intermediate_image_sizes_advanced',
	array(
		'Pantheon_Image_Enrichment\Hooks',
		'filter_intermediate_image_sizes_advanced',
	),
	10,
	2
);

/**
 * Registers the class autoloader.
 */
spl_autoload_register(
	function( $class ) {
			$class = ltrim( $class, '\\' );
		if ( 0 !== stripos( $class, 'Pantheon_Image_Enrichment\\' ) ) {
			return;
		}

			$parts = explode( '\\', $class );
			array_shift( $parts ); // Don't need "Pantheon_Image_Enrichment".
			$last    = array_pop( $parts ); // File should be 'class-[...].php'.
			$last    = 'class-' . $last . '.php';
			$parts[] = $last;
			$file    = dirname( __FILE__ ) . '/inc/' . str_replace( '_', '-', strtolower( implode( $parts, '/' ) ) );
		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

/**
 * Make our prefetch cache group non-persistent.
 */
wp_cache_add_non_persistent_groups( Pantheon_Image_Enrichment\GCV::PREFETCH_CACHE_GROUP );

/**
 * Registers the WP-CLI commands.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'pantheon image', 'Pantheon_Image_Enrichment\CLI' );
}
