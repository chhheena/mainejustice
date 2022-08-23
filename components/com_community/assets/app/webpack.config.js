const path = require('path');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = env => {
    const isDev = env && env.dev;
    const mode = isDev ? 'development' : 'production';

    return {
        watch: isDev ? true : false,

        mode,

        entry: './src/index.js',

        output: {
            filename: 'app.js',
            chunkFilename: isDev ? '_temp.[name].app.js' : '[name].app.js',
            path: path.resolve(__dirname, 'dist'),
        },

        module: {
            rules: [
                {
                    test: /\.vue$/,
                    loader: 'vue-loader',
                },
                {
                    test: /\.(css|scss)$/,
                    use: [
                        MiniCssExtractPlugin.loader,
                        'css-loader',
                        'sass-loader',
                    ]
                },
                {
                    test: /\.(png|jpe?g|gif)$/i,
                    use: [
                        {
                            loader: 'file-loader',
                            options: {
                                name: '[name].[ext]',
                                outputPath: 'images',
                            },
                        },
                    ],
                },
            ]
        },

        optimization: {
            splitChunks: {
                automaticNameDelimiter: '-',
            }
        },

        plugins: [
            new VueLoaderPlugin(),
            new MiniCssExtractPlugin({
                filename: isDev ? '_temp.[name].css' : '[name].css',
                chunkFilename: isDev ? '_temp.[name].css' : '[name].css',
            }),
        ],
    }
}