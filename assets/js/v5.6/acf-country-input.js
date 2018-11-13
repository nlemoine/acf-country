(function($) {

	if(!acf.hasOwnProperty('conditional_logic')) {
		return;
	}

	// Extend conditional logic to allow country type
	$.extend(acf.conditional_logic, {
		calculate: function( rule, $trigger, $target ){

			// bail early if $trigger could not be found
			if( !$trigger || !$target ) return false;


			// debug
			//console.log( 'calculate(%o, %o, %o)', rule, $trigger, $target);


			// vars
			var match = false,
				type = $trigger.data('type');


			// input with :checked
			if( type == 'true_false' || type == 'checkbox' || type == 'radio' || type == 'button_group' ) {

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

})(jQuery);
