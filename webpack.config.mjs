import path from 'node:path';
import MiniCssExtractPlugin from 'mini-css-extract-plugin';
import defaultConfig from '@wordpress/scripts/config/webpack.config.js';
import rtlcss from 'rtlcss';
import { WebpackManifestPlugin } from 'webpack-manifest-plugin';

const src = path.resolve(process.cwd(), 'assets', 'src');

// Plugins from the wp-scripts config that do not apply here:
// - DependencyExtractionWebpackPlugin: the field script is enqueued by the plugin, with no *.asset.php file.
// - RtlCssPlugin: it writes an unhashed [name]-rtl.css; HashedRtlCssPlugin replaces it.
const skippedPlugins = ['DependencyExtractionWebpackPlugin', 'RtlCssPlugin'];

/**
 * Writes field-<hash>-rtl.css next to each field-<hash>.css: the name WordPress loads for a style
 * registered with wp_style_add_data( $handle, 'rtl', 'replace' ) on RTL sites.
 *
 * Runs once the CSS is minified and its hash final, so the RTL file follows the name of the LTR file.
 */
class HashedRtlCssPlugin {
  apply(compiler) {
    const { Compilation, sources } = compiler.webpack;
    compiler.hooks.thisCompilation.tap('HashedRtlCssPlugin', (compilation) => {
      compilation.hooks.processAssets.tap(
        { name: 'HashedRtlCssPlugin', stage: Compilation.PROCESS_ASSETS_STAGE_OPTIMIZE_TRANSFER },
        (assets) => {
          for (const name of Object.keys(assets).filter((asset) => asset.endsWith('.css'))) {
            const rtl = rtlcss.process(assets[name].source().toString());
            compilation.emitAsset(name.replace(/\.css$/, '-rtl.css'), new sources.RawSource(rtl));
          }
        }
      );
    });
  }
}

export default {
  ...defaultConfig,
  context: src,
  entry: {
    field: [path.resolve(src, 'js', 'field.js'), path.resolve(src, 'css', 'field.css')],
  },
  output: {
    filename: '[name]-[contenthash].js',
    path: path.resolve(process.cwd(), 'assets', 'dist'),
    clean: true,
  },
  plugins: [
    ...defaultConfig.plugins
      .filter((plugin) => !skippedPlugins.includes(plugin.constructor.name))
      .map((plugin) =>
        plugin.constructor.name === 'MiniCssExtractPlugin'
          ? new MiniCssExtractPlugin({ filename: '[name]-[contenthash].css' })
          : plugin
      ),
    new HashedRtlCssPlugin(),
    new WebpackManifestPlugin({
      basePath: '',
      publicPath: '',
      // WordPress derives the RTL URL from the LTR one.
      filter: (file) => !file.path.endsWith('-rtl.css'),
    }),
  ],
};
