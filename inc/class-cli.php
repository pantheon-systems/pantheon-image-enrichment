<?php
/**
 * WP-CLI commands for managing Pantheon Image Enrichment.
 *
 * @package Pantheon_Image_Enrichment
 */

namespace Pantheon_Image_Enrichment;

use WP_CLI;
use WP_CLI\Utils;
use WP_Query;

/**
 * Manage Pantheon Image Enrichment.
 */
class CLI {

	/**
	 * Generate alt text for attachments.
	 *
	 * ## OPTIONS
	 *
	 * [<attachment-id>...]
	 * : One or more IDs of the attachments to regenerate.
	 *
	 * [--force]
	 * : Always generate alt text, even if some already exists.
	 *
	 * @subcommand generate-alt-text
	 */
	public function generate_alt_text( $args, $assoc_args ) {

		$force = Utils\get_flag_value( $assoc_args, 'force' );

		$query_args = array(
			'post_type'      => 'attachment',
			'post__in'       => $args,
			'post_mime_type' => array( 'image' ),
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);
		$images     = new WP_Query( $query_args );
		$count      = $images->post_count;

		if ( ! $count ) {
			WP_CLI::error( 'No images found.' );
		}

		$successes = 0;
		$errors    = 0;
		foreach ( $images->posts as $id ) {
			$method = $force ? 'generate_alt_text_always' : 'generate_alt_text_if_none_exists';
			if ( Enrich::$method( $id ) ) {
				$successes++;
			} else {
				$errors++;
			}
		}

		Utils\report_batch_operation_results( 'image', 'enrich', $count, $successes, $errors );
	}

}
