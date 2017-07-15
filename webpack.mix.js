let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.sass('resources/assets/sass/app.scss', 'public/css/app-all.css').version();

/* Concatenate all JS */
mix.scripts([
    'jquery-2.1.4.js',
    '../vendor/bootstrap3/js/bootstrap.min.js',
    '../vendor/bootstrap-tour/bootstrap-tour.min.js',
    'classie.js',
    'cbpAnimatedHeader.js',
    'app.js',
], 'public/js/app-all.js').version();

/* Move and shake our dependency files around too! */
mix.copy('resources/assets/css/images/**', 'public/build/css/images')
    .copy('resources/assets/css/AdminLTE/**', 'public/assets/css')
    .copy('resources/assets/js/AdminLTE/**', 'public/assets/js')
    .copy('resources/assets/vendor/bootstrap3/fonts/**', 'public/build/fonts')
    .copy('resources/assets/vendor/font-awesome/fonts/**', 'public/build/fonts')
    .copy('resources/assets/vendor/ionicons/fonts', 'public/build/fonts')
    .copy('resources/assets/images/**', 'public/assets/images');
