import 'babel-polyfill';
import '../scss/styles.scss';
import { setContentMinHeightDynamically } from './util.js';
import { library } from '@fortawesome/fontawesome-svg-core'
import { fas } from '@fortawesome/free-solid-svg-icons'
import { far } from '@fortawesome/free-regular-svg-icons'
import { fab } from '@fortawesome/free-brands-svg-icons'
library.add(fas, far, fab);
import { FontAwesomeIcon, FontAwesomeLayers } from '@fortawesome/vue-fontawesome'

window.Vue = require('vue');

Vue.options.delimiters = ['{[', ']}'];
import ClickOutside from 'vue-click-outside';

setContentMinHeightDynamically(document.getElementById('content-wrapper'));

let undraggable = document.querySelectorAll('.undraggable');
Array.from(undraggable).forEach(function(el) {
    el.setAttribute('ondragstart', 'return false;');
});

let navbar = new Vue({
    el: '#navbar',
    directives: {
        ClickOutside
    },
    data: {
        showNavbarMenu: false,
        showProfileMenu: false
    },
    methods: {
        toggleNavbarMenu: function() {
            this.showNavbarMenu = !this.showNavbarMenu;
        },
        toggleProfileMenu: function() {
            this.showProfileMenu = !this.showProfileMenu;
        },
        hideProfileMenu: function() {
            this.showProfileMenu = false;
        }
    }
});

let footer = new Vue({
    el: '#page-footer',
    components: {
        FontAwesomeIcon,
	FontAwesomeLayers 
    }
});

let icons = new Vue({
    el: '#icons',
    components: {
        FontAwesomeIcon,
        FontAwesomeLayers
    }
});
