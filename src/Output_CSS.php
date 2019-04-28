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

use Kirki\Core\Values;

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
	 * The original value.
	 *
	 * @access protected
	 * @since 1.0
	 * @var mixed
	 */
	protected $initial_val;

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
	 * Field arguments.
	 *
	 * @access protected
	 * @since 1.0
	 * @var array
	 */
	protected $field;

	/**
	 * Should this output perhaps be skipped?
	 *
	 * Handles the "exclude" argument.
	 *
	 * @access protected
	 * @since 1.0
	 * @var bool
	 */
	protected $excluded = false;

	/**
	 * The class constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $output The output arguments.
	 * @param mixed $value  The field value.
	 */
	public function __construct( $output, $value, $field = [] ) {
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
				'value_pattern'     => '$',
				'pattern_replace'   => [],
				'exclude'           => [],
			]
		);

		$this->initial_val = $value;
		$this->field       = $field;
		$this->value       = $this->get_value();

		// Should this output be excluded?
		$this->maybe_exclude();
	}
	
	/**
	 * Populates the $css_array from the object's $output property.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function populate_css_array() {

		// Early exit if we don't have an element defined.
		if ( null === $this->output['element'] ) {
			return;
		}

		// Add the media-query.
		$this->css_array = [
			$this->output['media_query'] = [
				$this->output['element'] = [],
			],
		];

		// Add the element.
		$this->css_array
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

		if ( null === $this->value ) {
			$this->value = $this->initial_val;

			// Apply sanitize_callback to the value.
			$this->maybe_apply_sanitize_callback();

			// Apply value_pattern & pattern_replace to the value.
			$this->apply_value_pattern();
			$this->apply_pattern_replace();
		}

		if ( ! is_array( $this->value ) ) {
			return $this->output['prefix'] . $this->value . $this->output['units'] . $this->output['suffix'];
		}
		return $this->value;
	}

	/**
	 * Returns the result of the "exclude" argument checks.
	 *
	 * @access public
	 * @since 1.0
	 * @return bool
	 */
	public function get_exclude() {
		return (bool) $this->exclude;
	}

	/**
	 * If we have a sanitize_callback defined, apply it to the value.
	 *
	 * @access public
	 * @since 1.0
	 * @return mixed
	 */
	public function maybe_apply_sanitize_callback() {
		if ( is_callable( $this->output['sanitize_callback'] ) ) {
			$this->value = call_user_func( $this->output['sanitize_callback'], $this->value );
		}
	}

	/**
	 * Apply value_pattern the $this->value.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	protected function apply_value_pattern() {
		if ( ! is_array( $this->value ) ) {
			$this->value = str_replace( '$', $this->value, $output['value_pattern'] );
		}
		if ( is_array( $this->value ) ) {
			foreach ( array_keys( $this->value ) as $value_k ) {
				if ( is_array( $this->value[ $value_k ] ) ) {
					continue;
				}
				if ( isset( $this->output['choice'] ) ) {
					if ( $this->output['choice'] === $value_k ) {
						$this->value[ $output['choice'] ] = str_replace( '$', $this->value[ $this->output['choice'] ], $this->output['value_pattern'] );
					}
					continue;
				}
				$this->value[ $value_k ] = str_replace( '$', $this->value[ $value_k ], $this->output['value_pattern'] );
			}
		}
	}

	/**
	 * Apply value-pattern replacements to $this->value.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	protected function apply_pattern_replace() {
		foreach ( $this->output['pattern_replace'] as $search => $replace ) {
			$replacement = Values::get_value( $this->field['kirki_config'], $replace );
			$replacement = $replacement ?: '';
			if ( is_array( $this->value ) ) {
				foreach ( $this->value as $k => $v ) {
					$_val              = ( isset( $this->value[ $v ] ) ) ? $this->value[ $v ] : $v;
					$this->value[ $k ] = str_replace( $search, $replacement, $_val );
				}
				return;
			}
			$this->value = str_replace( $search, $replacement, $this->value );
		}
	}

	/**
	 * Evaluate the "exclude" argument.
	 *
	 * @access protected
	 * @since 1.0
	 * @return void
	 */
	function maybe_exclude() {

		// Skip if value is empty.
		if ( '' === $this->initial_val ) {
			$this->exclude = true;
			return;
		}

		// No need to proceed this if the current value is the same as in the "exclude" value.
		if ( is_array( $this->output['exclude'] ) ) {
			foreach ( $this->output['exclude'] as $exclude ) {
				if ( is_array( $this->initial_val ) ) {
					if ( is_array( $exclude ) ) {
						$diff1 = array_diff( $this->initial_val, $exclude );
						$diff2 = array_diff( $exclude, $this->initial_val );

						if ( empty( $diff1 ) && empty( $diff2 ) ) {
							$this->exclude = true;
							return;
						}
					}

					// If 'choice' is defined check for sub-values too.
					// Fixes https://github.com/aristath/kirki/issues/1416.
					if ( isset( $this->output['choice'] ) && isset( $this->initial_val[ $this->output['choice'] ] ) && $exclude == $this->initial_val[ $this->output['choice'] ] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
						$this->exclude = true;
						return;
					}
				}

				// Skip if value is defined as excluded.
				if ( $exclude === $this->initial_val || ( '' === $exclude && empty( $this->initial_val ) ) ) {
					$this->exclude = true;
					return;
				}
			}
		}
	}
}
