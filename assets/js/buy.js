import TokenModal from '../components/TokenModal';
import DepositModal from '../components/DepositModal';
import { addParachutesResponsive } from './util.js';
import axios from 'axios';
import Toasted from 'vue-toasted';
import Modal from '../components/Modal.vue';
import { FontAwesomeIcon, FontAwesomeLayers } from '@fortawesome/vue-fontawesome'
import ReferralModal from '../components/ReferralModal.vue';
import clipboard from 'vue-clipboard2';
import VueTippy from 'vue-tippy';

const usdRateUrl = Routing.generate('api_get_usd_rate');

Vue.use(Toasted, {
    position: 'top-center',
    duration: 5000,
});
Vue.use(clipboard);
Vue.use(VueTippy);

let airdropRow = document.getElementById('airdrop-row');
let parachuteUrl = document.getElementById('parachute-url').value;

if (airdropRow) {
    addParachutesResponsive(airdropRow, parachuteUrl, {
        animationDurationRange: [15, 30],
        animationDelayRange: [1, 3]
    });
}

new Vue({
    el:'#app',
    data: {
        modalShown: true,
        disabled: false,
    },
    methods: {
        cancelBtn: function(){
            this.toggleModal();
            window.location.href = "/";
        },
        acceptBtn: function () {
            if (!this.disabled) {
                this.disabled = true;
                axios.post(this.$refs.acceptUrl.value).then(response => {
                    this.$toasted.success('You agreed to CoinIMP Terms and Conditions.');
                    this.toggleModal();
                }).catch(() => {
                    this.$toasted.error('Failed to request a server.');
                    this.disabled = false;
                });
            }
        },
        toggleModal: function () {
            this.modalShown = !this.modalShown;
        }
    }
});

new Vue({
    el: '#buy',
    data() {
        return {
            balance: {
                XMR: 0,
                BTC: 0,
                ETH: 0,
            },
            availableImp: 0,
            showTokenModal: false,
            showDepositModal: false,
            activeDepositCurrency: null,
            activeDepositCurrencySymbol: null,
            activeDepositAddress: null,
            activeDepositPaymentId: null,
            showSubmitAirdropsCode: false,
            showReferralModal: false,
            currenciesUsdRate: {
                XMR: 0,
                BTC: 0,
                ETH: 0
            },
            currenciesUsdRateRefreshDuration: 20000,
            tooltipMessage: '',
            tooltipOptions: {
                placement: 'top',
                arrow: true,
                trigger: 'click',
                delay: [100, 2000]
            },
            airdropCode: '',
        };
    },
    components: {
        TokenModal,
        DepositModal,
        Modal,
        FontAwesomeIcon,
        FontAwesomeLayers,
        ReferralModal,
    },
    mounted() {
        this.availableImp = this.$refs.availableImp.dataset.impInitAmount || 0;
        this.balance.BTC = this.$refs.btcBalance.dataset.balance || 0;
        this.balance.ETH = this.$refs.ethBalance.dataset.balance || 0;
        this.balance.XMR = this.$refs.xmrBalance.dataset.balance || 0;
    },
    created() {
        this.getCurrenciesUsdRate();
        setInterval(
            this.getCurrenciesUsdRate,
            this.currenciesUsdRateRefreshDuration
        );
    },
    methods: {
        updateData: function(newData) {
            this.availableImp = newData.impAmount;
            this.balance = newData.balances;
        },
        handleTokenModalShow: function(xmrBalance, btcBalance, ethBalance) {
            this.showTokenModal = true;
        },
        handleTokenModalError: function() {
            this.showTokenModal = false;
            this.$toasted.error('An error has ocurred, please try again later');
        },
        openDepositModal: function (currency, currencySymbol, address, paymentId) {
            this.activeDepositCurrency = currency;
            this.activeDepositCurrencySymbol = currencySymbol;
            this.activeDepositAddress = address || 'No ADDRESS available for now';
            this.activeDepositPaymentId = paymentId || null;
            this.showDepositModal = true;
        },
        handleDepositModalError: function() {
            this.showDepositModal = false;
            this.$toasted.error('An error has ocurred, please try again later');
        },
        setShowSubmitAirdropsCode: function () {
            this.showSubmitAirdropsCode = true;
        },
        hideSubmitAirdropsCode: function () {
            this.showSubmitAirdropsCode = false;
        },
        disableSubmit: function (e) {
            if(this.airdropCode.length === 16)
            {
                e.srcElement.disabled = true;
                this.$refs.airdropForm.submit();
            }
        },
        openReferralModal: function() {
            this.showReferralModal = true;
        },
        getCurrenciesUsdRate: function() {
            Object.keys(this.currenciesUsdRate).forEach(currency => {
                axios.get(this.makeExchangeUrl(currency))
                    .then(response => {
                        this.currenciesUsdRate[currency] = response.data.usdRate;
                    })
                    .catch(error => {
                        this.$emit('error');
                    });
            });
        },
        makeExchangeUrl: function(currency) {
            return usdRateUrl + '/' + currency;
        },
        refUrlClicked: function(evt) {
            this.$copyText(evt.target.dataset.refUrl)
            .then(() => {
                this.tooltipMessage = 'Copied!';
            }, () => {
                this.tooltipMessage = 'Please select address and press Ctrl+C to copy';
            })
        }
    },
    filters: {
        toFixed: function(number, precision) {
            return parseFloat('0' + number).toFixed(precision);
        },
    },
});
