<?php
/**
 * Generates the styles for a given output.
 * Handles a single 'output' argument.
 *
 * @package   kirki-framework/module-css
 * @author    Ari Stathopoulos (@aristath)
 * @copyright Copyright (c) 2019, Ari Stathopoulos (@aristath)
 * @license   https://opensource.org/licenses/MIT
 * @since     1.0
 */

namespace Kirki\Modules\CSS;

class Output_CSS {

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
	protected $css_array;

    /**
	 * The class constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $output The output arguments.
	 * @param mixed $value  The field value.
	 */
	public function __construct( $output, $value ) {
		$this->output = wp_parse_args(
            $output,
            [
                'media_query'       => 'global',
                'element'           => null,
                'property'          => null,
                'prefix'            => '',
                'units'             => '',
                'suffix'            => '',
                'sanitize_callback' => null,
            ]
        );
		$this->value  = $value;// $this->maybe_apply_sanitize_callback( $value );
	}
	
	/**
	 * Populates the $css_array from the object's $output property.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function populate_css_array() {

        $this->css_array = [];

        // Early exit if we don't have an element defined.
        if ( null === $output['element'] ) {
            return;
        }

        // Add the media-query.
        $this->css_array[ $output['media_query'] ] = [];
    }
    
    /**
     * Returns the $css_array.
     *
     * @access public
     * @since 1.0
     * @return array
     */
    public function get_css_array() {
        if ( ! $this->css_array ) {
            $this->populate_css_array();
        }
        return $this->css_array;
    }

    /**
     * Gets the value.
     *
     * @access public
     * @since 1.0
     * @return mixed
     */
    public function get_value() {
        return $this->value;
    }

    /**
	 * If we have a sanitize_callback defined, apply it to the value.
	 *
     * @access public
     * @since 1.0
	 * @return mixed
	 */
    public function maybe_apply_sanitize_callback() {
		if ( $this->output['sanitize_callback'] && is_callable( $this->output['sanitize_callback'] ) ) {
            return call_user_func( $this->output['sanitize_callback'], $this->value );
        }
		return $this->value;
	}
}
