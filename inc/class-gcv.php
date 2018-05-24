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
	 * @return mixed Data object if success, WP_Error if failure.
	 */
	public static function get_enrichment_data( $attachment_id, $features = array() ) {

		if ( empty( $features ) ) {
			$features = array( 'LABEL_DETECTION' );
		}

		$attachment = get_post( $attachment_id );
		if ( ! $attachment_id || ! $attachment ) {
			return new WP_Error( 'pie-invalid-attachment', __( 'Attachment doesn\'t exist.', 'pantheon-image-enrichment' ) );
		}

		$attached_file = get_attached_file( $attachment_id );
		if ( ! is_readable( $attached_file ) ) {
			return new WP_Error( 'pie-invalid-attachment', __( 'Attachment file doesn\'t exist.', 'pantheon-image-enrichment' ) );
		}

		$request_body = array(
			'image'    => array(
				'content' => base64_encode( file_get_contents( $attached_file ) ),
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
			}
		}

		$request  = array(
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body'    => json_encode(
				array(
					'requests' => array(
						$request_body,
					),
				)
			),
			'timeout' => 20,
		);
		$request_url = self::ENRICHMENT_ENDPOINT;
		if ( defined( 'GCV_API_KEY' ) && GCV_API_KEY ) {
			$request_url = add_query_arg( 'key', GCV_API_KEY, $request_url );
		}
		$response = wp_remote_post( $request_url, $request );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$response_body = json_decode( $response_body, true );
		if ( 200 !== $response_code ) {
			return new WP_Error( 'pie-invalid-response', sprintf( __( 'Error with GCV request: %1$s (HTTP code %2$d)', 'pantheon-image-enrichment' ), $response_body['error']['message'], $response_code ) );
		}
		return $response_body;
	}

}
