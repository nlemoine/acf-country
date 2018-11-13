(function($, undefined) {
	/**
	 * Get ACF country data
	 *
	 * @param  {string} key
	 * @return {(string|bool)}
	 */
	function get_data(key) {
		return typeof acfCountryGroup != "undefined" &&
			acfCountryGroup.hasOwnProperty(key) &&
			acfCountryGroup[key]
			? acfCountryGroup[key].toString()
			: false;
	}

	var compareVersions = require("compare-versions");
	var acf_version = get_data("acf_version");

	// v4
	if (compareVersions(acf_version, "5") === -1) {
		require("./v4/acf-country-group.js");
	}
	// 5 -> 5.3.0
	if (
		compareVersions(acf_version, "4.9.99") === 1 &&
		compareVersions(acf_version, "5.3.1") === -1
	) {
		console.log(acf_version);
		require("./v5/acf-country-group.js");
	}
	// 5.3.1 -> 5.5.x
	if (
		compareVersions(acf_version, "5.3.0") === 1 &&
		compareVersions(acf_version, "5.6") === -1
	) {
		require("./v5.3.1/acf-country-group.js");
	}
	// 5.6.x
	if (
		compareVersions(acf_version, "5.5.99") === 1 &&
		compareVersions(acf_version, "5.7") === -1
	) {
		require("./v5.6/acf-country-group.js");
	}
	// 5.7+
	if (compareVersions(acf_version, "5.6.99") === 1) {
		require("./v5.7/acf-country.js");
	}
})(jQuery);
