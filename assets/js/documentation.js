import { setContentMinHeightDynamically, getWidth } from './util.js';
import Slideout from 'vue-slideout';
import { FontAwesomeIcon, FontAwesomeLayers } from '@fortawesome/vue-fontawesome'

setContentMinHeightDynamically(document.getElementsByClassName('documentation')[0]);

let fixedNavbar = document.querySelector('.navbar');
let fixedFooter = document.querySelector('#page-footer');

let documentation = new Vue({
    el: '#documentation',
    components: {
        Slideout,
        FontAwesomeIcon,
        FontAwesomeLayers
    },
    mounted: function() {
        if (getWidth() < 992)
            this.showToggleSidebarButton = true;
        window.addEventListener('resize', this.handleWidthChange);
    },
    data: {
        showToggleSidebarButton: false,
        version: '2'
    },
    computed: {
        versionValue: function () {
            return parseInt(this.version);
        }
    },
    methods: {
        handleWidthChange: function() {
            if (getWidth() < 992) {
                this.showToggleSidebarButton = true;
            }
            else {
                this.$children[0].slideout.close();
                this.showToggleSidebarButton = false;
            }
        },
        handleCloseOnClick: function() {
            if (getWidth() < 992) {
            	this.$children[0].slideout.close();
       	    }
        },
	disableSlideout: function() {
            this.$children[0].slideout.disableTouch();
        },
        enableSlideout: function() {
            this.$children[0].slideout.enableTouch();
        },
        translate: function (translated) {
            fixedNavbar.style.transform = fixedFooter.style.transform = 'translateX(' +
            translated + 'px)';
        },
        beforeToggle: function (status) {
            fixedNavbar.style.transition = fixedFooter.style.transition = 'transform 300ms ease';
            fixedNavbar.style.transform = fixedFooter.style.transform = 'translateX(' +
            (status == 'open' ? 256 : 0 ) + 'px)';
        },
        toggle: function () {
            fixedNavbar.style.transition = fixedFooter.style.transition = '';
        }
    }
});
