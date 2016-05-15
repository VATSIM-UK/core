var elixir = require('laravel-elixir');

elixir(function (mix) {
    //mix.sass('app.scss', './public/css/app.css');
    //mix.sass('app.scss', './public/css/adm.css');

    /* Concatenate all CSS */
    mix.styles([
        /*"jquery-ui.cupertino.1.11.4.css",
        "design.css",
        "font-awesome.4.5.0.min.css",
        "../vendor/bootstrap-slider/css/bootstrap-slider.css",
        "../vendor/bootstrap-summernote/summernote.css",*/
        "../vendor/bootstrap3/css/bootstrap.css",
        "../vendor/bootstrap3/css/bootstrap-theme.css",
        "../vendor/ionicons/ionicons.2.0.1.css",
        "../vendor/font-awesome/font-awesome.4.5.0.css",
        "app.css",
    ], './public/build/css/app-all.css');

    /* Concatenate all JS */
    mix.scripts([
            /*"jquery-1.11.3.js",
            "../vendor/js-cookie/src/js.cookie.js",
            "../vendor/jquery-ui/jquery-ui.js",
            "../vendor/bootstrap-summernote/summernote.js",
            "../vendor/bootstrap-switch/bootstrap-switch.min.js",*/
            "jquery-2.1.4.js",
            "../vendor/bootstrap3/js/bootstrap.js",
            "cbpAnimatedHeader.js",
            "app.js",
        ], "./public/build/js/app-all.js"
    );

    /* Cache Busting */
    mix.version([
        "public/build/css/app-all.css",
        "public/build/js/app-all.js"
    ]);

    /* Move and shake our dependency files around too! */
    mix.copy("resources/assets/css/images", "public/build/css/images");
    mix.copy("resources/assets/vendor/bootstrap3/fonts", "public/build/fonts");
    mix.copy("resources/assets/vendor/font-awesome/fonts", "public/build/fonts");
    mix.copy("resources/assets/vendor/ionicons/fonts", "public/build/fonts");
    mix.copy("resources/assets/images", "public/assets/images");

    /**
     * To mix multiple we can do:
     * mix.sass(['app.scss', 'front.scss']);
     */
});
