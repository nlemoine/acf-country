(function($) {

	if(!acf.hasOwnProperty('conditional_logic')) {
		return;
	}

	// Extend conditional logic to allow country type
	$.extend(acf.conditional_logic, {
		calculate : function( rule, $trigger, $target ){

			// debug
			//console.log( 'calculate(%o, %o, %o)', rule, $trigger, $target);


			// vars
			var type = acf.get_data($trigger, 'type');


			// input with :checked
			if( type == 'true_false' || type == 'checkbox' || type == 'radio' ) {

				var exists = $trigger.find('input[value="' + rule.value + '"]:checked').exists();

				if( rule.operator == "==" && exists ) {

					return true;

				} else if( rule.operator == "!=" && !exists ) {

					return true;

				}

			} else if( type == 'select' || type == 'country' ) {

				// vars
				var $select = $trigger.find('select'),
					data = acf.get_data( $select ),
					val = [];


				if( data.multiple && data.ui ) {

					if( type == 'country' ) {

						val = $select.val();

					} else {

						$trigger.find('.acf-select2-multi-choice').each(function(){

							val.push( $(this).val() );

						});
					}

				} else if( data.multiple ) {

					val = $select.val();

				} else if( data.ui ) {

					if( type == 'country' ) {

						val.push( $select.val() );

					} else {

						val.push( $trigger.find('input').first().val() );

					}

				} else {

					val.push( $select.val() );

				}


				if( rule.operator == "==" ) {

					if( $.inArray(rule.value, val) > -1 ) {

						return true;

					}

				} else {

					if( $.inArray(rule.value, val) < 0 ) {

						return true;

					}

				}

			}


			// return
			return false;

		}
	});

})(jQuery);
