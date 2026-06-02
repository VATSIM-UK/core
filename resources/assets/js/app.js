import "@fortawesome/fontawesome-free/js/fontawesome";
import "@fortawesome/fontawesome-free/js/solid";
import "@fortawesome/fontawesome-free/js/brands";
import "./mobile-fixed-nav-height";

$("body").scrollspy({
    target: ".navbar-fixed-top",
});

$(".tooltip_displays").tooltip();

import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";

Alpine.plugin(collapse);

window.Alpine = Alpine;
Alpine.start();
