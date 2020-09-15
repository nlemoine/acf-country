<?php
/*
Plugin Name: Advanced Custom Fields: ACF Country
Plugin URI: https://github.com/nlemoine/acf-country
Description: Display a select field of all countries, in any language.
Version: 2.0.2
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

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->settings = array(
			'version' => '2.0.2',
			'url'     => plugin_dir_url( __FILE__ ),
			'path'    => plugin_dir_path( __FILE__ ),
		);

		load_plugin_textdomain( 'acf-country', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' );
		add_action( 'acf/include_field_types', array( $this, 'fields' ) );
		add_action( 'acf/register_fields', array( $this, 'fields' ) );

		add_filter( 'wpgraphql_acf_supported_fields', function ( $supported_fields ) {
			array_push( $supported_fields, 'country' );

			return $supported_fields;
		} );

		add_filter( 'wpgraphql_acf_register_graphql_field', function ( $field_config, $type_name, $field_name, $config ) {

			// How to add new WPGraphQL fields is super undocumented, I used this code as a base
			// https://github.com/wp-graphql/wp-graphql/issues/214#issuecomment-653141685

			$acf_field = isset( $config['acf_field'] ) ? $config['acf_field'] : null;
			$acf_type  = isset( $acf_field['type'] ) ? $acf_field['type'] : null;

			$resolve = $field_config['resolve'];

			if ( $acf_type === "country" ) {

				switch ( $acf_field['return_format'] ) {
					case 'array':
						$field_config = [
							'type'    => [ 'list_of' => 'String' ],
							'resolve' => function ( $root, $args, $context, $info ) use ( $resolve, $acf_field ) {
								$value = $resolve( $root, $args, $context, $info );

								if ( ! empty( $value ) ) {
									return [
										'value' => $value,
										'label' => $acf_field['choices'][ $value ]
									];
								}

								return [];
							}
						];
						break;
					case 'value':
						$field_config = [
							'type'    => 'String',
							'resolve' => function ( $root, $args, $context, $info ) use ( $resolve ) {
								$value = $resolve( $root, $args, $context, $info );

								return ! empty( $value ) ? $value : null;
							}
						];
						break;
					case 'label':
						$field_config = [
							'type'    => 'String',
							'resolve' => function ( $root, $args, $context, $info ) use ( $resolve, $acf_field ) {
								$value = $resolve( $root, $args, $context, $info );

								if ( ! empty( $value ) ) {
									return $acf_field['choices'][ $value ];
								}

								return null;
							}
						];
						break;
				}

			}

			return $field_config;
		}, 10, 4 );
	}

	/**
	 * Include our ACF Field Types
	 *
	 * @param integer $version
	 *
	 * @return void
	 */
	public function fields( $version = 5 ) {
		include_once 'fields/acf-country.php';
	}
}

new FieldLoader();
