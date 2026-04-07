import "./bootstrap";

import {
    Livewire,
    Alpine,
} from "../../vendor/livewire/livewire/dist/livewire.esm";
import rover from "@sheaf/rover";

window.Alpine = Alpine;

Alpine.plugin(rover);

await import("./components/select");
await import("./components/markdown-renderer");

// ...
import './globals/modals.js';
// ...

Livewire.start();
