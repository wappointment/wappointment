'use strict'
const CopyWebpackPlugin = require('copy-webpack-plugin')
module.exports = {
  mode: 'development',
  entry: {
    main: ['./blank.js'],
  },
  plugins: [
         new CopyWebpackPlugin([
        {from:'resources/js/WP/export',to:'js'},
        {from:'resources/images',to:'images'},
        {from:'resources/css',to:'css'},
        {from:'resources/fonts',to:'fonts'}
    ]),  
  ]
}