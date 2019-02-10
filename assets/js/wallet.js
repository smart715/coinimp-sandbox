import LoginAlert from '../components/LoginAlert.vue';
import Modal from '../components/Modal.vue';
import InputNumber from '../components/InputNumber.vue';
import BigNumber from 'bignumber.js';
import axios from 'axios';
import number_format from 'locutus/php/strings/number_format';
import Visibility from 'visibilityjs';
import SimpleBar from 'simplebar';
import Toasted from 'vue-toasted';
import VeeValidate from 'vee-validate';
Vue.use(Toasted, {
    position: 'top-center',
    duration: 5000,
});
Vue.use(VeeValidate);
import { asXmr } from './util';

const totalAmount = new BigNumber(document.getElementById('total-wallet-amount').value);
const minimalPayout = new BigNumber(document.getElementById('minimal-payout').value);
const paymentFee = new BigNumber(document.getElementById('payment-fee').value);
const statsRefreshRate = document.getElementById('stats-refresh-rate').value;
const crypto = document.getElementById('crypto-symbol').value;
const pendingRewardUrl = Routing.generate('api_get_pending_reward', {'crypto': crypto});
const loginUrl = Routing.generate('fos_user_security_login');

let pollPendingReward;

let wallet = new Vue({
    el: '#wallet',
    components: {
        LoginAlert,
        Modal,
        InputNumber
    },
    created: function() {
        this.fetchRewardData();
        this.updateRewardData();
    },
    data: {
        totalReward: totalAmount.valueOf(),
        showModal: false,
        showInsufficientBalance: false,
        showLoginAlert: false,
        showUserReward: false,
        usdRate: 0,
        quantitySelectorXMR: '',
        quantitySelectorXMRWithFee: '',
        paymentId: ''
    },
    computed: {
        paymentFeeAtomicUnits: function() {
            return paymentFee.times((new BigNumber(10)).pow(12));
        },
        canPay: function() {
            let total = new BigNumber(this.totalReward);
            return total.isGreaterThanOrEqualTo(minimalPayout);
        },
        displayTotalRewardInUSD: function() {
            return (this.usdRate > 0.00);
        }
    },
    watch: {
        quantitySelectorXMR: function(val) {
            this.quantitySelectorXMRWithFee = (this.quantitySelectorXMR > 0)
                ? (new BigNumber(val)).minus(asXmr(this.paymentFeeAtomicUnits)).toFixed(8)
                : '-';
        }
    },
    methods: {
        inputNumberValidation: function(crypto) {
            return {
                decimal: true,
                required: true,
                min_value: crypto,
                max_value: asXmr(this.totalReward) * 1
            }
        },
        updateRewardData: function() {
            pollPendingReward = Visibility.every(statsRefreshRate * 1000, () => {
                this.fetchRewardData();
            });
        },
        fetchRewardData: function(){
            axios.get(pendingRewardUrl)
                .then(response => {
                    this.usdRate = response.data.usdRate;
                    this.totalReward = response.data.total;
                    this.showUserReward = true;
                    if ((new BigNumber(this.totalReward)).isGreaterThanOrEqualTo(minimalPayout))
                        this.showInsufficientBalance = false;
                })
                .catch(error => {
                    this.handleLogoutError(error);
                });
        },
        confirmPayout: function() {
            if (this.canPay)
            {
                this.quantitySelectorXMR = asXmr(this.totalReward);
                this.showModal = true;
            }
            else
            {
                this.showInsufficientBalance = true;
                Vue.nextTick(function() {
                    let insufficientBalanceAlert = document.getElementById('alert-insufficient-balance');
                    let offsetTop = insufficientBalanceAlert.getBoundingClientRect().top;
                    window.scrollBy(0, offsetTop < 0 ? offsetTop : 0);
                });
            }
        },
        handleLoginAlert: function(isLoggedIn) {
            this.showLoginAlert = false;
            if (isLoggedIn)
            {
                this.updateRewardData();
                this.$toasted.success('Good, you are already logged in');
            }
            else
            {
                window.location.href = loginUrl;
            }
        },
        handleLogoutError: function(error) {
            if (error.response && error.response.status == 404)
            {
                Visibility.stop(pollPendingReward);
                this.showLoginAlert = true;
            }
        },
        submitPayment: function() {
            if (this.errors.any())
                event.preventDefault();
        },
        hideModal: function() {
            this.showModal = false;
            this.paymentId = '';
        },
    },
    filters: {
        numberFormat: function(value, digits) {
            return number_format(value, digits, '.', ' ');
        },
        toXMR: function(value, digits) {
            return asXmr(value, digits);
        },
        toRate: function(value, rate,  digits) {
            return number_format((asXmr(value, digits) * rate), 2,'.',' ');
        }
    }
});
