const mix = require('laravel-mix');

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

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
   .options({
       processCssUrls: false
   });

// Copy AdminLTE assets
mix.copy('node_modules/admin-lte/dist/css/adminlte.min.css', 'public/css/adminlte.min.css')
   .copy('node_modules/admin-lte/dist/js/adminlte.min.js', 'public/js/adminlte.min.js');

// Copy Bootstrap
mix.copy('node_modules/bootstrap/dist/css/bootstrap.min.css', 'public/css/bootstrap.min.css')
   .copy('node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', 'public/js/bootstrap.bundle.min.js');

// Copy jQuery
mix.copy('node_modules/jquery/dist/jquery.min.js', 'public/js/jquery.min.js');

// Copy Chart.js
mix.copy('node_modules/chart.js/dist/chart.min.js', 'public/js/chart.min.js');

// Copy Font Awesome
mix.copy('node_modules/font-awesome/css/font-awesome.min.css', 'public/css/font-awesome.min.css')
   .copyDirectory('node_modules/font-awesome/fonts', 'public/fonts');

if (mix.inProduction()) {
    mix.version();
}