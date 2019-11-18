const path = require('path');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = {
  // モード
  // development|production|none
  mode: 'development',

  // メインとなるJavaScriptファイル（エントリーポイント）
  entry: {
    "default": "./public/template/default/assets/scss/style.scss",
  },

  devtool: "source-map",//ソースマップツールを有効

  // ファイルの出力設定
  output: {
    //  出力ディレクトリ
    // __dirnameは webpack.config.js があるディレクトリの絶対パス
    path: __dirname,

    // 出力ファイル名
    // [name]はentryがハッシュの場合、keyで置換される
    filename: 'public/template/[name]/assets/bundle.js'
  },

  module: {
    rules: [
      {
        test: /\.scss$/,
        use: [
          MiniCssExtractPlugin.loader,// javascriptとしてバンドルせず css として出力する
          {
            loader: 'css-loader',
            options: {
              url: false,
              sourceMap: true //ソースマップを有効
            }
          },
          {
            loader: 'postcss-loader',
            options: {
              sourceMap: true, //ソースマップを有効
              plugins: [
                require('autoprefixer')({
                  grid: true, // CSS Grid Layout を使いたいんだ
                  "browsers": [
                    "> 1%",
                    "IE 10"
                  ]
                })
              ]
            }
          },
          {
            loader: "sass-loader",
            options: {
              sourceMap: true //ソースマップを有効
            }
          }
        ]
      }

    ]
  },

  plugins: [
    new MiniCssExtractPlugin({
      // prefix は output.path
      filename: 'public/template/[name]/assets/css/style.css',
    })
  ]
};
