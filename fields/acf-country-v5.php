<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('acf_field_country') ) :


class acf_field_country extends acf_field {

	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/

	function __construct( $settings ) {

		/*
		*  name (string) Single word, no spaces. Underscores allowed
		*/

		$this->name = 'country';


		/*
		*  label (string) Multiple words, can include spaces, visible when selecting a field type
		*/

		$this->label = __('Country', 'acf-country');


		/*
		*  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
		*/

		$this->category = 'choice';


		/*
		*  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
		*/

		$this->defaults = acf_country_helpers::get_defaults();


		/*
		*  settings (array) Store plugin settings (url, path, version) as a reference for later use with assets
		*/

		$this->settings = $settings;

		// do not delete!
		parent::__construct();

	}


	/*
	*  render_field_settings()
	*
	*  Create extra settings for your field. These are visible when editing a field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field_settings( $field ) {

		// choices
		$countries = acf_country_helpers::get_countries();
		acf_render_field_setting( $field, array(
			'label'			=> __('Choices','acf'),
			'name'			=> 'choices',
			'type'			=> 'textarea',
			'wrapper' => array(
				'class' => 'hidden',
			),
			'value' => acf_encode_choices($countries),
		));

		// allow_null
		acf_render_field_setting( $field, array(
			'label'			=> __('Allow Null?','acf'),
			'instructions'	=> '',
			'name'			=> 'allow_null',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));

		// multiple
		acf_render_field_setting( $field, array(
			'label'			=> __('Select multiple values?','acf'),
			'instructions'	=> '',
			'name'			=> 'multiple',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));

		// ui
		acf_render_field_setting( $field, array(
			'label'			=> __('Stylised UI','acf'),
			'instructions'	=> '',
			'name'			=> 'ui',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));

		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Return Format','acf'),
			'instructions'	=> __('Specify the value returned','acf'),
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'choices'		=> array(
				'array'	=> __('Country code and name (as array)', 'acf-country'),
				'code'	=> __('Country code', 'acf-country'),
				'name'	=> __('Country name', 'acf-country')
			)
		));

	}


	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field( $field ) {

		acf_country_helpers::render_field( $field );

		// acf()->fields->types['select']->render_field($field);

	}


	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	function input_admin_enqueue_scripts() {

		// vars
		$url     = $this->settings['url'];
		$path    = $this->settings['path'];
		$version = $this->settings['version'];

		if( ! class_exists('acf_field_select') ) {
			return;
		}

		if( !isset(acf()->fields->types['select']) ) {
			return;
		}

		acf()->fields->types['select']->input_admin_enqueue_scripts();

		// register & include JS
		wp_register_script( 'acf-country', "{$url}assets/js/acf-country.js", array('acf-input'), $version );
		wp_enqueue_script('acf-country');

		// register & include CSS
		wp_register_style( 'acf-country', "{$url}assets/css/acf-country.css", array('acf-input'), $version );
		wp_enqueue_style('acf-country');

		wp_register_style( 'famfamfam-flags', sprintf('%sassets/vendor/famfamfam-flags/dist/sprite/famfamfam-flags%s.css', $url, WP_DEBUG ? '' : '.min'), false, $version);
		wp_enqueue_style( 'famfamfam-flags' );

	}

	/*
	*  field_group_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	*  Use this action to add CSS + JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	function field_group_admin_enqueue_scripts() {
		// vars
		$url     = $this->settings['url'];
		$path    = $this->settings['path'];
		$version = $this->settings['version'];

		// register & include JS
		wp_register_script( 'acf-country-group', "{$url}assets/js/acf-country-group.js", array('acf-field-group'), $version );
		wp_enqueue_script('acf-country-group');
	}

	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/

	function load_value( $value, $post_id, $field ) {

		// ACF4 null
		if( $value === 'null' ) {
			return false;
		}

		// return
		return $value;

	}

	/*
	*  update_value()
	*
	*  This filter is applied to the $value before it is saved in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/

	function update_value( $value, $post_id, $field ) {

		// validate
		if( empty($value) ) {
			return $value;
		}

		// array
		if( is_array($value) ) {
			// save value as strings, so we can clearly search for them in SQL LIKE statements
			$value = array_map('strval', $value);
		}

		// return
		return $value;

	}


	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/

	function format_value( $value, $post_id, $field ) {

		// bail early if is empty
		if( acf_is_empty($value) ) {
			return $value;
		}

		return acf_country_helpers::format_country($value, $field);
	}

	/*
	*  validate_value()
	*
	*  This filter is used to perform validation on the value prior to saving.
	*  All values are validated regardless of the field's required setting. This allows you to validate and return
	*  messages to the user if the value is not correct
	*
	*  @type	filter
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$valid (boolean) validation status based on the value and the field's required setting
	*  @param	$value (mixed) the $_POST value
	*  @param	$field (array) the field array holding all the field options
	*  @param	$input (string) the corresponding input name for $_POST value
	*  @return	$valid
	*/

	function validate_value( $valid, $value, $field, $input ) {

		// if( !$field['allow_null'] && empty($value) ) {
		// 	return $valid = __('You must select a country', 'acf-country');
		// }

		// $countries = acf_country_helpers::get_countries();
		// if( !empty($value) ) {
		// 	if( is_array($value) && count(array_intersect($value, array_keys($countries))) !== count($value) ) {
		// 		$valid = __('One or more countries selected are not valid','acf-country');
		// 	} elseif( is_string($value) && !in_array($value, array_keys($countries)) ) {
		// 		$valid = __('The country selected is not a valid', 'acf-country');
		// 	}
		// }

		// return
		return $valid;

	}

}


// initialize
new acf_field_country( $this->settings );


// class_exists check
endif;

?>
