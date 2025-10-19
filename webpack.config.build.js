const path = require('path');
const postcss = require('postcss');
const postcssDiscardComments = require('postcss-discard-comments');

const isDev = process.env.NODE_ENV !== 'production';
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const autoprefixer = require('autoprefixer');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');

module.exports = {
  mode: 'production',
  entry: {
    // ################################################
    // SCSS
    // ################################################
    'theme/moderation-state.theme': [
      './scss/theme/moderation-state.theme.scss',
    ],
  },
  output: {
    path: path.resolve(__dirname, 'css'),
    pathinfo: isDev,
    publicPath: '../../',
  },
  module: {
    rules: [
      {
        test: /\.(png|jpe?g|gif|svg)$/,
        exclude: /sprite\.svg$/,
        type: 'javascript/auto',
        use: [
          {
            loader: 'file-loader',
            options: {
              name: '[path][name].[ext]', // ?[contenthash]
              outputPath: '../../',
            },
          },
          {
            loader: 'img-loader',
            options: {
              enabled: !isDev,
            },
          },
        ],
      },
      {
        test: /\.(css|scss)$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
          },
          {
            loader: 'css-loader',
            options: {
              sourceMap: isDev,
              importLoaders: 2,
              url: {
                filter: (url) => {
                  // Don't handle sprite svg
                  if (url.includes('sprite.svg')) {
                    return false;
                  }

                  return true;
                },
              },
            },
          },
          {
            loader: 'postcss-loader',
            options: {
              sourceMap: isDev,
              postcssOptions: {
                plugins: [
                  autoprefixer(),
                  [
                    'postcss-perfectionist',
                    {
                      format: 'expanded',
                      indentSize: 2,
                      trimLeadingZero: true,
                      zeroLengthNoUnit: false,
                      maxAtRuleLength: false,
                      maxSelectorLength: false,
                      maxValueLength: false,
                    },
                  ],
                  postcssDiscardComments({ removeAll: true }),
                ],
              },
            },
          },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: isDev,
              // Global SCSS imports:
              additionalData: `
                @use "sass:color";
                @use "sass:math";
              `,
            },
          },
        ],
      },
    ],
  },
  resolve: {
    modules: [path.join(__dirname, 'node_modules')],
    extensions: ['.js', '.json'],
  },
  optimization: {
    minimizer: [
      new CssMinimizerPlugin({
        test: /\.css$/i,
        parallel: false,
        minify: async (data) => {
          const [[filename, input]] = Object.entries(data);
          const result = await postcss([
            postcssDiscardComments({ removeAll: true }),
          ]).process(input, {
            from: filename,
            to: filename,
            map: false,
          });

          return {
            code: result.css,
            map: result.map,
            warnings: result.warnings().map((warning) => warning.toString()),
          };
        },
      }),
    ],
    minimize: true,
  },
  plugins: [
    new RemoveEmptyScriptsPlugin(),
    new CleanWebpackPlugin({
      cleanStaleWebpackAssets: false,
    }),
    new MiniCssExtractPlugin(),
  ],
  watchOptions: {
    aggregateTimeout: 300,
    ignored: [
      '**/*.woff',
      '**/*.json',
      '**/*.woff2',
      '**/*.jpg',
      '**/*.png',
      '**/*.svg',
      'node_modules',
      'images',
    ],
  },
};
