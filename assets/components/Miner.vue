<template>
    <div class="row">
        <div class="col-12">
            <span class="info"><font-awesome-icon icon="info-circle" /> Hash rate below is not average but current rate.</span>
        </div>
        <div class="col-12">
            <div class="card">
                <slot name="card-header"></slot>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 col-lg-3">
                            Hashes/sec
                            <div class="miner-field bg-empty">
                                <span id="miner-hash-rate-value">
                                    <span v-if="poolError"> - </span>
                                    <span v-else >{{ hashRate|numberFormat(2) }}</span>
                                </span>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            Local Hashes
                            <div class="miner-field bg-empty">
                                <span id="miner-total-hashes-value">
                                    <span v-if="poolError" > - </span>
                                    <span v-else >{{ visibleTotalHashes|numberFormat(0) }}</span>
                                </span>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            Threads
                            <miner-counter
                                name="threads"
                                :initial="threads"
                                :min="1"
                                :max="24"
                                :multiplier="1"
                                @change-value="changeThreads"
                            ></miner-counter>
                        </div>
                        <div class="col-6 col-lg-3">
                            Speed
                            <miner-counter
                                name="speed"
                                :initial="speed"
                                :min="10"
                                :max="100"
                                :multiplier="10"
                                @change-value="changeSpeed"
                            >
                                <span slot="value-postfix">%</span>
                            </miner-counter>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 text-right mt-2">
            <button class="btn btn-primary" id="start-miner-btn" @click="handleButtonClick">
                <font-awesome-icon
                    icon="circle-notch"
                    spin class="loading-spinner"
                    fixed-width
                    v-if="showLoadingIcon"/>
                {{ buttonText }}
            </button>
        </div>
    </div>
</template>

<script>
import MinerCounter from './MinerCounter.vue';
import number_format from 'locutus/php/strings/number_format';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

var miner;
var minerRefreshInterval = 300;
var minerIntervalId = 0;

export default {
    name: 'Miner',
    components: {
        MinerCounter,
        FontAwesomeIcon
    },
    props: {
        poolError: Boolean,
        minerUrl: String,
        minerKey: String,
        lastTotalHashes: Number,
        newTotalHashes: Number,
        crypto: String
    },
    data () {
        return {
            loadedScript: false,
            minerRunning: false,
            hashRate: 0,
            totalHashes: this.lastTotalHashes,
            lastUpdatedTotalHashes: this.lastTotalHashes,
            threads: navigator.hardwareConcurrency || 4,
            speed: 100,
            minerTried: false,
            lastMinerValue: 0,
        }
    },
    methods: {
        handleButtonClick: function() {
            if (this.minerRunning == false)
                this.startMining();
            else
                this.stopMining();
        },
        startMining: function() {
            if (!this.loadedScript)
            {
                let minerScript = document.createElement('script');
                minerScript.setAttribute('src', this.minerUrl);
                minerScript.onload = () => {
                    this.loadedScript = true;
                    this.doStartMining();
                };
                document.head.appendChild(minerScript);
                return;
            }
            this.doStartMining();
        },
        doStartMining: function() {
            if (typeof miner === 'undefined')
                miner = new Client.Anonymous(this.minerKey, this.minerOptions);

            miner.setNumThreads(this.threads);
            miner.setThrottle(1 - this.speed / 100.0);
            miner.start();

            minerIntervalId = setInterval(() => {
                this.hashRate = miner.getHashesPerSecond();
                this.totalHashes = this.lastUpdatedTotalHashes
                                 + (miner.getTotalHashes() - this.lastMinerValue);
            }, minerRefreshInterval);

            this.minerRunning = true;
            this.minerTried = true;
        },
        stopMining: function() {
            miner.stop();
            clearInterval(minerIntervalId);
            this.minerRunning = false;
            this.lastMinerValue = miner.getTotalHashes();
        },
        changeThreads: function(value) {
            this.threads = value;
            if (typeof miner !== 'undefined')
                miner.setNumThreads(this.threads);
        },
        changeSpeed: function(value) {
            this.speed = value;
            if (typeof miner !== 'undefined')
                miner.setThrottle(1 - this.speed / 100.0);
        }
    },
    computed: {
        buttonText: function() {
            if(this.minerRunning){
                return (this.hashRate > 0) ? 'Stop mining' : 'Loading mining';
            }
            return 'Start mining';
        },
        showLoadingIcon: function() {
            return (this.buttonText === 'Loading mining');
        },
        minerOptions: function() {
            let cryptoOptions = {
                'xmr': {},
                'web': { c: 'w' }
            };
            return cryptoOptions[this.crypto];
        },
        visibleTotalHashes: function() {
            if (!this.minerTried || this.minerRunning) {
                return this.totalHashes;
            }
            this.lastUpdatedTotalHashes = this.newTotalHashes;
            this.totalHashes = this.newTotalHashes;
            return this.newTotalHashes;
        },
    },
    filters: {
        numberFormat: function(value, digits) {
            return number_format(value, digits, '.', ' ');
        }
    },
}
</script>

