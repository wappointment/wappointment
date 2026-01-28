const path = require('path');

module.exports = {
    entry: './resources/js-react-widget/index.js',
    output: {
        path: path.resolve(__dirname, 'dist'),
        filename: 'widget.js'
    },
    module: {
        rules: [
            {
                test: /\.jsx?$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-react']
                    }
                }
            }
        ]
    },
    resolve: {
        extensions: ['.js', '.jsx']
    },
    externals: {
        'react': 'React',
        'react-dom': 'ReactDOM'
    }
};
