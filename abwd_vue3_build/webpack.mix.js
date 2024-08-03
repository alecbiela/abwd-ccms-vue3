let mix = require('laravel-mix');

mix.setPublicPath('../abwd_vue3');

mix.sass('assets/scss/main.scss', 'css/main.css')
 .js('assets/js/main.js', 'js/main.js').vue({ version: 3 });