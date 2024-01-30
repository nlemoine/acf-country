<?php
/*
Plugin Name: Advanced Custom Fields: ACF Country
Plugin URI: https://github.com/nlemoine/acf-country
Description: Display a select field of all countries, in any language.
Version: 2.1.1
Author: Nicolas Lemoine
Author URI: https://helloni.co/
GitHub Plugin URI: https://github.com/nlemoine/acf-country

License: MIT License
License URI: http://opensource.org/licenses/MIT
*/

namespace HelloNico\ACF;

/**
 * Return if Field Loader already exists.
 */
if ( class_exists( 'FieldLoader' ) ) {
	return;
}

/**
 * Field Loader
 */
class FieldLoader {
	public $settings = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->settings = array(
			'version' => '2.1.1',
			'url'     => plugin_dir_url( __FILE__ ),
			'path'    => plugin_dir_path( __FILE__ ),
		);

		load_plugin_textdomain( 'acf-country', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' );
		add_action( 'acf/include_field_types', array( $this, 'fields' ) );
		add_action( 'acf/register_fields', array( $this, 'fields' ) );

		add_filter( 'wpgraphql_acf_register_graphql_field', array( $this, 'register_graphql_field' ), 10, 4 );
	}

	/**
	 * Register WPGraphQL field
	 *
	 * @see https://github.com/wp-graphql/wp-graphql/issues/214#issuecomment-653141685
	 *
	 * @param array $field_config
	 * @param string $type_name
	 * @param string $field_name
	 * @param array $config
	 * @return mixed
	 */
	public function register_graphql_field( $field_config, $type_name, $field_name, $config ) {

		$acf_field = isset( $config['acf_field'] ) ? $config['acf_field'] : null;
		$acf_type  = isset( $acf_field['type'] ) ? $acf_field['type'] : null;

		if ( $acf_type !== 'country' ) {
			return $field_config;
		}

		$resolve = $field_config['resolve'];

		switch ( $acf_field['return_format'] ) {
			case 'array':
				$field_config = array(
					'type'    => empty( $acf_field['multiple'] ) ? array( 'list_of' => 'String' ) : array( 'list_of' => array( 'list_of' => 'String' ) ),
					'resolve' => function ( $root, $args, $context, $info ) use ( $resolve, $acf_field ) {
						$value = $resolve( $root, $args, $context, $info );

						if ( ! empty( $value ) ) {
							if ( is_array( $value ) ) {
								$values = array();

								foreach ( $value as $single_value ) {
									array_push( $values, array(
										'value' => $single_value,
										'label' => $acf_field['choices'][ $single_value ],
									) );
								}

								return $values;

							} else {
								return array(
									'value' => $value,
									'label' => $acf_field['choices'][ $value ],
								);
							}

						}

						return array();
					},
				);
				break;
			case 'value':
				$field_config = array(
					'type'    => empty( $acf_field['multiple'] ) ? 'String' : array( 'list_of' => 'String' ),
					'resolve' => function ( $root, $args, $context, $info ) use ( $resolve ) {
						$value = $resolve( $root, $args, $context, $info );

						return ! empty( $value ) ? $value : null;
					},
				);
				break;
			case 'label':
				$field_config = array(
					'type'    => empty( $acf_field['multiple'] ) ? 'String' : array( 'list_of' => 'String' ),
					'resolve' => function ( $root, $args, $context, $info ) use ( $resolve, $acf_field ) {
						$value = $resolve( $root, $args, $context, $info );

						if ( ! empty( $value ) ) {
							if ( is_array( $value ) ) {
								$values = array();

								foreach ( $value as $single_value ) {
									array_push( $values, $acf_field['choices'][ $single_value ] );
								}

								return $values;

							} else {
								return $acf_field['choices'][ $value ];
							}

						}

						return null;
					},
				);
				break;
		}

		return $field_config;
	}

	/**
	 * Add ACF Country to WPGraphQL supported fields
	 *
	 * @param array $supported_fields
	 * @return array
	 */
	public function add_graphql_field_support( $supported_fields ) {
		array_push( $supported_fields, 'country' );
		return $supported_fields;
	}

	/**
	 * Include our ACF Field Types
	 *
	 * @param integer $version
	 *
	 * @return void
	 */
	public function fields( $version = 5 ) {
		require_once 'fields/acf-country.php';
		$field = new AcfCountry( $this->settings );
		acf_register_field_type( $field );
	}
}

new FieldLoader();
