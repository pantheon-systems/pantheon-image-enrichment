<?php
/**
 * Plugin Name:     Pantheon Image Enrichment
 * Plugin URI:      https://pantheon.io
 * Description:     Generate default alt text, auto-crop and more.
 * Author:          Pantheon
 * Author URI:      https://pantheon.io
 * Text Domain:     pantheon-image-enrichment
 * Domain Path:     /languages
 * Version:         0.1.0-alpha
 *
 * @package         Pantheon_Image_Enrichment
 */

// Your code starts here.
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
 * Registers the WP-CLI commands.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'pie', 'Pantheon_Image_Enrichment\CLI' );
}
