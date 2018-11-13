(function($, undefined) {
	var Field = acf.models.SelectField.extend({
		type: 'country',
	});
	acf.registerFieldType( Field );
	acf.registerConditionForFieldType('contains', 'country');
	acf.registerConditionForFieldType('selectEqualTo', 'country');
	acf.registerConditionForFieldType('selectNotEqualTo', 'country');
})(jQuery);
