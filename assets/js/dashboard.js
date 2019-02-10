import CodeCard from '../components/CodeCard.vue';
import WordpressCard from '../components/WordpressCard.vue';
import CopyButton from '../components/CopyButton.vue';
import CodeCalculator from '../components/CodeCalculator.vue';
import SiteTable from '../components/SiteTable.vue';
import FormModal from '../components/FormModal.vue';
import LoginAlert from '../components/LoginAlert.vue';
import ApiKeysModal from '../components/ApiKeysModal.vue';
import Miner from '../components/Miner.vue';
import ConfirmModal from '../components/ConfirmModal';
import { asXmr, sortRecords } from './util.js';
import number_format from 'locutus/php/strings/number_format';
import SiteUserTable from '../components/SiteUserTable';
import axios from 'axios';
import _ from 'lodash';
import Visibility from 'visibilityjs';
import Toasted from 'vue-toasted';
import { FontAwesomeIcon, FontAwesomeLayers } from '@fortawesome/vue-fontawesome';

Vue.use(Toasted, {
    position: 'top-center',
    duration: 5000,
});

const apiKeyPublic = document.getElementById('initial-api-key-public').value;
const totalHashRate = document.getElementById('initial-total-hash-rate').value;
const totalHashes = document.getElementById('initial-total-hashes').value;
const pendingBalance = document.getElementById('initial-pending-balance').value;
const referralReward = document.getElementById('initial-referral-reward').value;
const referralsCount = document.getElementById('initial-referrals-count').value;
const statsRefreshRate = document.getElementById('stats-refresh-rate').value;
const crypto = document.getElementById('crypto').value;

const addSiteUrl = Routing.generate('site_add', {'crypto': crypto});
const addSiteUrlE = Routing.generate('site_add', {'crypto': crypto, 'error': 'true'});
const loginUrl = Routing.generate('fos_user_security_login');
const userStatsUrl = Routing.generate('api_profile_get_stats', {'crypto': crypto});
const poolStatsUrl = Routing.generate('api_get_pool_stats', {'crypto': crypto});
const apiKeysUrl = Routing.generate('api_get_api_keys');

let polls = {};
let pollIntervals = [];

function doPollOnInterval(uri, interval, pollId, onSuccess, onFail)
{
    polls[pollId] = false;
    pollIntervals.push(Visibility.every(interval, function() {
        if (polls[pollId])
            return;

        polls[pollId] = true;
        axios.get(uri).then(onSuccess).catch(onFail)
            .then(function() {
                polls[pollId] = false;
            });
    }));
}

function stopPolling()
{
    pollIntervals.forEach(function(pollInterval) {
        Visibility.stop(pollInterval);
    });
    pollIntervals = [];
}

