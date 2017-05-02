(function($){

	function format_country(code, country) {
		return '<span class="acf-country-flag-icon famfamfam-flags ' + code + '"></span> <span class="acf-country-flag-name">' + country + '</span>';
	}

	function format_country_result(result, container, query, escapeMarkup) {
		var text = $.fn.select2.defaults.formatResult(result, container, query, escapeMarkup);
		return format_country(result.id.toLowerCase(), text);
	}

	function format_country_selection(result, container, query, escapeMarkup) {
		return format_country(result.id.toLowerCase(), result.text);
	}

	function init_field( $el ) {
		var $acf_country = $el.find('[data-ui="1"]');
		if( !$acf_country.length ) {
			return;
		}
		$acf_country.select2({
			allowClear: !!$acf_country.data('allow-null'),
			formatResult: format_country_result,
			formatSelection: format_country_selection
		});
	}

	if( typeof acf.add_action !== 'undefined' ) {

		/*
		*  ready append (ACF5)
		*
		*  These are 2 events which are fired during the page load
		*  ready = on page load similar to $(document).ready()
		*  append = on new DOM elements appended via repeater field
		*
		*  @type	event
		*  @date	20/07/13
		*
		*  @param	$el (jQuery selection) the jQuery element which contains the ACF fields
		*  @return	n/a
		*/

		acf.add_action('ready append', function( $el ){

			// search $el for fields of type 'acf_country'
			acf.get_fields({ type : 'country'}, $el).each(function(){

				init_field($(this));

			});

		});

		// Extend conditional logic to allow country type
		acf.conditional_logic.extend({
			calculate : function( rule, $trigger, $target ){

				// bail early if $trigger could not be found
				if( !$trigger || !$target ) return false;

				// debug
				//console.log( 'calculate(%o, %o, %o)', rule, $trigger, $target);

				// vars
				var match = false,
					type = $trigger.data('type');


				// input with :checked
				if( type == 'true_false' || type == 'checkbox' || type == 'radio' ) {

					match = this.calculate_checkbox( rule, $trigger );


				} else if( type == 'select' || type == 'country' ) {

					match = this.calculate_select( rule, $trigger );

				}

				// reverse if 'not equal to'
				if( rule.operator === "!=" ) {

					match = !match;

				}

				// return
				return match;

			}
		});

	} else {


		/*
		*  acf/setup_fields (ACF4)
		*
		*  This event is triggered when ACF adds any new elements to the DOM.
		*
		*  @type	function
		*  @since	1.0.0
		*  @date	01/01/12
		*
		*  @param	event		e: an event object. This can be ignored
		*  @param	Element		postbox: An element which contains the new HTML
		*
		*  @return	n/a
		*/

		$(document).on('acf/setup_fields', function(e, postbox){

			$(postbox).find('.field[data-field_type="country"]').each(function() {

        		init_field($(this));

			});

		});


	}


})(jQuery);
