const path = require('path');
const webpack = require('webpack');
const nodeEnvInfo = process.env.NODE_ENV;
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const FileManagerPlugin = require('filemanager-webpack-plugin');

const devMode = nodeEnvInfo !== 'production';

const plugins = [
    new webpack.DefinePlugin({
        'process.env': {
            NODE_ENV: JSON.stringify(nodeEnvInfo),
        },
    }),
    new webpack.SourceMapDevToolPlugin({
        filename: 'studio-bundle.js.map',
        exclude: 'studio-bundle.js',
    }),
    new MiniCssExtractPlugin({
        // Options similar to the same options in webpackOptions.output
        // both options are optional
        filename: 'css/studio-bundle.css',
        chunkFilename: 'css/[id].css',
    }),
];
if (!devMode) {
    plugins.push(
        new FileManagerPlugin({
            onStart: {
                delete: ['../public/htdocs/js/react'],
            },
        })
    );
}
module.exports = {
    mode: nodeEnvInfo,
    devtool: 'source-map',
    entry: ['@babel/polyfill', './src/index.js', './src/css/studio/index.scss'],
    output: {
        //path: __dirname,
        path: path.resolve(__dirname, '../public/htdocs/js/react'),
        publicPath: '/htdocs/js/react/',
        sourceMapFilename: 'studio-bundle.js.map',
        filename: 'studio-bundle.js',
        chunkFilename: !devMode
            ? '[name].[chunkhash].js'
            : '[name].studio-chunk-bundle.js',
    },
    node: {
        fs: 'empty',
    },

    plugins: plugins,

    module: {
        rules: [
            {
                test: /\.js$/,
                include: path.join(__dirname, 'src'),
                exclude: /(node_modules)|(dist)/,
                use: {
                    loader: 'babel-loader',
                },
            },
            {
                test: /\.(sa|sc|c)ss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    `sass-loader?outputStyle=${
                        devMode ? 'expanded' : 'compressed'
                    }`,
                ],
                exclude: /node_modules/,
            },
            {
                test: /\.svg$/,
                loader: 'file-loader',
            },
        ],
    },
    optimization: {
        minimizer: [
            new TerserPlugin({
                cache: true,
                parallel: true,
                sourceMap: true, // Must be set to true if using source-maps in production
                terserOptions: {
                    compress: {
                        drop_console: true,
                    },
                    ie8: false,
                },
            }),
            new OptimizeCssAssetsPlugin({
                cssProcessorOptions: {
                    map: {
                        inline: false, // set to false if you want CSS source maps
                    },
                },
            }),
        ],
    },
};
