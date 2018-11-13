const mix = require('laravel-mix');

const assets    = 'assets';
const dist      = 'assets/dist';

mix.setPublicPath(dist);
mix.setResourceRoot('../');

// SASS
mix
	.sass(`${assets}/css/acf-country.scss`, `${dist}/css`)
	 .options({
				postCss: [
						require('postcss-import')(),
						require('postcss-url')()
				]
	 });

// Javascript
mix
	.js(`${assets}/js/acf-country-input.js`, `${dist}/js`)
	.js(`${assets}/js/acf-country-group.js`, `${dist}/js`)
;

// Assets
mix.copyDirectory('node_modules/select2', `${assets}/vendor/select2`);

// Source maps when not in production.
if (!mix.inProduction()) {
	mix.sourceMaps();
}

// Hash and version files in production.
if (mix.inProduction()) {
	mix.version();
}
