'use strict'
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const path = require('path');
module.exports = {
  mode: 'development',
  entry: {
    main: ['./resources/js/babelpolyfill.js','./resources/js/backend.js'],
    front: ['./resources/js/babelpolyfill.js','./resources/js/front.js'],
  },
  plugins: [
    new BundleAnalyzerPlugin({
      reportFilename: path.resolve(__dirname, 'dist')
    })
  ]
}