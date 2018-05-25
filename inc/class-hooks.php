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
}
