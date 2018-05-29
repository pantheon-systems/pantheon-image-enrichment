<?php
/**
 * Base test class for Pantheon Image Enrichment.
 *
 * @package Pantheon_Image_Enrichment
 */

/**
 * Base test class for Pantheon Image Enrichment.
 */
class Pantheon_Image_Enrichment_Testcase extends WP_UnitTestCase {

	/**
	 * Create an upload object based on provided data.
	 *
	 * @param string  $file   Path to the file to upload.
	 * @param integer $parent Optional parent ID to assign.
	 * @return integer New attachment ID.
	 */
	protected function create_upload_object( $file, $parent = 0 ) {
		$contents = file_get_contents( $file );
		$upload   = wp_upload_bits( basename( $file ), null, $contents );

		$type = '';
		if ( ! empty( $upload['type'] ) ) {
			$type = $upload['type'];
		} else {
			$mime = wp_check_filetype( $upload['file'] );
			if ( $mime ) {
				$type = $mime['type'];
			}
		}

		$attachment = array(
			'post_title'     => basename( $upload['file'] ),
			'post_content'   => '',
			'post_type'      => 'attachment',
			'post_parent'    => $parent,
			'post_mime_type' => $type,
			'guid'           => $upload['url'],
		);

		$id = wp_insert_attachment( $attachment, $upload['file'], $parent );
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );

		return $id;
	}

	/**
	 * Create a $FILES array based on provided filename.
	 *
	 * @param string $file_path Path to file to use.
	 * @return array
	 */
	protected function create_files_array( $file_path ) {
		$files = array();
		$tmp_file = wp_tempnam( $file_path );
		// Replace random data with only random digits.
		$tmp_file = preg_replace( '#-[^\.]+\.#', '-' . mt_rand() . '.', $tmp_file );
		copy( $file_path, $tmp_file );
		$files['file'] = array(
			'tmp_name' => $tmp_file,
			'name'     => basename( $file_path ),
			'type'     => 'image/jpeg',
		);
		return $files;
	}
}
