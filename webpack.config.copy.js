'use strict'
const CopyWebpackPlugin = require('copy-webpack-plugin')
module.exports = {
  mode: 'development',

  plugins: [
         new CopyWebpackPlugin([
        {from:'resources/js/WP',to:'js'},
        {from:'resources/images',to:'images'},
        {from:'resources/css',to:'css'},
        {from:'resources/fonts',to:'fonts'}
    ]),  
  ]
}