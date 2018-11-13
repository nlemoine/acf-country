(function($) {

	if(!acf.hasOwnProperty('field_group')) {
		return;
	}

	if(!acf.field_group.hasOwnProperty('conditions')) {
		return;
	}

	$.extend(acf.field_group.conditions, {
		render : function( $field ){

			// reference
			var self = this;


			// vars
			var choices		= [],
				key			= $field.attr('data-key'),
				$ancestors	= $field.parents('.acf-field-list'),
				$tr			= $field.find('> .settings > table > tbody > tr[data-name="conditional_logic"]');


			$.each( $ancestors, function( i ){

				var group = (i == 0) ? acf.l10n.sibling_fields : acf.l10n.parent_fields;

				$(this).children('.acf-field-object').each(function(){


					// vars
					var $this_field	= $(this),
						this_key	= $this_field.attr('data-key'),
						this_type	= $this_field.attr('data-type'),
						this_label	= $this_field.find('> .settings > table > tbody > tr[data-name="label"] input').val();


					// validate
					if( this_key == 'acfcloneindex' ) {

						return;

					} else if( this_key == key ) {

						return;

					}


					// add this field to available triggers
					if( this_type == 'country' || this_type == 'select' || this_type == 'checkbox' || this_type == 'true_false' || this_type == 'radio' )
					{
						choices.push({
							value	: this_key,
							label	: this_label,
							group	: group
						});
					}


				});

			});


			// empty?
			if( choices.length == 0 ) {

				choices.push({
					'value' : '',
					'label' : acf.l10n.no_fields
				});

			}


			// create select fields
			$tr.find('.conditional-logic-field').each(function(){

				self.update_select( $(this), choices );

				self.change_trigger( $(this) );

			});

		},
		change_trigger : function( $select ){

			// vars
			var val			= $select.val(),
				$trigger	= this.$el.find('.acf-field-object[data-key="' + val + '"]'),
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
				var field_choices = $trigger.find('tr[data-name="choices"] textarea').val().split("\n");
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


				// allow null
				$allow_null = $trigger.find('tr[data-name="allow_null"]');

				if( $allow_null.exists() ) {

					if( $allow_null.find('input:checked').val() == '1' ) {

						choices.unshift({
							'value' : '',
							'label' : acf._e('null')
						});

					}

				}


			}


			// update select
			this.update_select( $value, choices );

		}
	});

})(jQuery);
