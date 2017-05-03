(function($) {

	if( typeof acf.add_action !== 'undefined' ) {

		// Override some methods to allow conditional logic on country type
		acf.field_group.conditional_logic.extend({
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
				} else if( field_type == "select" || field_type == "checkbox" || field_type == "radio" || field_type == "country" ) {

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

	} else {

		acf.conditional_logic = $.extend(acf.conditional_logic, {
			render : function( $field ){

				// reference
				var _this = this;


				// vars
				var choices		= [],
					key			= $field.attr('data-id'),
					$ancestors	= $field.parents('.fields'),
					$tr			= $field.find('> .field_form_mask > .field_form > table > tbody > tr.conditional-logic');


				$.each( $ancestors, function( i ){

					var group = (i == 0) ? acf.l10n.sibling_fields : acf.l10n.parent_fields;

					$(this).children('.field').each(function(){


						// vars
						var $this_field	= $(this),
							this_id		= $this_field.attr('data-id'),
							this_type	= $this_field.attr('data-type'),
							this_label	= $this_field.find('tr.field_label input').val();


						// validate
						if( this_id == 'field_clone' )
						{
							return;
						}

						if( this_id == key )
						{
							return;
						}


						// add this field to available triggers
						if( this_type == 'country' || this_type == 'select' || this_type == 'checkbox' || this_type == 'true_false' || this_type == 'radio' )
						{
							choices.push({
								value	: this_id,
								label	: this_label,
								group	: group
							});
						}


					});

				});


				// empty?
				if( choices.length == 0 )
				{
					choices.push({
						'value' : 'null',
						'label' : acf.l10n.no_fields
					});
				}


				// create select fields
				$tr.find('.conditional-logic-field').each(function(){

					var val = $(this).val(),
						name = $(this).attr('name');


					// create select
					var $select = acf.helpers.create_field({
						'type'		: 'select',
						'classname'	: 'conditional-logic-field',
						'name'		: name,
						'value'		: val,
						'choices'	: choices
					});


					// update select
					$(this).replaceWith( $select );


					// trigger change
					$select.trigger('change');

				});

			},
			change_trigger : function( $select ){

				// vars
				var val			= $select.val(),
					$trigger	= $('.field_key-' + val),
					type		= $trigger.attr('data-type'),
					$value		= $select.closest('tr').find('.conditional-logic-value'),
					choices		= [];


				// populate choices
				if( type == "true_false" )
				{
					choices = [
						{ value : 1, label : acf.l10n.checked }
					];

				}
				else if( type == "country" || type == "select" || type == "checkbox" || type == "radio" )
				{
					var field_choices = $trigger.find('.field_option-choices').val().split("\n");

					if( field_choices )
					{
						for( var i = 0; i < field_choices.length; i++ )
						{
							var choice = field_choices[i].split(':');

							var label = choice[0];
							if( choice[1] )
							{
								label = choice[1];
							}

							choices.push({
								'value' : $.trim( choice[0] ),
								'label' : $.trim( label )
							});

						}
					}

				}


				// create select
				var $select = acf.helpers.create_field({
					'type'		: 'select',
					'classname'	: 'conditional-logic-value',
					'name'		: $value.attr('name'),
					'value'		: $value.val(),
					'choices'	: choices
				});

				$value.replaceWith( $select );

				$select.trigger('change');

			}
		});

	}

})(jQuery);
