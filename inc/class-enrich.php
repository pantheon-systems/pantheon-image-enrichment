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
	 * Alt text meta key name.
	 *
	 * @var string
	 */
	const ALT_TEXT_META_KEY = '_wp_attachment_image_alt';

	/**
	 * Flag for indicating whether or not the attachment was enriched.
	 *
	 * @var string
	 */
	const ENRICHED_META_KEY = 'pie_enriched_image_alt';

	/**
	 * Generate alt text for an attachment if none exists.
	 *
	 * @param integer $attachment_id ID for the attachment.
	 * @return bool
	 */
	public static function generate_alt_text_if_none_exists( $attachment_id ) {
		$alt_text = get_post_meta( $attachment_id, self::ALT_TEXT_META_KEY, true );
		if ( '' !== $alt_text ) {
			return false;
		}
		return self::generate_alt_text_always( $attachment_id );
	}

	/**
	 * Generate alt text for an attachment if none exists or it was previously enriched.
	 *
	 * @param integer $attachment_id ID for the attachment.
	 * @return bool
	 */
	public static function generate_alt_text_if_missing_or_previously_enriched( $attachment_id ) {
		$enriched = get_post_meta( $attachment_id, self::ENRICHED_META_KEY, true );
		$alt_text = get_post_meta( $attachment_id, self::ALT_TEXT_META_KEY, true );
		if ( '' === $alt_text || $enriched ) {
			return self::generate_alt_text_always( $attachment_id );
		}
		return false;
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
		update_post_meta( $attachment_id, self::ALT_TEXT_META_KEY, $alt_text );
		update_post_meta( $attachment_id, self::ENRICHED_META_KEY, 1 );
		return true;
	}

}
