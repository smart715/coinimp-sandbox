import Timeline from '../components/Timeline.vue';
import TimelineItem from '../components/TimelineItem.vue';
import ClickOutside from 'vue-click-outside';
import VueYouTubeEmbed from 'vue-youtube-embed';
import { FontAwesomeIcon, FontAwesomeLayers } from '@fortawesome/vue-fontawesome';
import { addParachutesResponsive } from './util.js';
import TokenSaleProgressCounter from '../components/TokenSaleProgressCounter';
import CountDown from '../components/CountDown.vue';

Vue.use(VueYouTubeEmbed);

let tokenSale = new Vue({
    el: '#token-sale',
    components: {
        Timeline,
        TimelineItem,
        FontAwesomeIcon,
        FontAwesomeLayers,
        TokenSaleProgressCounter,
        CountDown,
    },
    mounted() {
        if (this.$refs.parachuteUrl) {
            addParachutesResponsive(this.$refs.airdropsRow, this.$refs.parachuteUrl.value);
        }
    },
    directives: {
        ClickOutside
    },
    data: {
        showVideo: false,
        player: null,
        playerOptions: {
            rel: 0,
            showinfo: 0
        }
    },
    methods: {
        videoReady: function(event) {
            this.player = event.target;
            this.player.playVideo();
        },
        videoEnded: function() {
            this.showVideo = false;
        },
        exitVideo: function() {
            this.showVideo = false;
            this.player.stopVideo();
        },
    }
});
