const mix = require('laravel-mix');

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

mix.js('resources/js/app.js', 'public/js')
    .postCss('resources/css/sanitize.css', 'public/css', [])
    .postCss('resources/css/layouts/header.css', 'public/css/layouts', [])
    .postCss('resources/css/attendance/list.css', 'public/css/attendance', [])
    .postCss('resources/css/attendance/index.css', 'public/css/attendance', [])
    .postCss('resources/css/attendance/detail.css', 'public/css/attendance', [])
    .postCss('resources/css/auth/login.css', 'public/css/auth', [])
    .postCss('resources/css/auth/register.css', 'public/css/auth', [])
    .postCss('resources/css/auth/verify-email.css', 'public/css/auth', [])
    .postCss('resources/css/stamp_correction_request/list.css', 'public/css/stamp_correction_request', []);
