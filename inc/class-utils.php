<?php
/**
 * Various utility methods.
 *
 * @package Pantheon_Image_Enrichment
 */

namespace Pantheon_Image_Enrichment;

/**
 *  Various utility methods.
 */
class Utils {

	/**
	 * Transform bounding vertices from GCV into quadrant crop hints.
	 *
	 * @param array   $bounding_vertices Bounding vertices from GCV.
	 * @param integer $width             Image width.
	 * @param integer $height            Image height.
	 * @return array
	 */
	public static function transform_bounding_vertices_into_crop_hints( $bounding_vertices, $width, $height ) {
		$horz_quad = 'center';
		$vert_quad = 'center';

		$x1 = isset( $bounding_vertices[0]['x'] ) ? (int) $bounding_vertices[0]['x'] : 0;
		$x2 = $bounding_vertices[1]['x'];

		$x2_diff = $width - $x2;

		// 2 allows some fuzz because GCV is zero-based pixel positions.
		if ( 0 === $x1 && $x2_diff >= 2 ) {
			$horz_quad = 'left';
		} elseif ( $x2_diff <= 1 && 0 !== $x1 ) {
			$horz_quad = 'right';
		} elseif ( $x1 < $x2_diff && 0 !== $x1 && ( $x2_diff / $x1 > 2 ) ) {
			$horz_quad = 'left';
		} elseif ( $x2_diff < $x1 && 0 !== $x2_diff && ( $x1 / $x2_diff > 2 ) ) {
			$horz_quad = 'right';
		}

		$y1 = isset( $bounding_vertices[1]['y'] ) ? $bounding_vertices[1]['y'] : 0;
		$y2 = $bounding_vertices[2]['y'];

		$y2_diff = $height - $y2;

		if ( 0 === $y1 && $y2_diff >= 2 ) {
			$vert_quad = 'top';
		} elseif ( $y2_diff <= 1 && 0 !== $y1 ) {
			$vert_quad = 'bottom';
		} elseif ( $y1 < $y2_diff && 0 !== $y1 && ( $y2_diff / $y1 > 2 ) ) {
			$vert_quad = 'top';
		} elseif ( $y2_diff < $y1 && 0 !== $x2_diff && ( $y1 / $y2_diff > 2 ) ) {
			$vert_quad = 'bottom';
		}

		return array(
			$horz_quad,
			$vert_quad,
		);
	}

}
