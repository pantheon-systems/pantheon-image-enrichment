<?php
/**
 * Class EnrichTest
 *
 * @package Pantheon_Image_Enrichment
 */

use Pantheon_Image_Enrichment\Enrich;
use Pantheon_Image_Enrichment\GCV;

/**
 * Tests the Enrich class.
 */
class EnrichTest extends Pantheon_Image_Enrichment_Testcase {

	/**
	 * Alt text should be generated for an attachment when it has none.
	 */
	public function test_generate_alt_text_for_attachment_when_missing() {
		$file          = dirname( __FILE__ ) . '/data/canola.jpg';
		$attachment_id = $this->create_upload_object( $file );
		$this->assertEquals( 'yellow, rapeseed, field, canola, grassland', Enrich::get_attachment_alt_text( $attachment_id ) );
		$this->assertTrue( Enrich::is_attachment_enriched( $attachment_id ) );
	}

	/**
	 * Support for refreshing the alt text when it's already dynamically set.
	 */
	public function test_generate_alt_text_refresh() {
		$file          = dirname( __FILE__ ) . '/data/canola.jpg';
		$attachment_id = $this->create_upload_object( $file );
		$this->assertTrue( Enrich::is_attachment_enriched( $attachment_id ) );
		$ret = Enrich::generate_alt_text_if_missing_or_previously_enriched( $attachment_id );
		$this->assertTrue( $ret );
		$this->assertTrue( Enrich::is_attachment_enriched( $attachment_id ) );
	}

	/**
	 * Don't generate alt text when it's been customized manually.
	 */
	public function test_generate_alt_text_no_refresh_when_customized() {
		$file          = dirname( __FILE__ ) . '/data/canola.jpg';
		$attachment_id = $this->create_upload_object( $file );
		$this->assertTrue( Enrich::is_attachment_enriched( $attachment_id ) );
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'My custom alt text' );
		$this->assertFalse( Enrich::is_attachment_enriched( $attachment_id ) );
		$ret = Enrich::generate_alt_text_if_missing_or_previously_enriched( $attachment_id );
		$this->assertFalse( $ret );
		$this->assertFalse( Enrich::is_attachment_enriched( $attachment_id ) );
	}

	/**
	 * If alt text is manually updated then the enrichment flag should be removed.
	 */
	public function test_remove_enrichment_flag_when_alt_text_is_updated() {
		$file          = dirname( __FILE__ ) . '/data/canola.jpg';
		$attachment_id = $this->create_upload_object( $file );
		$this->assertTrue( Enrich::is_attachment_enriched( $attachment_id ) );
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'My custom alt text' );
		$this->assertFalse( Enrich::is_attachment_enriched( $attachment_id ) );
	}

	/**
	 * If the image has a landmark, use that as the alt text over labels.
	 */
	public function test_use_landmark_for_alt_text_when_exists() {
		$file          = dirname( __FILE__ ) . '/data/eiffeltower.jpg';
		$attachment_id = $this->create_upload_object( $file );
		$this->assertTrue( Enrich::is_attachment_enriched( $attachment_id ) );
		$this->assertEquals( 'Eiffel Tower', Enrich::get_attachment_alt_text( $attachment_id ) );
	}

	/**
	 * If the image has a logo, use that as the alt text over labels.
	 */
	public function test_use_logo_for_alt_text_when_exists() {
		$file          = dirname( __FILE__ ) . '/data/cocacola.jpg';
		$attachment_id = $this->create_upload_object( $file );
		$this->assertTrue( Enrich::is_attachment_enriched( $attachment_id ) );
		$this->assertEquals( 'Coca-Cola', Enrich::get_attachment_alt_text( $attachment_id ) );
	}

	/**
	 * Non-racy images should be uploaded just fine.
	 */
	public function test_safe_search_upload_safe_image() {
		$files     = $this->create_files_array( dirname( __FILE__ ) . '/data/canola.jpg' );
		$overrides = array(
			'test_form' => false,
			'action'    => 'wp_handle_mock_upload',
		);
		$file      = wp_handle_upload( $files['file'], $overrides );
		$this->assertFalse( isset( $file['error'] ) );
		$this->assertEquals( 'image/jpeg', $file['type'] );
	}

	/**
	 * Racy images should be blocked from upload.
	 */
	public function test_safe_search_block_racy_image() {
		$files     = $this->create_files_array( dirname( __FILE__ ) . '/data/racy-image.jpg' );
		$overrides = array(
			'test_form' => false,
			'action'    => 'wp_handle_mock_upload',
		);
		$file      = wp_handle_upload( $files['file'], $overrides );
		$this->assertTrue( isset( $file['error'] ) );
		$this->assertContains( 'Image has likely or very likely Google Safe Search violations:', $file['error'] );
	}

	/**
	 * Pre-fetching should get all necessary data to be processed.
	 */
	public function test_prefetch_file_enrichment_data() {
		$file      = dirname( __FILE__ ) . '/data/prefetch-image.jpg';
		$cache_key = GCV::get_file_path_cache_key( $file );
		// Doesn't exist in cache yet.
		$this->assertFalse( wp_cache_get( $cache_key, GCV::PREFETCH_CACHE_GROUP ) );
		// No cache for the default features, so errored.
		$enrichment_data = GCV::get_file_enrichment_data( $file );
		$this->assertWPError( $enrichment_data );
		// // Successfully prefetches based on cache for all features.
		$this->assertTrue( GCV::prefetch_file_enrichment_data( $file ) );
		// Now it's in cache.
		$cache_value = wp_cache_get( $cache_key, GCV::PREFETCH_CACHE_GROUP );
		$this->assertTrue( isset( $cache_value['enrichment_data'] ) );
		$enrichment_data = GCV::get_file_enrichment_data( $file );
		$this->assertTrue( isset( $enrichment_data['responses'] ) );
	}

}
