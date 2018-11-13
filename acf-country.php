<?php
/*
Plugin Name: Advanced Custom Fields: ACF Country
Plugin URI: https://github.com/nlemoine/acf-country
Description: Display a select field of all countries, in any language.
Version: 1.4
Author: Nicolas Lemoine
Author URI: https://github.com/nlemoine
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// check if class already exists
if ( ! class_exists( 'hellonico_acf_plugin_acf_country' ) ) :

	class hellonico_acf_plugin_acf_country {

		/*
		*  __construct
		*
		*  This function will setup the class functionality
		*
		*  @type    function
		*  @date    17/02/2016
		*  @since   1.0.0
		*
		*  @param   n/a
		*  @return  n/a
		*/
		public function __construct() {

			// vars
			$this->settings = array(
				'version' => '1.4',
				'url'     => plugin_dir_url( __FILE__ ),
				'path'    => plugin_dir_path( __FILE__ ),
			);

			// include field
			add_action( 'acf/include_field_types', array( $this, 'include_field' ) ); // v5
			add_action( 'acf/register_fields', array( $this, 'include_field' ) ); // v4

		}


		/*
		*  include_field_types
		*
		*  This function will include the field type class
		*
		*  @type    function
		*  @date    17/02/2016
		*  @since   1.0.0
		*
		*  @param   $version (int) major ACF version. Defaults to false
		*  @return  n/a
		*/
		public function include_field( $version = false ) {

			// support empty $version
			if ( ! $version ) {
				$version = 4;
			}

			// include
			include_once 'fields/acf-country-helpers.php';
			include_once 'fields/acf-country-v' . $version . '.php';

		}

	}


	// initialize
	new hellonico_acf_plugin_acf_country();


	// class_exists check
endif;
