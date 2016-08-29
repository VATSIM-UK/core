var elixir = require('laravel-elixir');

elixir(function (mix) {
    /* Concatenate all CSS */
    mix.sass([
        /*"jquery-ui.cupertino.1.11.4.css",
        "design.css",
        "font-awesome.4.5.0.min.css",
        "../vendor/bootstrap-slider/css/bootstrap-slider.css",
        "../vendor/bootstrap-summernote/summernote.css",*/
        "app.scss",
    ], 'public/css/app-all.css');

    /* Concatenate all JS */
    mix.scripts([
        /*"jquery-1.11.3.js",
        "../vendor/js-cookie/src/js.cookie.js",
        "../vendor/jquery-ui/jquery-ui.js",
        "../vendor/bootstrap-summernote/summernote.js",
        "../vendor/bootstrap-switch/bootstrap-switch.min.js",*/
        "jquery-2.1.4.js",
        "../vendor/bootstrap3/js/bootstrap.min.js",
        "../vendor/bootstrap-tour/bootstrap-tour.min.js",
        "classie.js",
        "cbpAnimatedHeader.js",
        "app.js",
    ], "public/js/app-all.js");

    /* Cache Busting */
    mix.version([
        "public/css/app-all.css",
        "public/js/app-all.js"
    ]);

    /* Move and shake our dependency files around too! */
    mix.copy("resources/assets/css/images/**", "public/build/css/images")
        .copy("resources/assets/css/AdminLTE/**", "public/assets/css")
        .copy("resources/assets/js/AdminLTE/**", "public/assets/js")
        .copy("resources/assets/vendor/bootstrap3/fonts/**", "public/build/fonts")
        .copy("resources/assets/vendor/font-awesome/fonts/**", "public/build/fonts")
        .copy("resources/assets/vendor/ionicons/fonts", "public/build/fonts")
        .copy("resources/assets/images/**", "public/assets/images");
});
