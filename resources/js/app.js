import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;

import $ from 'jquery';
window.$ = window.jQuery = $;

// Bootstrap JS
import 'bootstrap';

// Bootstrap Table
import 'bootstrap-table/dist/bootstrap-table.min.css';
import 'bootstrap-table/dist/bootstrap-table.min.js';

Alpine.start();