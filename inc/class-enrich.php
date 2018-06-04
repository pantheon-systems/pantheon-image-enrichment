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
		if ( '' !== self::get_attachment_alt_text( $attachment_id ) ) {
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
		if ( '' === self::get_attachment_alt_text( $attachment_id )
			|| self::is_attachment_enriched( $attachment_id ) ) {
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
		$enrichment_data = GCV::get_attachment_enrichment_data(
			$attachment_id, array(
				'LANDMARK_DETECTION',
				'LOGO_DETECTION',
				'LABEL_DETECTION',
			)
		);
		if ( is_wp_error( $enrichment_data ) ) {
			return false;
		}
		$landmark_bits = array();
		$logo_bits     = array();
		$label_bits    = array();
		if ( ! empty( $enrichment_data['responses'] ) ) {
			foreach ( $enrichment_data['responses'] as $response ) {
				if ( ! empty( $response['landmarkAnnotations'] ) ) {
					foreach ( $response['landmarkAnnotations'] as $annotation ) {
						$landmark_bits[] = $annotation['description'];
					}
				}
				if ( ! empty( $response['logoAnnotations'] ) ) {
					foreach ( $response['logoAnnotations'] as $annotation ) {
						$logo_bits[] = $annotation['description'];
					}
				}
				if ( ! empty( $response['labelAnnotations'] ) ) {
					foreach ( $response['labelAnnotations'] as $annotation ) {
						$label_bits[] = $annotation['description'];
					}
				}
			}
		}
		if ( ! empty( $landmark_bits ) ) {
			$alt_text = implode( ', ', $landmark_bits );
		} elseif ( ! empty( $logo_bits ) ) {
			$alt_text = implode( ', ', $logo_bits );
		} else {
			$alt_text = implode( ', ', $label_bits );
		}
		update_post_meta( $attachment_id, self::ALT_TEXT_META_KEY, $alt_text );
		update_post_meta( $attachment_id, self::ENRICHED_META_KEY, 1 );
		return true;
	}

	/**
	 * Get any LIKELY or VERY_LIKELY Google Safe Search violations.
	 *
	 * @param string $file_path Path to the file to check.
	 * @return array
	 */
	public static function get_likely_safe_search_violations( $file_path ) {
		if ( ! is_readable( $file_path ) ) {
			return array();
		}
		$enrichment_data = GCV::get_file_enrichment_data( $file_path, array( 'SAFE_SEARCH_DETECTION' ) );
		if ( is_wp_error( $enrichment_data ) ) {
			return array();
		}
		$likely_violations = array();
		if ( ! empty( $enrichment_data['responses'] ) ) {
			foreach ( $enrichment_data['responses'] as $response ) {
				if ( ! empty( $response['safeSearchAnnotation'] ) ) {
					foreach ( $response['safeSearchAnnotation'] as $violation => $status ) {
						if ( in_array( $status, array( 'LIKELY', 'VERY_LIKELY' ), true ) ) {
							$likely_violations[] = $violation;
						}
					}
				}
			}
		}
		$likely_violations = array_unique( $likely_violations );
		return $likely_violations;
	}

	/**
	 * Get the alt text for an attachment.
	 *
	 * @param integer $attachment_id ID for the attachment.
	 * @return string
	 */
	public static function get_attachment_alt_text( $attachment_id ) {
		return get_post_meta( $attachment_id, self::ALT_TEXT_META_KEY, true );
	}

	/**
	 * Whether or not the attachment is enriched.
	 *
	 * @param integer $attachment_id ID for the attachment.
	 * @return boolean
	 */
	public static function is_attachment_enriched( $attachment_id ) {
		return (bool) get_post_meta( $attachment_id, self::ENRICHED_META_KEY, true );
	}

}
