<?php
/**
 * All of the points at which the plugin integrates with WordPress.
 *
 * @package Pantheon_Image_Enrichment
 */

namespace Pantheon_Image_Enrichment;

/**
 *  All of the points at which the plugin integrates with WordPress.
 */
class Hooks {

	/**
	 * Automatically generate alt text when a new attachment is uploaded.
	 *
	 * @param integer $attachment_id ID for the newly uploaded attachment.
	 */
	public static function action_add_attachment( $attachment_id ) {
		Enrich::generate_alt_text_if_none_exists( $attachment_id );
	}

	/**
	 * Remove the enrichment key anytime alt text is updated.
	 *
	 * We can assume that if the alt text is updated elsewhere, it's no longer
	 * automatically enriched.
	 *
	 * @param int    $meta_id    ID of updated metadata entry.
	 * @param int    $object_id  Object ID.
	 * @param string $meta_key   Meta key.
	 */
	public static function action_updated_post_meta_remove_key( $meta_id, $object_id, $meta_key ) {
		if ( Enrich::ALT_TEXT_META_KEY !== $meta_key
			|| 'attachment' !== get_post_type( $object_id ) ) {
			return;
		}
		delete_post_meta( $object_id, Enrich::ENRICHED_META_KEY );
	}

	/**
	 * Ensure the image meets Google SafeSearch criteria before processing.
	 *
	 * @param array $file An array of data for a single file.
	 */
	public static function filter_wp_handle_upload_prefilter( $file ) {
		if ( empty( $file['tmp_name'] ) ) {
			return $file;
		}
		if ( empty( $file['type'] ) || 0 !== stripos( $file['type'], 'image/' ) ) {
			return $file;
		}
		$violations = Enrich::get_likely_safe_search_violations( $file['tmp_name'] );
		if ( $violations ) {
			// translators: Communicates all returned likely violations.
			$file['error'] = sprintf( __( 'Image has likely or very likely Google Safe Search violations: %s.', 'pantheon-image-enrichment' ), implode( ', ', $violations ) );
		}
		return $file;
	}
}