let dashboard = new Vue({
    el: '#dashboard',
    components: {
        CopyButton,
        CodeCard,
        CodeCalculator,
        WordpressCard,
        SiteTable,
        SiteUserTable,
        FormModal,
        LoginAlert,
        ApiKeysModal,
        ConfirmModal,
        Miner,
        FontAwesomeIcon,
        FontAwesomeLayers
    },
    created: function() {
        this.startNormalPolling();
    },
    data: {
        intervalErrors: {},
        crypto: crypto,
        paginate: ['visibleSites'],
        showConfirmModal: false,
        showCodeCard: false,
        showCodeCalculator: false,
        showWordpressCard: false,
        showAddSiteModal: false,
        showLoginAlert: false,
        hasSitesLoaded: false,
        showApiKeysModal: false,
        apiKeysLoaded: false,
        apiKeys: {
            public: apiKeyPublic
        },
        userStats: {
            referralReward: referralReward,
            referralsCount: referralsCount,
            total: {
                pendingBalance: pendingBalance,
                totalHashRate: totalHashRate,
                totalHashes: totalHashes
            }
        },
        poolStats: {
            payoutPerMillion: 0
        },
        isInAliveMode: true,
        showEmergencyAlert: false,
        showMoneroAlert: false,
        confirmModalAction: () => {},
        localMinerTotalHashes: 0,
        siteKey: '',
        siteOrder: '',
        siteIsAlpha: false
    },
    computed: {
        poolError: function() {
            return !!this.intervalErrors.pool;
        },
        showSites: function() {
            return this.userStats.sites && this.userStats.sites.some(site => site.isVisible);
        },
        showPoolStats: function() {
            return this.poolStats && this.poolStats.isAlive;
        },
        visibleSites: function() {
            return sortRecords(_.filter(this.userStats.sites, site => site.isVisible), this.siteKey, this.siteOrder, this.siteIsAlpha);
        },
        isApiKeyExists: function () {
            return this.apiKeys.public !== '';
        },
        visibleUsers: function () {
            return _.filter(_.flatten(_.toArray(_.mapValues(this.visibleSites, site => _.map(site.users, user => {
                user.site = site.name;
                return user;
            })))), user => user.name !== 'anonymous');
        }
    },
    watch: {
        poolStats: function(newPoolStats, oldPoolStats) {
            if (newPoolStats.isAlive !== true)
            {
                if (this.isInAliveMode)
                    this.enterEmergencyMode();
                return;
            }

            if (!this.isInAliveMode)
                this.recoverFromEmergency();
        }
    },
    methods: {
        toggleCodeCard: function() {
            this.showCodeCard = !this.showCodeCard;
            this.showCodeCalculator = false;
            this.showWordpressCard = false;
        },
        toggleCodeCalculator: function() {
            this.showCodeCalculator = !this.showCodeCalculator;
            this.showCodeCard = false;
            this.showWordpressCard = false;
        },
        toggleWordpressCard: function() {
            this.showWordpressCard = !this.showWordpressCard;
            this.showCodeCard = false;
            this.showCodeCalculator = false;
        },
        pollOnInterval: function(url, interval, name, onSuccess) {
            doPollOnInterval(url, interval, name, response => {
                if(this.intervalErrors[name])
                    Vue.delete(this.intervalErrors, name);
                onSuccess(response);
            }, error => {
                if(!this.intervalErrors[name])
                    Vue.set(this.intervalErrors, name, true);
                this.handleLogoutError(error);
            });
        },
        startNormalPolling: function() {
            this.getUserStats();
            this.getPoolStats();

            this.pollOnInterval(userStatsUrl, statsRefreshRate * 1000, 'site', response => {
                this.userStats = _.assign({}, response.data);
                this.localMinerTotalHashes = response.data.localMinerTotalHashes;
            });
            this.pollOnInterval(poolStatsUrl, 61000, 'pool', response => {
                this.poolStats = _.assign({}, response.data);
            });
        },
        handleLogoutError: function(error) {
            if (error.response && error.response.status == 404)
            {
                this.showLoginAlert = true;
                stopPolling();
            }
        },
        enterEmergencyMode: function() {
            this.isInAliveMode = false;
            this.showEmergencyAlert = true;
            stopPolling();
            this.pollOnInterval(poolStatsUrl, 10000, 'pool', response => {
                this.poolStats = _.assign({}, response.data);
            });
        },
        recoverFromEmergency: function() {
            this.isInAliveMode = true;
            this.showEmergencyAlert = false;
            stopPolling();
            this.startNormalPolling();
        },
        getUserStats: function() {
            axios.get(userStatsUrl)
                .then(response => {
                    this.userStats = _.assign({}, response.data);
		    this.hasSitesLoaded = true;
                })
                .catch(error => {
                    if (error.response && error.response.status == 404)
                        this.showLoginAlert = true;
                });
        },
        getPoolStats: function() {
            axios.get(poolStatsUrl)
                .then(response => { this.poolStats = _.assign({}, response.data); })
                .catch(error => { this.handleLogoutError(error); });
        },
        loadAddSiteModal: function() {
            this.showAddSiteModal = true;
            Vue.nextTick(() => this.$refs.formAddSite.loadForm(addSiteUrl));
        },
        handleFormSubmit: function(response, target) {
            var fields = document.querySelectorAll('[submit-clearable]');
            _.forEach(fields, function (el) {
                el.value = '';
            });
            if (response.data.status === 'success')
            {
                if (target.hasAttribute('data-submit-and-close')) {
                    this.showAddSiteModal = false; 
                } else {
                    this.showAddSiteModal = true;
                    Vue.nextTick(() => this.$refs.formAddSite.loadForm(addSiteUrl));
                }
                this.$toasted.success(response.data.message);
                this.addSite(response.data.site);
            }
            else
            {
                this.$toasted.error(response.data.message);
            }
        },
        handleFormModalError: function() {
            this.showAddSiteModal = true;
            Vue.nextTick(() => this.$refs.formAddSite.loadForm(addSiteUrlE));
        },
	handleFormButton: function(target) {
            if(target.hasAttribute('close'))
		this.showAddSiteModal = false;
        },
        addSite: function(site) {
            this.userStats.sites = _.concat(this.userStats.sites, site);
        },
        editSite: function(site) {
            let index = _.findIndex(this.userStats.sites, ['words', site.words]);
            this.userStats.sites[index] = _.assign(this.userStats.sites[index], site);
        },
        deleteSite: function(site) {
            let index = _.findIndex(this.userStats.sites, ['words', site.words]);
            Vue.delete(this.userStats.sites, index);
        },
        handleLoginAlert: function(isLoggedIn) {
            this.showLoginAlert = false;
            if (isLoggedIn)
            {
                this.$toasted.success('Good, you are already logged in');
                this.startNormalPolling();
            }
            else
            {
                window.location.href = loginUrl;
            }
        },
        openApiKeysModal: function() {
            this.showApiKeysModal = true;
            axios.get(apiKeysUrl)
                .then(response => {
                    this.apiKeys = JSON.parse(response.data.keys);
                    this.apiKeysLoaded = true;
                })
                .catch(error => {
                    this.showApiKeysModal = false;
                    this.$toasted.error('An error has ocurred, please try again later');
                });
        },
        closeApiKeysModal: function() {
            this.showApiKeysModal = false;
            this.apiKeysLoaded = false;
        },
        openConfirmModal: function (action) {
            this.confirmModalAction = action;
            this.showConfirmModal = true;
        },
        sortSiteBy: function(newSortKey, newSortOrder, isAlpha) {
            this.siteKey =  newSortKey;
            this.siteOrder = newSortOrder;
            this.siteIsAlpha = isAlpha;
      	    this.userStats.sites = sortRecords(_.filter(this.userStats.sites, site => site.isVisible), newSortKey, newSortOrder, isAlpha);
    }
    },
    filters: {
        numberFormat: function(value, digits) {
            return number_format(value, digits, '.', ' ');
        },
        toFixed: function(value, precision) {
            return value.toFixed(precision);
        },
        noZeroPadding: function(value) {
            return parseFloat(value);
        },
        toXMR: function(value, digits) {
            return asXmr(value, digits);
        },
        upper: function(value) {
            return value.toUpperCase();
        }
    }
});
