<?php
/**
 * Class UtilsTest
 *
 * @package Pantheon_Image_Enrichment
 */

use Pantheon_Image_Enrichment\Utils;

/**
 * Tests the Utils class.
 */
class UtilsTest extends Pantheon_Image_Enrichment_Testcase {

	/**
	 * Ensure bounding vertices are correctly transformed to quadrants.
	 */
	public function test_transform_bounding_vertices_into_crop_hints_horizontal_image() {
		$this->assertEquals(
			array(
				'left',
				'center',
			), Utils::transform_bounding_vertices_into_crop_hints(
				array(
					array(),
					array(
						'x' => 1200,
					),
					array(
						'x' => 1200,
						'y' => 1200,
					),
					array(
						'y' => 1200,
					),
				), 1600, 1200
			)
		);
		$this->assertEquals(
			array(
				'center',
				'center',
			), Utils::transform_bounding_vertices_into_crop_hints(
				array(
					array(
						'x' => 205,
					),
					array(
						'x' => 1405,
					),
					array(
						'x' => 1405,
						'y' => 1200,
					),
					array(
						'y' => 1200,
					),
				), 1600, 1200
			)
		);
		$this->assertEquals(
			array(
				'right',
				'center',
			), Utils::transform_bounding_vertices_into_crop_hints(
				array(
					array(
						'x' => 400,
					),
					array(
						'x' => 1600,
					),
					array(
						'x' => 1600,
						'y' => 1200,
					),
					array(
						'y' => 1200,
					),
				), 1600, 1200
			)
		);
		$this->assertEquals(
			array(
				'center',
				'center',
			), Utils::transform_bounding_vertices_into_crop_hints(
				array(
					array(
						'x' => 250,
					),
					array(
						'x' => 1450,
					),
					array(
						'x' => 1450,
						'y' => 1200,
					),
					array(
						'y' => 1200,
					),
				), 1600, 1200
			)
		);
		$this->assertEquals(
			array(
				'center',
				'top',
			), Utils::transform_bounding_vertices_into_crop_hints(
				array(
					array(),
					array(
						'x' => 1200,
					),
					array(
						'x' => 1200,
						'y' => 1200,
					),
					array(
						'y' => 1200,
					),
				), 1200, 1600
			)
		);
	}

}
