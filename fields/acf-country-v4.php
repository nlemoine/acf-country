<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('acf_field_country') ) :


class acf_field_country extends acf_field {

	// vars
	var $settings, // will hold info such as dir / path
		$defaults; // will hold default field options


	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/

	function __construct( $settings )
	{
		// vars
		$this->name     = 'country';
		$this->label    = __('Country', 'acf-country');
		$this->category = __('Choice', 'acf');
		$this->defaults = acf_country_helpers::get_defaults();

		// do not delete!
    	parent::__construct();


    	// settings
		$this->settings = $settings;

	}


	/*
	*  create_options()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like below) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/

	function create_options( $field )
	{
		$key = $field['name'];
		$countries = acf_country_helpers::get_countries();
		array_walk($countries, function(&$value, $key) {
			$value = $key . ' : ' . $value;
		});
		$choices = implode("\n", $countries);
		?>
<tr class="field_option field_option_<?php echo $this->name; ?> hidden">
	<td class="label">
		<label for=""><?php _e("Choices",'acf'); ?></label>
	</td>
	<td>
		<?php
		do_action('acf/create_field', array(
			'type'	=>	'textarea',
			'class' => 	'textarea field_option-choices',
			'name'	=>	'fields['.$key.'][choices]',
			'value'	=>	$choices,
		));
		?>
	</td>
</tr>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e('Allow Null?', 'acf'); ?></label>
	</td>
	<td>
		<?php
		do_action('acf/create_field', array(
			'type'	=>	'radio',
			'name'	=>	'fields['.$key.'][allow_null]',
			'value'	=>	$field['allow_null'],
			'choices'	=>	array(
				1	=>	__('Yes','acf'),
				0	=>	__('No','acf'),
			),
			'layout'	=>	'horizontal',
		));
		?>
	</td>
</tr>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e('Select multiple values?','acf'); ?></label>
	</td>
	<td>
		<?php
		do_action('acf/create_field', array(
			'type'	=>	'radio',
			'name'	=>	'fields['.$key.'][multiple]',
			'value'	=>	$field['multiple'],
			'choices'	=>	array(
				1	=>	__('Yes','acf'),
				0	=>	__('No','acf'),
			),
			'layout'	=>	'horizontal',
		));
		?>
	</td>
</tr>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e('Stylised UI','acf'); ?></label>
	</td>
	<td>
		<?php
		do_action('acf/create_field', array(
			'type'	=>	'radio',
			'name'	=>	'fields['.$key.'][ui]',
			'value'	=>	$field['ui'],
			'choices'	=>	array(
				1	=>	__('Yes','acf'),
				0	=>	__('No','acf'),
			),
			'layout'	=>	'horizontal',
		));
		?>
	</td>
</tr>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e('Return format','acf'); ?></label>
	</td>
	<td>
		<?php
		do_action('acf/create_field', array(
			'type'	=>	'select',
			'name'	=>	'fields['.$key.'][return_format]',
			'value'	=>	$field['return_format'],
			'choices'	=>	array(
				'array'	=> __('Country code and name (as array)', 'acf-country'),
				'code'	=> __('Country code', 'acf-country'),
				'name'	=> __('Country name', 'acf-country'),
			),
			'layout'	=>	'horizontal',
		));
		?>
	</td>
</tr>
<?php

	}


	/*
	*  create_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function create_field( $field )
	{

		// defaults
		$field = array_merge($this->defaults, $field);

		acf_country_helpers::render_field( $field );

	}


	/*
	*  format_value_for_api()
	*
	*  This filter is applied to the $value after it is loaded from the db and before it is passed back to the API functions such as the_field
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/

	function format_value_for_api( $value, $post_id, $field )
	{

		if( empty($value) ) {
			return $value;
		}

		// defaults
		$field = array_merge($this->defaults, $field);

		return acf_country_helpers::format_country($value, $field);
	}

	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your create_field() action.
	*
	*  $info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function input_admin_enqueue_scripts()
	{

		// vars
		$url     = $this->settings['url'];
		$path    = $this->settings['path'];
		$version = $this->settings['version'];

		// register & include JS
		wp_register_script( 'select2-v3.5.4', sprintf('%sassets/vendor/select2/select2%s.js', $url, WP_DEBUG ? '' : '.min'), array('jquery'), $version );
		wp_register_script( 'acf-country', sprintf('%sassets/js/acf-country.js', $url), array('select2-v3.5.4' ,'acf-input'), $version );
		wp_enqueue_script('acf-country');

		$locale = str_replace('_', '-', get_locale());
		$locale_file = '%sassets/vendor/select2/select2_locale_%s.js';

		if( is_file(sprintf($locale_file, $path, $locale)) ) {
			wp_enqueue_script( 'select2-locale', sprintf($locale_file, $url, $locale), array('select2-v3.5.4'), $version );
		} elseif( is_file(sprintf($locale_file, $path, substr($locale, 0, 2))) ) {
			wp_enqueue_script( 'select2-locale', sprintf($locale_file, $url, substr($locale, 0, 2)), array('select2-v3.5.4'), $version );
		}

		// register & include CSS
		wp_register_style( 'famfamfam-flags', sprintf('%sassets/vendor/famfamfam-flags/dist/sprite/famfamfam-flags%s.css', $url, WP_DEBUG ? '' : '.min'), false,$version);
		wp_register_style( 'select2-v3.5.4', sprintf('%sassets/vendor/select2/select2.css', $url), array('acf-input'), $version );
		wp_register_style( 'acf-country', sprintf('%sassets/css/acf-country.css', $url), array('acf-input', 'select2-v3.5.4', 'famfamfam-flags'), $version );
		wp_enqueue_style('acf-country');

	}

	/*
	*  field_group_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	*  Use this action to add CSS + JavaScript to assist your create_field_options() action.
	*
	*  $info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	function field_group_admin_enqueue_scripts()
	{

		// vars
		$url     = $this->settings['url'];
		$path    = $this->settings['path'];
		$version = $this->settings['version'];

		wp_register_script( 'acf-country-group', "{$url}assets/js/acf-country-group.js", array('acf-field-group'), $version );
		wp_enqueue_script('acf-country-group');
	}

}


// initialize
new acf_field_country( $this->settings );


// class_exists check
endif;

?>
