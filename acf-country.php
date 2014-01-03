<?php
/*
Plugin Name: Advanced Custom Fields: Country
Plugin URI: {{git_url}}
Description: {{short_description}}
Version: 1.0.0
Author: Nicolas Lemoine
Author URI: {{website}}
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

require __DIR__ . '/vendor/autoload.php';

class acf_field_country_plugin
{
	/*
	*  Construct
	*
	*  @description:
	*  @since: 3.6
	*  @created: 1/04/13
	*/

	function __construct()
	{
		// set text domain
		/*
		$domain = 'acf-country';
		$mofile = trailingslashit(dirname(__File__)) . 'lang/' . $domain . '-' . get_locale() . '.mo';
		load_textdomain( $domain, $mofile );
		*/


		// version 4+
		add_action('acf/register_fields', array($this, 'register_fields'));


		// version 3-
		add_action('init', array( $this, 'init' ), 5);
	}


	/*
	*  Init
	*
	*  @description:
	*  @since: 3.6
	*  @created: 1/04/13
	*/

	function init()
	{
		if(function_exists('register_field'))
		{
			register_field('acf_field_country', dirname(__File__) . '/country-v3.php');
		}
	}

	/*
	*  register_fields
	*
	*  @description:
	*  @since: 3.6
	*  @created: 1/04/13
	*/

	function register_fields()
	{
		include_once('country-v4.php');
	}

}

new acf_field_country_plugin();

?>
