<?php
/**
 * Class EnrichTest
 *
 * @package Pantheon_Image_Enrichment
 */

use Pantheon_Image_Enrichment\Enrich;

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
		$this->assertEquals( 'yellow, rapeseed, field, canola, grassland, mustard plant, plain, prairie, mustard and cabbage family, sky', get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
	}

	/**
	 * Support for refreshing the alt text when it's already dynamically set.
	 */
	public function test_generate_alt_text_refresh() {
		$file          = dirname( __FILE__ ) . '/data/canola.jpg';
		$attachment_id = $this->create_upload_object( $file );
		$ret           = Enrich::generate_alt_text_if_missing_or_previously_enriched( $attachment_id );
		$this->assertTrue( $ret );
	}

	/**
	 * Don't generate alt text when it's been customized manually.
	 */
	public function test_generate_alt_text_no_refresh_when_customized() {
		$file          = dirname( __FILE__ ) . '/data/canola.jpg';
		$attachment_id = $this->create_upload_object( $file );
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'My custom alt text' );
		$ret = Enrich::generate_alt_text_if_missing_or_previously_enriched( $attachment_id );
		$this->assertFalse( $ret );
	}

	/**
	 * If alt text is manually updated then the enrichment flag should be removed.
	 */
	public function test_remove_enrichment_flag_when_alt_text_is_updated() {
		$file          = dirname( __FILE__ ) . '/data/canola.jpg';
		$attachment_id = $this->create_upload_object( $file );
		$this->assertTrue( (bool) get_post_meta( $attachment_id, Enrich::ENRICHED_META_KEY, true ) );
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'My custom alt text' );
		$this->assertFalse( (bool) get_post_meta( $attachment_id, Enrich::ENRICHED_META_KEY, true ) );
	}

}
