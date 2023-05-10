'use strict'
const webpack = require('webpack')
const { VueLoaderPlugin } = require('vue-loader')
const path = require('path');
const CleanWebpackPlugin = require('clean-webpack-plugin'); 
const ManifestPlugin = require('webpack-manifest-plugin');
const WebpackAssetsManifest = require('webpack-assets-manifest');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin

module.exports = {
  mode: process.env.NODE_ENV,
  entry: {
    main: ['./resources/js/backend.js'],
    front: ['./resources/js/front.js'],
  },
  output: {
    filename: '[name].[contenthash].bundle.js',
    path: path.resolve(__dirname, 'dist'),
    chunkFilename: '[name].[contenthash].bundle.js',
  },

  resolve: {
    extensions: ['.vue', '.js'],
    enforceExtension: false,
    mainFiles: ['index']
  },
  optimization: {
    splitChunks: {
      automaticNameDelimiter: '-',
    },
    minimize: true
  },
  module: {
    rules: [
      {
        test: /\.vue$/,
        use: [
          {
            loader: 'vue-loader',
            options: {
              loaders: {
                  'scss': 'vue-style-loader!css-loader!sass-loader',
                  'sass': 'vue-style-loader!css-loader!sass-loader?indentedSyntax'
              },
              url: false,
            }
          }
        ],
      },
      {
        test: /\.css$/,
        use: [
          'vue-style-loader',
          {
            loader: 'css-loader',
          }
        ],
      },
      {
        test: /\.js$/,
        exclude: /(node_modules)/,
        use: [
          'babel-loader'
        ]
      },
      {
        test: /\.scss$/,
        use: [
          'css-loader',
          'sass-loader'
        ]
      },
      {
        test: /\.(png|jpg|gif|svg)$/,
        use: [
          {
            loader: 'file-loader',
            options: {},
          },
        ],
      },
    ]
  },
  plugins: [
    new webpack.IgnorePlugin({
        resourceRegExp: /^\.\/locale$/,
        contextRegExp: /moment$/
      }),
    new WebpackAssetsManifest({}),
    new VueLoaderPlugin(),
    new webpack.HashedModuleIdsPlugin(),
    // new BundleAnalyzerPlugin({
    //   analyzerHost: '192.168.56.56'
    // })
    {
       apply: (compiler) => {
         compiler.hooks.done.tap('DonePlugin', (stats) => {
           console.log('Compile is done !')
           setTimeout(() => {
             process.exit(0)
           })
         });
       }
    }
  ], 

}