<?php

namespace HelloNico\ACF;

if ( class_exists( 'AcfCountry' ) ) {
	return;
}

use acf_field;

/**
 * ACF Country field
 */
class AcfCountry extends acf_field {

	/**
	 * Constructor
	 */
	public function __construct( $settings ) {
		$this->name     = 'country';
		$this->label    = __( 'Country', 'acf-country' );
		$this->category = 'choice';
		$this->defaults = [
			'multiple'      => 0,
			'allow_null'    => 0,
			'choices'       => [],
			'default_value' => '',
			'ui'            => 1,
			'placeholder'   => __( 'Select a country', 'acf-country' ),
			'return_format' => 'value',
		];
		$this->settings = $settings;

		parent::__construct();
	}

	/**
	 * Create extra settings for your field. These are visible when editing a field.
	 *
	 * @param  array $field
	 * @return void
	 */
	public function render_field_settings( $field ) {

		// encode choices (convert from array)
		$field['choices']       = acf_encode_choices( $this->get_countries() );
		$field['default_value'] = acf_encode_choices( $field['default_value'], false );

		// choices
		acf_render_field_setting(
			$field,
			[
				'label'   => __( 'Choices', 'acf' ),
				'name'    => 'choices',
				'type'    => 'textarea',
				'wrapper' => [
					'class' => 'hidden',
				],
			]
		);

		// Placeholder
		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Placeholder Text', 'acf-country' ),
				'instructions' => __( 'Appears within the input', 'acf-country' ),
				'type'         => 'text',
				'name'         => 'placeholder',
			]
		);

		// default_value
		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Default Value', 'acf' ),
				'instructions' => __( 'Enter each default value on a new line', 'acf' ),
				'name'         => 'default_value',
				'type'         => 'textarea',
			]
		);

		// allow_null
		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Allow Null?', 'acf' ),
				'instructions' => '',
				'name'         => 'allow_null',
				'type'         => 'true_false',
				'ui'           => 1,
			]
		);

		// multiple
		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Select multiple values?', 'acf' ),
				'instructions' => '',
				'name'         => 'multiple',
				'type'         => 'true_false',
				'ui'           => 1,
			]
		);

		// ui
		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Stylised UI', 'acf' ),
				'instructions' => '',
				'name'         => 'ui',
				'type'         => 'true_false',
				'ui'           => 1,
			]
		);

		// return_format
		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Return Format', 'acf' ),
				'instructions' => __( 'Specify the value returned', 'acf' ),
				'type'         => 'select',
				'name'         => 'return_format',
				'choices'      => [
					'array' => __( 'Country code and name', 'acf-country' ),
					'value' => __( 'Country code', 'acf-country' ),
					'label' => __( 'Country name', 'acf-country' ),
				],
			]
		);

	}

	/**
	 * Create the HTML interface for your field
	 *
	 * @param  array $field
	 * @return void
	 */
	public function render_field( $field ) {
		$field['choices'] = $this->get_countries();
		$field['ajax']    = 0;
		if ( $field['value'] && is_array( $field['value'] ) ) {
			$field['value'] = array_map( 'strtoupper', $field['value'] );
		}
		acf_get_field_type( 'select' )->render_field( $field );
	}

	/**
	 * This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	 * Use this action to add CSS + JavaScript to assist your render_field() action.
	 *
	 * @return void
	 */
	public function input_admin_enqueue_scripts() {
		acf_get_field_type( 'select' )->input_admin_enqueue_scripts();

		wp_register_script( 'acf-country', "{$this->settings['url']}assets/dist/js/acf-country.js", [ 'acf-input' ], $this->settings['version'] );
		wp_enqueue_script( 'acf-country' );

		wp_register_style( 'acf-country', "{$this->settings['url']}assets/dist/css/acf-country.css", [ 'acf-input' ], $this->settings['version'] );
		wp_enqueue_style( 'acf-country' );

	}

	/**
	 * This filter is applied to the fields value after it is loaded from the database.
	 *
	 * @param  mixed $value
	 * @param  mixed $post_id
	 * @param  array $field
	 * @return mixed
	 */
	public function load_value( $value, $post_id, $field ) {
		return acf_get_field_type( 'select' )->load_value( $value, $post_id, $field );
	}

	/**
	 * This filter is applied to the $value before it is saved in the database.
	 *
	 * @param  mixed $value
	 * @param  mixed $post_id
	 * @param  array $field
	 * @return mixed
	 */
	public function update_value( $value, $post_id, $field ) {
		return acf_get_field_type( 'select' )->update_value( $value, $post_id, $field );
	}

	/**
	 *  format_value()
	 *
	 *  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	 *
	 *  @param  mixed $value
	 *  @param  mixed $post_id
	 *  @param  array $field
	 *  @return mixed
	 */
	public function format_value( $value, $post_id, $field ) {
		$field['choices'] = $this->get_countries();
		return acf_get_field_type( 'select' )->format_value( $value, $post_id, $field );
	}

	/*
	*  validate_value()
	*
	*  This filter is used to perform validation on the value prior to saving.
	*  All values are validated regardless of the field's required setting. This allows you to validate and return
	*  messages to the user if the value is not correct
	*
	*  @param  boolean $valid
	*  @param  mixed   $value
	*  @param  array   $field
	*  @param  array   $input
	*  @return boolean
	*/
	public function validate_value( $valid, $value, $field, $input ) {
		if ( empty( $value ) ) {
			return $valid;
		}

		$countries = array_keys( $this->get_countries() );
		if ( is_array( $value ) ) {
			if ( count( array_diff( $value, $countries ) ) !== 0 ) {
				/* translators: placeholder indicates the invalid country codes */
				$valid = sprintf( _n( '%s is not valid a country code', '%s are not valid country codes', count( $value ), 'acf-country' ), implode( ', ', $value ) );
			}
		} elseif ( is_string( $value ) ) {
			if ( ! in_array( $value, $countries, true ) ) {
				/* translators: placeholder indicates the invalid country code */
				$valid = sprintf( __( '%s is not a valid country code', 'acf-country' ), $value );
			}
		}
		return $valid;
	}

	/**
	 * This filter is applied to the $field before it is saved to the database
	 *
	 * @param  array $field
	 * @return array
	 */
	public function update_field( $field ) {
		return acf_get_field_type( 'select' )->update_field( $field );
	}

	/**
	 * Get countries
	 *
	 * @return array
	 */
	private function get_countries() {

		$wp_locale = get_locale();

		// Try locales in that order
		$locales = [
			$wp_locale,
			substr( $wp_locale, 0, 2 ),
			'en',
		];

		foreach ( $locales as $locale ) {
			$file = sprintf( '%s/data/%s/country.php', $this->settings['path'], $locale );
			if ( is_file( $file ) ) {
				break;
			}
		}

		$countries = require $file;

		return apply_filters( 'acf/country/countries', $countries );
	}
}

new AcfCountry( $this->settings );
