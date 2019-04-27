<?php
/**
 * Handles CSS output for fields.
 *
 * @package     Kirki
 * @subpackage  Controls
 * @copyright   Copyright (c) 2019, Ari Stathopoulos (@aristath)
 * @license    https://opensource.org/licenses/MIT
 * @since       2.2.0
 */

namespace Kirki\Modules\CSS;

use Kirki\Core\Values;

/**
 * Handles field CSS output.
 */
class Output {

	/**
	 * The Kirki configuration used in the field.
	 *
	 * @access protected
	 * @var string
	 */
	protected $config_id = 'global';

	/**
	 * The field's `output` argument.
	 *
	 * @access protected
	 * @var array
	 */
	protected $output = [];

	/**
	 * An array of the generated styles.
	 *
	 * @access protected
	 * @var array
	 */
	protected $styles = [];

	/**
	 * The field.
	 *
	 * @access protected
	 * @var array
	 */
	protected $field = [];

	/**
	 * The value.
	 *
	 * @access protected
	 * @var string|array
	 */
	protected $value;

	/**
	 * The class constructor.
	 *
	 * @access public
	 * @param string       $config_id The config ID.
	 * @param array        $output    The output argument.
	 * @param string|array $value     The value.
	 * @param array        $field     The field.
	 */
	public function __construct( $config_id, $output, $value, $field ) {
		$this->config_id = $config_id;
		$this->value     = $value;
		$this->output    = $output;
		$this->field     = $field;

		$this->parse_output();
	}

	/**
	 * Parses the output arguments.
	 * Calls the process_output method for each of them.
	 *
	 * @access protected
	 */
	protected function parse_output() {
		foreach ( $this->output as $output ) {
			$output_obj = new Output_CSS( $output, $this->value, $this->field );

			if ( $output_obj->get_exclude() ) {
				return;
			}

			$value      = $output_obj->get_value();

			if ( isset( $output['element'] ) && is_array( $output['element'] ) ) {
				$output['element'] = array_unique( $output['element'] );
				sort( $output['element'] );
				$output['element'] = implode( ',', $output['element'] );
			}

			$value = $this->process_value( $value, $output );

			if ( ( is_admin() && ! is_customize_preview() ) || ( isset( $_GET['editor'] ) && '1' === $_GET['editor'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

				// Check if this is an admin style.
				if ( ! isset( $output['context'] ) || ! in_array( 'editor', $output['context'], true ) ) {
					continue;
				}
			} elseif ( isset( $output['context'] ) && ! in_array( 'front', $output['context'], true ) ) {

				// Check if this is a frontend style.
				continue;
			}
			$this->process_output( $output, $value );
		}
	}

	/**
	 * Parses an output and creates the styles array for it.
	 *
	 * @access protected
	 * @param array        $output The field output.
	 * @param string|array $value  The value.
	 *
	 * @return null
	 */
	protected function process_output( $output, $value ) {
		if ( ! isset( $output['element'] ) || ! isset( $output['property'] ) ) {
			return;
		}
		$output['media_query'] = ( isset( $output['media_query'] ) ) ? $output['media_query'] : 'global';
		$output['prefix']      = ( isset( $output['prefix'] ) ) ? $output['prefix'] : '';
		$output['units']       = ( isset( $output['units'] ) ) ? $output['units'] : '';
		$output['suffix']      = ( isset( $output['suffix'] ) ) ? $output['suffix'] : '';

		// Properties that can accept multiple values.
		// Useful for example for gradients where all browsers use the "background-image" property
		// and the browser prefixes go in the value_pattern arg.
		$accepts_multiple = [
			'background-image',
			'background',
		];
		if ( in_array( $output['property'], $accepts_multiple, true ) ) {
			if ( isset( $this->styles[ $output['media_query'] ][ $output['element'] ][ $output['property'] ] ) && ! is_array( $this->styles[ $output['media_query'] ][ $output['element'] ][ $output['property'] ] ) ) {
				$this->styles[ $output['media_query'] ][ $output['element'] ][ $output['property'] ] = (array) $this->styles[ $output['media_query'] ][ $output['element'] ][ $output['property'] ];
			}
			$this->styles[ $output['media_query'] ][ $output['element'] ][ $output['property'] ][] = $output['prefix'] . $value . $output['units'] . $output['suffix'];
			return;
		}
		if ( is_string( $value ) || is_numeric( $value ) ) {
			$this->styles[ $output['media_query'] ][ $output['element'] ][ $output['property'] ] = $output['prefix'] . $this->process_property_value( $output['property'], $value ) . $output['units'] . $output['suffix'];
		}
	}

	/**
	 * Some CSS properties are unique.
	 * We need to tweak the value to make everything works as expected.
	 *
	 * @access protected
	 * @param string       $property The CSS property.
	 * @param string|array $value    The value.
	 *
	 * @return array
	 */
	protected function process_property_value( $property, $value ) {
		$properties = apply_filters(
			'kirki_output_property_classnames',
			[
				'font-family'         => '\Kirki\Modules\CSS\Property\Font_Family',
				'background-image'    => '\Kirki\Modules\CSS\Property\Background_Image',
				'background-position' => '\Kirki\Modules\CSS\Property\Background_Position',
			]
		);
		if ( array_key_exists( $property, $properties ) ) {
			$classname = $properties[ $property ];
			$obj       = new $classname( $property, $value );
			return $obj->get_value();
		}
		return $value;
	}

	/**
	 * Returns the value.
	 *
	 * @access protected
	 * @param string|array $value The value.
	 * @param array        $output The field "output".
	 * @return string|array
	 */
	protected function process_value( $value, $output ) {
		if ( isset( $output['property'] ) ) {
			return $this->process_property_value( $output['property'], $value );
		}
		return $value;
	}

	/**
	 * Exploses the private $styles property to the world
	 *
	 * @access protected
	 * @return array
	 */
	public function get_styles() {
		return $this->styles;
	}
}
