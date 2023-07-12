const mix = require("laravel-mix");

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.options({
    processCssUrls: false,
});

if (!mix.inProduction()) {
    mix.webpackConfig({
        devtool: "source-map",
    }).sourceMaps();
}

mix.js("resources/js/app.js", "public/js");
mix.combine(
    ["resources/js/laraadmin/app.js", "resources/js/laraadmin/base.js"],
    "public/la-assets/js/app.js"
);
mix.less("resources/less/home.less", "public/css");
mix.less("resources/less/LaraAdmin.less", "public/la-assets/css").options({
    processCssUrls: false,
});
