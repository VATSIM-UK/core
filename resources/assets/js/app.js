import "@fortawesome/fontawesome-free/js/fontawesome";
import "@fortawesome/fontawesome-free/js/solid";
import "@fortawesome/fontawesome-free/js/brands";

$("body").scrollspy({
    target: ".navbar-fixed-top",
});

$(".tooltip_displays").tooltip();

import { Alpine } from "../../../vendor/livewire/livewire/dist/livewire.esm";
import collapse from "@alpinejs/collapse";

Alpine.plugin(collapse);

Livewire.start();
