import path from 'node:path';
import MiniCssExtractPlugin from 'mini-css-extract-plugin';
import defaultConfig from '@wordpress/scripts/config/webpack.config.js';
import { WebpackManifestPlugin } from 'webpack-manifest-plugin';

const config = {
  ...defaultConfig,
  ...{
    context: path.resolve(process.cwd(), 'assets', 'src'),
    entry: {
      field: [
        path.resolve(process.cwd(), 'assets', 'src', 'js', 'field.js'),
        path.resolve(process.cwd(), 'assets', 'src', 'css', 'field.css'),
      ],
    },
    output: {
      filename: '[name]-[contenthash].js',
      path: path.resolve(process.cwd(), 'assets', 'dist'),
      clean: true,
    }
  },
  plugins: [
		...defaultConfig.plugins.filter(
			plugin =>
				! [ 'DependencyExtractionWebpackPlugin' ].includes(
					plugin.constructor.name
				)
		).map(plugin => {
      if(plugin.constructor.name === 'MiniCssExtractPlugin') {
        console.log(plugin.constructor.name);
        return new MiniCssExtractPlugin({
          filename: '[name]-[contenthash].css',
        });
      }
      return plugin;
    }),
    new WebpackManifestPlugin({
      basePath: '',
      publicPath: '',
    }),
  ],
};

export default config;
