<?php
/**
 * Handles CSS output for image fields.
 *
 * @package    Kirki
 * @subpackage Controls
 * @copyright  Copyright (c) 2019, Ari Stathopoulos (@aristath)
 * @license    https://opensource.org/licenses/MIT
 * @since      3.0.10
 */

namespace Kirki\Modules\CSS\Field;

use Kirki\Modules\CSS\Output;

/**
 * Output overrides.
 */
class Image extends Output {

	/**
	 * Processes a single item from the `output` array.
	 *
	 * @access protected
	 * @param array $output The `output` item.
	 * @param array $value  The field's value.
	 */
	protected function process_output( $output, $value ) {
		if ( ! isset( $output['element'] ) || ! isset( $output['property'] ) ) {
			return;
		}
		$output = wp_parse_args(
			$output,
			[
				'media_query' => 'global',
			]
		);
		if ( is_array( $value ) ) {
			if ( isset( $output['choice'] ) && $output['choice'] ) {
				$this->styles[ $output['media_query'] ][ $output['element'] ][ $output['property'] ] = $this->process_property_value( $output['property'], $value[ $output['choice'] ] );
				return;
			}
			if ( isset( $value['url'] ) ) {
				$this->styles[ $output['media_query'] ][ $output['element'] ][ $output['property'] ] = $this->process_property_value( $output['property'], $value['url'] );
				return;
			}
			return;
		}
		$this->styles[ $output['media_query'] ][ $output['element'] ][ $output['property'] ] = $this->process_property_value( $output['property'], $value );
	}
}
