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
		// Prefetch all data for later use.
		GCV::prefetch_file_enrichment_data( $file['tmp_name'] );
		$violations = Enrich::get_likely_safe_search_violations( $file['tmp_name'] );
		if ( $violations ) {
			// translators: Communicates all returned likely violations.
			$file['error'] = sprintf( __( 'Image has likely or very likely Google Safe Search violations: %s.', 'pantheon-image-enrichment' ), implode( ', ', $violations ) );
		}
		return $file;
	}

	/**
	 * Incorporate crop hints into the image cropping process.
	 *
	 * Any image size where 'crop=>true' can be transformed to quadrant-based crop positions.
	 *
	 * @param array $sizes    An associative array of image sizes.
	 * @param array $metadata An associative array of image metadata: width, height, file.
	 * @return array
	 */
	public static function filter_intermediate_image_sizes_advanced( $sizes, $metadata ) {
		if ( empty( $sizes ) ) {
			return $sizes;
		}
		$sizes_to_hint = array();
		foreach ( $sizes as $size => $size_data ) {
			// 'crop' can be an array of values, false, or true.
			// We only want to suggest crop positions when crop=>true.
			if ( ! is_array( $size_data['crop'] ) && $size_data['crop'] ) {
				$sizes_to_hint[] = $size;
			}
		}
		if ( empty( $sizes_to_hint ) ) {
			return $sizes_to_hint;
		}

		$file    = $metadata['file'];
		$uploads = wp_get_upload_dir();
		if ( $file && 0 !== strpos( $file, '/' ) && ! preg_match( '|^.:\\\|', $file ) && ( $uploads && false === $uploads['error'] ) ) {
			$file = $uploads['basedir'] . "/$file";
		}
		$crop_hint = Enrich::get_quadrant_crop_suggestions( $file );
		if ( ! empty( $crop_hint ) ) {
			foreach ( $sizes_to_hint as $size_to_hint ) {
				$sizes[ $size_to_hint ]['crop'] = $crop_hint;
			}
		}
		return $sizes;
	}
}
