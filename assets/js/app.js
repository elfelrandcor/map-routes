/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
// import '../css/app.scss';
require('../css/app.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';

import Vue from 'vue'
import Hello from './components/Hello'

// eslint-disable-next-line no-new
new Vue({
    el: '#app',
    components: { Hello },
    template: '',
})
