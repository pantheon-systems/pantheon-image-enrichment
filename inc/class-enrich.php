<?php
/**
 * Provides an interface to enrichment behaviors.
 *
 * @package Pantheon_Image_Enrichment
 */

namespace Pantheon_Image_Enrichment;

/**
 * Provides an interface to enrichment behaviors.
 */
class Enrich {

	/**
	 * Generate alt text for an attachment if none exists.
	 *
	 * @param integer $attachment_id ID for the attachment.
	 * @return bool
	 */
	public static function generate_alt_text_if_none_exists( $attachment_id ) {
		$alt_text = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		if ( '' !== $alt_text ) {
			return false;
		}
		return self::generate_alt_text_always( $attachment_id );
	}

	/**
	 * Always generate alt text, even if some exists already.
	 *
	 * @param integer $attachment_id ID for the attachment.
	 * @return bool
	 */
	public static function generate_alt_text_always( $attachment_id ) {
		$attachment = get_post( $attachment_id );
		if ( ! $attachment_id || ! $attachment ) {
			return false;
		}
		$enrichment_data = GCV::get_enrichment_data( $attachment_id, array( 'LABEL_DETECTION' ) );
		if ( is_wp_error( $enrichment_data ) ) {
			return false;
		}
		$alt_text_bits = array();
		if ( ! empty( $enrichment_data['responses'] ) ) {
			foreach ( $enrichment_data['responses'] as $response ) {
				if ( ! empty( $response['labelAnnotations'] ) ) {
					foreach ( $response['labelAnnotations'] as $annotation ) {
						$alt_text_bits[] = $annotation['description'];
					}
				}
			}
		}
		$alt_text = implode( ', ', $alt_text_bits );
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
		return true;
	}

}
