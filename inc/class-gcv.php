<?php
/**
 * Integrates with the Google Cloud Vision API.
 *
 * @package Pantheon_Image_Enrichment
 */

namespace Pantheon_Image_Enrichment;

use WP_Error;

/**
 * Integrates with the Google Cloud Vision API.
 */
class GCV {

	/**
	 * API endpoint for image enrichment.
	 *
	 * @var string
	 */
	const ENRICHMENT_ENDPOINT = 'https://vision.googleapis.com/v1/images:annotate';

	/**
	 * Get the Google Cloud Vision enrichment data for a given attachment.
	 *
	 * @param integer $attachment_id ID for the attachment.
	 * @param array   $features      Which enrichment features to request.
	 * @return mixed Data object if success, WP_Error if failure.
	 */
	public static function get_attachment_enrichment_data( $attachment_id, $features = array() ) {

		$attachment = get_post( $attachment_id );
		if ( ! $attachment_id || ! $attachment ) {
			return new WP_Error( 'pie-invalid-attachment', __( 'Attachment doesn\'t exist.', 'pantheon-image-enrichment' ) );
		}

		$attached_file = get_attached_file( $attachment_id );
		if ( ! is_readable( $attached_file ) ) {
			return new WP_Error( 'pie-invalid-attachment', __( 'Attachment file doesn\'t exist.', 'pantheon-image-enrichment' ) );
		}

		return self::get_file_enrichment_data( $attached_file, $features );
	}

	/**
	 * Get the Google Cloud Vision enrichment data for a given file.
	 *
	 * @param string $file_path Path to the file to check.
	 * @param array  $features  Which enrichment features to request.
	 * @return mixed Data object if success, WP_Error if failure.
	 */
	public static function get_file_enrichment_data( $file_path, $features = array() ) {

		if ( empty( $features ) ) {
			$features = array( 'LABEL_DETECTION' );
		}

		$request_body = array(
			'image'    => array(
				'content' => base64_encode( file_get_contents( $file_path ) ),
			),
			'features' => array(),
		);
		foreach ( $features as $feature ) {
			switch ( $feature ) {
				case 'LABEL_DETECTION':
					$request_body['features'][] = array(
						'type'       => 'LABEL_DETECTION',
						'maxResults' => 10,
					);
					break;
				case 'LANDMARK_DETECTION':
					$request_body['features'][] = array(
						'type' => 'LANDMARK_DETECTION',
					);
					break;
				case 'SAFE_SEARCH_DETECTION':
					$request_body['features'][] = array(
						'type' => 'SAFE_SEARCH_DETECTION',
					);
					break;
			}
		}

		$request_signature = hash(
			'sha256', serialize(
				array(
					// Strip the digits off the filename.
					// The test suite can increment filenames indefinitely;
					// because we're simply creating a unique-ish hash, it's
					// fine that this value is a little lossy.
					preg_replace( '#-[\d]+$#', '', pathinfo( $file_path, PATHINFO_FILENAME ) ),
					$request_body['features'],
				)
			)
		);
		$request_signature = substr( $request_signature, 0, 8 );

		$request     = array(
			'headers' => array(
				'Content-Type'            => 'application/json',
				'X-PIE-Request-Signature' => $request_signature,
			),
			'body'    => json_encode(
				array(
					'requests' => array(
						$request_body,
					),
				)
			),
			'timeout' => 40,
		);
		$request_url = self::ENRICHMENT_ENDPOINT;
		if ( defined( 'PIE_GCV_API_KEY' ) && PIE_GCV_API_KEY ) {
			$request_url = add_query_arg( 'key', PIE_GCV_API_KEY, $request_url );
		}
		$response = wp_remote_post( $request_url, $request );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$response_body = json_decode( $response_body, true );
		file_put_contents( dirname( __DIR__ ) . '/tests/data/gcv-' . $request_signature . '.json', json_encode( $response_body, JSON_PRETTY_PRINT ) );
		if ( 200 !== $response_code ) {
			// translators: Message communicating the API failure.
			return new WP_Error( 'pie-invalid-response', sprintf( __( 'Error with GCV request: %1$s (HTTP code %2$d)', 'pantheon-image-enrichment' ), $response_body['error']['message'], $response_code ) );
		}
		return $response_body;
	}


}
