<?php
/**
 * Generates the styles for the frontend.
 * Handles the 'output' argument of fields
 *
 * @package   kirki-framework/module-css
 * @author    Ari Stathopoulos (@aristath)
 * @copyright Copyright (c) 2019, Ari Stathopoulos (@aristath)
 * @license   https://opensource.org/licenses/MIT
 * @since     1.0
 */

namespace Kirki\Modules\CSS;

class Field_CSS {

	/**
	 * The output arguments.
	 *
	 * @access protected
	 * @since 1.0
	 * @var array
	 */
	protected $output;

	/**
	 * The value.
	 *
	 * @access protected
	 * @since 1.0
	 * @var mixed
	 */
	protected $value;

	/**
	 * CSS as an array $css_array[ $media_query ][ $element ][ $property ] = [ $value ].
	 *
	 * @access protected
	 * @since 1.0
	 * @var array
	 */
	protected $css_array = [];

	/**
     * Field arguments.
     *
     * @access protected
     * @since 1.0
     * @var array
     */
	protected $field;

	/**
	 * The class constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $output The output arguments.
	 * @param mixed $value  The field value.
	 * @param array $field  The field arguments.
	 */
	public function __construct( $output, $value, $field = [] ) {
		$this->output = $output;
		$this->value  = $value;
		$this->field  = $field;
	}

	/**
	 * Populates the $css_array from the object's $output property.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function populate_css_array() {
		foreach ( $this->output as $output ) {
			$output_obj = new Output_CSS( $output, $this->value, $this->field );
			$this->output = array_replace_recursive( $this->output, $output_obj->get_css_array() );
		}
	}
}
