(function($, undefined) {
	/**
	 * Format country (Select3 v3)
	 *
	 * @param  {string} code
	 * @param  {string} country
	 * @return {string}
	 */
	function format_v3_country(code, country) {
		return (
			'<span class="acf-country-flag-icon famfamfam-flags ' +
			code +
			'"></span> <span class="acf-country-flag-name">' +
			country +
			"</span>"
		);
	}

	/**
	 * Format country result (Select3 v3)
	 *
	 * @param  {object} result
	 * @param  {jQuery} container
	 * @param  {object} query
	 * @param  {function} escapeMarkup
	 * @return {string}
	 */
	function format_v3_country_result(result, container, query, escapeMarkup) {
		var text = $.fn.select2.defaults.formatResult(
			result,
			container,
			query,
			escapeMarkup
		);
		return format_v3_country(result.id.toLowerCase(), text);
	}

	/**
	 * Format country selection (Select3 v3)
	 *
	 * @param  {object} result
	 * @param  {jQuery} container
	 * @param  {object} query
	 * @param  {function} escapeMarkup
	 * @return {string}
	 */
	function format_v3_country_selection(result, container, query, escapeMarkup) {
		return format_v3_country(result.id.toLowerCase(), result.text);
	}

	/**
	 * Format country (Select3 v4)
	 *
	 * @param  {object} state
	 * @return {jQuery}
	 */
	function format_country(state) {
		if (!state.id) {
			return state.text;
		}
		return $(
			'<span class="acf-country-flag-icon famfamfam-flags ' +
				state.id.toLowerCase() +
				'"></span> <span class="acf-country-flag-name">' +
				state.text +
				"</span>"
		);
	}

	/**
	 * Get ACF country data
	 *
	 * @param  {string} key
	 * @return {(string|bool)}
	 */
	function get_data(key) {
		return typeof acfCountry != "undefined" &&
			acfCountry.hasOwnProperty(key) &&
			acfCountry[key]
			? acfCountry[key].toString()
			: false;
	}

	/**
	 * Initialiaze field
	 *
	 * @param  {jQuery} $el
	 */
	function init_field($el) {
		var $acf_country = $el.find('[data-ui="1"]');
		if (!$acf_country.length) {
			return;
		}
		var select2_version = get_data("select2_version");

		var options = {
			containerCssClass: "-acf acf-select2-multi-choice",
			allowClear: !!$acf_country.data("allow-null"),
			width: "100%"
		};
		if (select2_version === "3") {
			$.extend(options, {
				formatResult: format_v3_country_result,
				formatSelection: format_v3_country_selection
			});
		} else if (select2_version === "4") {
			$.extend(options, {
				templateResult: format_country,
				templateSelection: format_country
			});
		}
		$acf_country.select2(options);
		var $container = $acf_country.next(".select2-container");
		$container.addClass("-acf");
	}

	/**
	 * Init field version 5.7+
	 *
	 * @see https://www.advancedcustomfields.com/resources/javascript-api/#compatibility
	 * @param  {object} field
	 */
	function init_fields_56(field) {
		init_field(field.$el);
	}

	/**
	 * Init field version 5 -> 5.6
	 *
	 * @see https://www.advancedcustomfields.com/resources/javascript-api/#compatibility
	 * @param  {jQuery} $field
	 */
	function init_fields_5($field) {
		init_field($field);
	}

	var acf_version = get_data("acf_version");
	var compareVersions = require("compare-versions");

	// 5 -> 5.5.x
	if (
		compareVersions(acf_version, "4.9.99") === 1 &&
		compareVersions(acf_version, "5.6") === -1
	) {
		require("./v5/acf-country-input.js");
	}
	// 5.6.x
	if (
		compareVersions(acf_version, "5.5.99") === 1 &&
		compareVersions(acf_version, "5.7") === -1
	) {
		require("./v5.6/acf-country-input.js");
	}
	// 5.7+
	if (compareVersions(acf_version, "5.6.99") === 1) {
		require("./v5.7/acf-country.js");
		acf.add_filter("select2_args", function(
			args,
			$select,
			settings,
			field,
			instance
		) {
			if (instance.data.field.get("type") !== "country") {
				return args;
			}
			$.extend(args, {
				templateResult: format_country,
				templateSelection: format_country
			});
			return args;
		});
	}

	if (typeof acf.addAction !== "undefined") {
		if (compareVersions(acf_version, "5.7") === -1) {
			acf.addAction("ready_field/type=country", init_fields_56);
			acf.addAction("append_field/type=country", init_fields_56);
		}
	} else if (typeof acf.add_action !== "undefined") {
		acf.add_action("ready_field/type=country", init_fields_5);
		acf.add_action("append_field/type=country", init_fields_5);
	} else {
		$(document).on("acf/setup_fields", function(e, postbox) {
			$(postbox)
				.find('.field[data-field_type="country"]')
				.each(function() {
					init_field($(this));
				});
		});
	}
})(jQuery);
