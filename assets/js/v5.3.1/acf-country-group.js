(function($) {

	if(!acf.hasOwnProperty('model')) {
		return;
	}

	var overrides = acf.model.extend({
		actions: {
			'open_field': 'render_field'
		},
		render_field: function( $field ){

			// reference
			var self = this;


			// vars
			var key			= $field.attr('data-key'),
				$ancestors	= $field.parents('.acf-field-list'),
				$tr			= $field.find('.acf-field[data-name="conditional_logic"]:last');


			// choices
			var choices	= [];


			// loop over ancestors
			$.each( $ancestors, function( i ){

				// vars
				var group = (i == 0) ? acf._e('sibling_fields') : acf._e('parent_fields');


				// loop over fields
				$(this).children('.acf-field-object').each(function(){

					// vars
					var $this_field	= $(this),
						this_key	= $this_field.attr('data-key'),
						this_type	= $this_field.attr('data-type'),
						this_label	= $this_field.find('.field-label:first').val();


					// validate
					if( $.inArray(this_type, ['select', 'checkbox', 'true_false', 'radio', 'country']) === -1 ) {

						return;

					} else if( this_key == 'acfcloneindex' ) {

						return;

					} else if( this_key == key ) {

						return;

					}


					// add this field to available triggers
					choices.push({
						value:	this_key,
						label:	this_label,
						group:	group
					});

				});

			});


			// empty?
			if( !choices.length ) {

				choices.push({
					value: '',
					label: acf._e('no_fields')
				});

			}


			// create select fields
			$tr.find('.rule').each(function(){

				self.render_rule( $(this), choices );

			});

		},
		render_rule: function( $tr, triggers ) {

			// vars
			var $trigger	= $tr.find('.conditional-rule-param'),
				$value		= $tr.find('.conditional-rule-value');


			// populate triggers
			if( triggers ) {

				acf.render_select( $trigger, triggers );

			}


			// vars
			var $field		= $('.acf-field-object[data-key="' + $trigger.val() + '"]'),
				field_type	= $field.attr('data-type'),
				choices		= [];


			// populate choices
			if( field_type == "true_false" ) {

				choices.push({
					'value': 1,
					'label': acf._e('checked')
				});

			// select
			} else if( field_type == "country" || field_type == "select" || field_type == "checkbox" || field_type == "radio" ) {

				// vars
				var lines = $field.find('.acf-field[data-name="choices"] textarea').val().split("\n");

				$.each(lines, function(i, line){

					// explode
					line = line.split(':');


					// default label to value
					line[1] = line[1] || line[0];


					// append
					choices.push({
						'value': $.trim( line[0] ),
						'label': $.trim( line[1] )
					});

				});


				// allow null
				var $allow_null = $field.find('.acf-field[data-name="allow_null"]');

				if( $allow_null.exists() ) {

					if( $allow_null.find('input:checked').val() == '1' ) {

						choices.unshift({
							'value': '',
							'label': acf._e('null')
						});

					}

				}

			}


			// update select
			acf.render_select( $value, choices );

		}
	});

	acf.field_group.conditional_logic.render_rule = overrides.render_rule;

	wp.hooks.removeAction('acf/open_field', acf.field_group.conditional_logic.render_field);
	wp.hooks.removeAction('acf.open_field', acf.field_group.conditional_logic.render_field);

})(jQuery);
