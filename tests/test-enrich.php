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
		Enrich::generate_alt_text_always( $attachment_id );
		$this->assertEquals( 'yellow, rapeseed, field, canola, grassland, mustard plant, plain, prairie, mustard and cabbage family, sky', get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
	}

	/**
	 * If alt text is manually updated then the enrichment flag should be removed.
	 */
	public function test_remove_enrichment_flag_when_alt_text_is_updated() {
		$file          = dirname( __FILE__ ) . '/data/canola.jpg';
		$attachment_id = $this->create_upload_object( $file );
		Enrich::generate_alt_text_always( $attachment_id );
		$this->assertTrue( (bool) get_post_meta( $attachment_id, Enrich::ENRICHED_META_KEY, true ) );
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'My custom alt text' );
		$this->assertFalse( (bool) get_post_meta( $attachment_id, Enrich::ENRICHED_META_KEY, true ) );
	}

}
