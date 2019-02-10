<template>
    <div class="row justify-content-center">
        <div class="col-12 text-center">
            <p v-show="tabSelected == 'easyToUse'">Put this code before &lt;/body&gt; tag in your website for background
                mining</p>
            <p v-show="tabSelected == 'AVFriendly'">Download this <a :href="clientPhpUrl" rel="nofollow"
                                                                     target="_blank">script</a> and place it in your
                site files. In case you don't have writable /tmp directory on your server, you will have to create
                directory "coinimp-cache" and set proper permissions (777) to make it writable by PHP. Then put the code
                below before &lt;/body&gt; tag in your website for background mining
            </p>
            <p>
                To enable separate mining for site users, check our <a :href="documentation" target="_blank">documentation</a>.
            </p>
        </div>
        <div class="col-12 mb-2 text-center">
            <p>Click on slider to limit maximum CPU usage <strong v-cloak>{{ sliderValue }}%</strong></p>
            <vue-slider ref="slider" v-model="sliderValue" v-bind="sliderOptions"></vue-slider>
        </div>
        <div class="col-12 mb-1">
            <span class="info"><font-awesome-icon icon="info-circle"/> Your site key placed inside <strong>Client.Anonymous({site-key})</strong>.</span>
        </div>
        <div class="col-12 mb-1" :id="'code-text-' + id">
            <script-code
                :tab-selected="tabSelected"
                :site-key="siteKey"
                :miner-url="minerUrl"
                :php-script="phpScript"
                :js-script="jsScript"
                :throttle="throttle"
                :anti-block-checked="antiBlockChecked"
                :crypto="crypto"
                :notification-checked="notificationChecked"
                :ads-checked="adsOptionChecked"
                :notification="notification"
            >
            </script-code>
        </div>
        <div class="col-12 text-center">
            <copy-button class="mb-4" :data-clipboard-target="'#code-text-' + id"></copy-button>
            <div class="form-group text-left">
                <div v-show="adsOption">
                    <span class="info"><font-awesome-icon icon="info-circle"/> If this option is ticked, your fee will be optimized but your users will see our ads maximum once per month. Otherwise your fee will increase. </span>
                    <label>
                        <input type="checkbox" id="adsCheckbox" v-model="adsChecked">
                        Show our advertisement on your site.
                    </label>
                </div>
                <div>
                    <label>
                        <input type="checkbox" id="antiBlockCheckbox" v-model="antiBlockChecked">
                        Block showing your site content until mining is allowed.
                    </label>
                </div>
                <div>
                    <label>
                        <input type="checkbox" id="checkbox" v-model="notificationChecked">
                        Add a notification on your site about ongoing mining.
                    </label>
                </div>
            </div>
            <miner-notification-options class="collapsible m-2"
                                        :class="{ 'collapsed': !notificationChecked }"
                                        @notification-changed="onNotificationChange">
            </miner-notification-options>
        </div>
    </div>
</template>

<script>
    import Routing from "../js/routing";
    import ScriptCode from './ScriptCode.vue';
    import CopyButton from './CopyButton.vue';
    import vueSlider from 'vue-slider-component';
    import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'
    import MinerNotificationOptions from "./MinerNotificationOptions";

    export default {
        name: 'CodeBody',
        components: {
            MinerNotificationOptions,
            vueSlider,
            ScriptCode,
            CopyButton,
            FontAwesomeIcon
        },
        props: {
            documentation: String,
            tabSelected: String,
            siteKey: String,
            scriptsUrl: String,
            minerUrl: String,
            phpScript: String,
            jsScript: String,
            adsOption: Boolean,
            crypto: {
                type: String,
                required: true
            },
        },
        mounted() {
            this.id = this._uid;
        },
        data() {
            return {
                id: null,
                clientPhpUrl: this.scriptsUrl + '/' + this.phpScript + '.php',
                sliderValue: 100,
                antiBlockChecked: false,
                notificationChecked: true,
                adsChecked: true,
                notification: [],
                sliderOptions: {
                    height: 16,
                    dotSize: 24,
                    min: 10,
                    max: 100,
                    interval: 10,
                    tooltip: false
                }
            }
        },
        computed: {
            throttle: function () {
                return (1 - this.sliderValue / 100).toPrecision(1);
            },
            documentation: function () {
                return Routing.generate('documentation', { 'section': 'reference' }) + '#Client.User';
            },
            adsOptionChecked: function () {
                return this.adsOption && this.adsChecked;
            },
        },
        methods: {
            onNotificationChange(notification) {
                this.notification = notification;
            },
        }
    }
</script>
