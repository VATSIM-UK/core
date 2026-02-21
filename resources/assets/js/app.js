import "@fortawesome/fontawesome-free/js/fontawesome";
import "@fortawesome/fontawesome-free/js/solid";
import "@fortawesome/fontawesome-free/js/brands";

$("body").scrollspy({
    target: "#nav",
});

$(".tooltip_displays").tooltip();

import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";

Alpine.plugin(collapse);

Alpine.start();

Livewire.start();
