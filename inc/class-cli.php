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
 * Generates default alt text and more.
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
	 * [--refresh]
	 * : Also generate new alt text for attachments previously enriched.
	 *
	 * [--force]
	 * : Always generate alt text for attachments, even if some already exists.
	 *
	 * @subcommand generate-alt-text
	 */
	public function generate_alt_text( $args, $assoc_args ) {

		$refresh = Utils\get_flag_value( $assoc_args, 'refresh' );
		$force   = Utils\get_flag_value( $assoc_args, 'force' );
		if ( $refresh && $force ) {
			WP_CLI::error( '--refresh and --force cannot be used at the same time.' );
		}

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
		$skips     = 0;
		foreach ( $images->posts as $id ) {
			$method   = 'generate_alt_text_if_none_exists';
			$alt_text = Enrich::get_attachment_alt_text( $id );
			$enriched = Enrich::is_attachment_enriched( $id );
			if ( $refresh ) {
				$method = 'generate_alt_text_if_missing_or_previously_enriched';
				if ( '' !== $alt_text && ! $enriched ) {
					WP_CLI::warning( sprintf( 'Skipping image #%d because it is unenriched.', $id ) );
					$skips++;
					continue;
				}
			} elseif ( $force ) {
				$method = 'generate_alt_text_always';
			} else {
				if ( '' !== $alt_text ) {
					WP_CLI::warning( sprintf( 'Skipping image #%d because it already has alt text.', $id ) );
					$skips++;
					continue;
				}
			}
			if ( Enrich::$method( $id ) ) {
				$successes++;
				WP_CLI::log( sprintf( 'Generated alt text for image #%d: %s', $id, Enrich::get_attachment_alt_text( $id ) ) );
			} else {
				WP_CLI::warning( sprintf( 'Unknown error generating alt text for image #%d.', $image_id ) );
				$errors++;
			}
		}

		Utils\report_batch_operation_results( 'image', 'enrich', $count, $successes, $errors, $skips );
	}

}
