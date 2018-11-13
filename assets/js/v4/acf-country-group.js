(function($) {

	if(!acf.hasOwnProperty('conditional_logic')) {
		return;
	}

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
})(jQuery);
