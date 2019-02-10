<template>
    <div>
        <div class="row pb-2 pb-lg-4 bonus-packages">
            <div class="col-6 col-md-3 px-1 text-center"
                 v-for='(bonusPackage, index) in bonusPackages'
                 v-bind:key="index"
            >
                <div class="package d-inline-block"
                     :class="{active: selectedPackage.amount === bonusPackage.amount}"
                     @mouseover="showPopover(index)"
                     @mouseleave="hidePopovers"
                     @click="setBonusValue(bonusPackage)"
                >
                    {{ bonusPackage.amount | numberFormat(0) }}<span class="text-success">+{{ bonusPackage.bonusValue|numberFormat(0) }} </span>
                    <div class="popover fade show bs-popover-bottom bg-success border-0"
                         x-placement="bottom"
                         v-if="popoverIndex === index"
                    >
                        <div class="arrow"></div>
                        <div class="popover-body text-white">
                            Pick this one and get a <b>{{ bonusPackage.bonusValue | numberFormat(0) }} IMP</b> bonus!
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-0 px-sm-5">
            <div class="my-2 currency-container pl-5">
                <input class="form-control form-control-md"
                       :class="{'text-danger': notEnoughBalance}"
                       type="text"
                       placeholder="INPUT AMOUNT AND SELECT CURRENCY TO DEPOSIT"
                       @keyup="exchange($event, input.types.currency)"
                       @keypress="onKeyPress($event, input.types.currency)"
                       v-model="currency.value"
                       onpaste="return false;"
                >
                <div class="popover lg bs-popover-top fade show bs-popover-top bg-danger border-0 ml-3"
                     x-placement="bottom"
                     v-if="input.error === 'notEnoughBalance'"
                >
                    <div class="arrow"></div>
                    <div class="popover-body text-white">
                        Not enough {{ currency.selected }} in your wallet
                    </div>
                </div>
                <div class="popover fade show bs-popover-top bg-danger border-0 ml-3"
                     x-placement="bottom"
                     v-if="input.error === input.types.currency"
                >
                    <div class="arrow"></div>
                    <div class="popover-body text-white">
                        Only numbers allowed with 8 digits after the decimal point!
                    </div>
                </div>
                <button class="btn btn-primary all-currency-amount px-3" @click="allCurrencyAmount">All</button>
                <div class="btn-group currency-select-fake">
                    <button class="btn btn-primary dropdown-toggle"
                            type="button"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            :aria-expanded="showSelectCurrencyMenu"
                            @click="toggleSelectCurrencyMenu"
                            v-click-outside="hideSelectCurrencyMenu"
                    >
                        {{ selectedCurrency }}
                    </button>
                    <div class="dropdown-menu bg-primary rounded-0 border-0 m-0 w-100"
                         :class="{ 'show': showSelectCurrencyMenu }"
                    >
                        <a class="dropdown-item"
                           href="#"
                           v-for="(price, currency) in currenciesInfo"
                           v-bind:key="currency"
                           @click.prevent="selectCurrency(currency)"
                        >
                            {{currency}}
                        </a>
                    </div>
                </div>
            </div>
            <div class="imp-container">
                <input class="form-control form-control-md"
                       :class="{'text-danger': outOfRange}"
                       type="text"
                       @focus="impInputFocus"
                       @keyup="exchange($event, input.types.imp)"
                       @keypress="onKeyPress($event, input.types.imp)"
                       @blur="impInputBlur"
                       v-model="impValue"
                       onpaste="return false;"
                >
                <div class="popover lg bs-popover-bottom fade show bs-popover-top bg-danger border-0"
                     x-placement="bottom"
                     v-if="outOfRange"
                >
                    <div v-if="input.error === 'maxImpAmount' || input.error === 'minImpAmount'"class="arrow"></div>
                    <div v-if="input.error === 'maxImpAmount'" class="popover-body text-white">
                        Maximum IMP you can buy is {{ imp.maxAmount | toFixed(0) }}
                    </div>
                    <div v-if="input.error === 'minImpAmount'" class="popover-body text-white">
                        Minimum IMP you can buy is {{ imp.minAmount | toFixed(0) }}
                    </div>
                </div>
                <div class="popover fade show bs-popover-top bg-danger border-0"
                     x-placement="bottom"
                     v-if="input.error === input.types.imp"
                >
                    <div class="arrow"></div>
                    <div class="popover-body text-white">
                        Only integer numbers allowed!
                    </div>
                </div>
                <div class="imp-label">IMP</div>
            </div>
        </div>
        <div class="text-center">
            <p class="pt-2">
                You're obtaining {{ obtainingImp | numberFormat(0) }} IMP
                <span class="text-success">
                    + {{ imp.bonus }} IMP
                </span>
            </p>
            <p>
                Remember to deposit this amount or greater in order to submit your purchase
            </p>
            <p>
                <b>1 IMP = {{ imp.price }}USD</b> We calculate the amount of IMP you’re buying through the cryptocompare API.
                We fetch new data every {{ refreshMiliseconds / 1000 }} seconds, so you get real values in real time.
            </p>
        </div>
        <div class="mt-2 text-center">
            <div class="alert alert-danger" role="alert" v-if="submitError">{{ submitError }}</div>
            <button class="btn"
                    :class="{'btn-secondary': !canSubmit, 'btn-primary': canSubmit}"
                    :disabled="!canSubmit"
                    @click="handleSubmit"
                    type="submit"
            >
                Submit
            </button>
            <button class="btn ml-2"
                    :class="{'btn-secondary': canSubmit, 'btn-primary': !canSubmit}"
                    @click="closeModal()">
                Close
            </button>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import qs from 'qs';
import number_format from 'locutus/php/strings/number_format';
import ClickOutside from 'vue-click-outside';
import Decimal from 'decimal.js';

export default {
    name: 'Token',
    directives: {
        ClickOutside
    },
    props: {
        currenciesUsdRate: Object,
        refreshMiliseconds: Number,
        balance: Object,
        impPriceInUsd: Number,
        availableImp: Number,
        depositUrl: String,
        bonusPackages: Array,
        impMinAmount: Number,
        impMaxAmount: Number,
        decimals: Number
    },
    data() {
        return {
            showSelectCurrencyMenu: false,
            currenciesInfo: this.currenciesUsdRate,
            balances: {
                XMR: this.formatDecimal(this.balance.XMR),
                BTC: this.formatDecimal(this.balance.BTC),
                ETH: this.formatDecimal(this.balance.ETH),
            },
            input: {
                types: {
                    currency: 'currency',
                    imp: 'imp',
                },
                error: false,
            },
            imp: {
                price: this.impPriceInUsd,
                minAmount: this.formatDecimal(this.impMinAmount),
                maxAmount: this.formatDecimal(this.impMaxAmount),
                value: null,
                nullValue: '0000',
                bonus: 0,
                formatedValue : null,
            },
            currency: {
                value: '',
                selected: '',
                nullValue: 'Select',
            },
            popoverIndex: null,
            lastInput: null,
            showAllCurrencyAmount: false,
            submitError: null,
        }
    },
    computed: {
        selectedPackage: function() {
            return this.getBonusPackageOfImp(this.imp.value);
        },
        lessThanMinAmount: function() {
            const impValue = this.formatDecimal(this.imp.value);
            return impValue.greaterThan(0) && impValue.lessThan(this.formatDecimal(this.imp.minAmount));
        },
        moreThanMaxAmount: function() {
            return this.formatDecimal(this.imp.value).greaterThan(this.formatDecimal(this.imp.maxAmount));
        },
        outOfRange: function() {
            return this.lessThanMinAmount || this.moreThanMaxAmount;
        },
        impValue: {
            get() {
                return this.imp.formatedValue === null ? this.imp.nullValue : this.imp.formatedValue;
            },
            set(value) {
                this.imp.formatedValue = value;
            }
        },
        obtainingImp: function() {
            return this.impValue || this.imp.nullValue;
        },
        notEnoughBalance: function() {
            return this.currency.selected
                 ? this.formatDecimal(this.currency.value).greaterThan(this.formatDecimal(this.balances[this.currency.selected]))
                 : false;
        },
        canSubmit: function() {
            return this.formatDecimal(this.currency.value)
                && !this.notEnoughBalance
                && this.currency.selected
                && !this.input.error
                && !this.inputHasError;
        },
        initCurrency: function() {
            let initCurrency = '';
            let initCurrencyPrice = 0;
            Object.keys(this.balances).forEach(currency => {
                let currencyPrice = this.formatDecimal(this.balances[currency]).times(this.formatDecimal(this.currenciesInfo[currency]));
                if (currencyPrice.greaterThan(this.formatDecimal(initCurrencyPrice))) {
                    initCurrencyPrice = currencyPrice;
                    initCurrency = currency;
                }
            });
            return initCurrency;
        },
        selectedCurrency: function() {
            if (this.currency.selected) {
                return this.currency.selected;
            }
            if (this.initCurrency) {
                this.currency.selected = this.initCurrency;
                return this.initCurrency;
            }
            return this.currency.nullValue;
        },
        inputHasError: function() {
            return Object.values(this.input.types).indexOf(this.input.error) > -1;
        }
    },
    methods: {
        showPopover: function(index) {
            this.popoverIndex = index;
        },
        hidePopovers: function() {
            this.popoverIndex = null;
        },
        toggleSelectCurrencyMenu: function() {
            this.showSelectCurrencyMenu = !this.showSelectCurrencyMenu;
        },
        hideSelectCurrencyMenu: function() {
            this.showSelectCurrencyMenu = false;
        },
        selectCurrency: function(currency) {
            this.currency.selected = currency;
            this.showAllCurrencyAmount ? this.allCurrencyAmount() : this.doExchange(this.lastInput);
        },
        impInputFocus: function(evt) {
            if (this.formatDecimal(evt.target.value).equals(0)) {
                this.imp.formatedValue = '';
            }
        },
        impInputBlur: function() {
            if (this.formatDecimal(this.imp.formatedValue).equals(0)) {
                this.imp.formatedValue = '';
            }
        },
        onKeyPress: function(evt, inputType) {
            this.showAllCurrencyAmount = false;

            if (!this.charIsValid(evt, inputType) || this.hasDecimalPlace(evt.target, this.decimals)) {
                this.input.error = inputType;
                evt.preventDefault();
            } else {
                this.input.error = this.getErrorType();
            }
        },
        getSelectionStart: function (el) {
            if (!el.createTextRange) {
                return el.selectionStart;
            }
            let r = document.selection.createRange().duplicate();
            r.moveEnd('character', el.value.length);

            return r.text ?
                el.value.lastIndexOf(r.text) :
                el.value.length;
        },
        charIsValid: function(evt, inputType) {
            const charKey = evt.key;
            const triedNumbers = inputType === this.input.types.currency
                        ? /[0-9]|[.]/.test(charKey)
                        : /[0-9]/.test(charKey);
            const triedControls = this.validateKeyCode(evt);
            const inputCurrentValue = inputType === this.input.types.currency
                        ? this.currency.value
                        : this.imp.value;
            const dubleDot = /\./.test(inputCurrentValue) && charKey === '.';
            return (triedNumbers || triedControls) && !dubleDot;
        },
        hasDecimalPlace: function(element, place) {
            var value = element.value;
            var pointIndex = value.indexOf('.');
            var caratPos = this.getSelectionStart(element);
            return  caratPos > pointIndex && pointIndex >= 0 && pointIndex < value.length - place;
        },
        exchange: function(evt, inputType) {
            // Chrome hack!
            if (this.validateKeyCode(evt) && this.inputHasError) {
                this.input.error = this.getErrorType();
            }
            this.doExchange(inputType);
        },
        doExchange: function(inputType) {
            this.submitError = null;
            this.lastInput = inputType;

            if (inputType === this.input.types.currency) {
                this.calculateImp();
            }
            if (inputType === this.input.types.imp) {
                this.calculateCurrency();
            }
        },
        validateKeyCode: function(evt) {
            const charCode = evt.which || evt.keyCode || 0;
            const allowedContorlKeyChars = [8, 9, 27, 35, 36, 37, 38, 39, 40, 46, 144];
            return allowedContorlKeyChars.indexOf(charCode) > -1
                && !/[#\$%&\(\.']/.test(evt.key);
        },
        calculateImp: function() {
            if (this.formatDecimal(this.currency.value).equals(0) || !this.currency.selected) {
                this.imp.value = null;
                this.imp.formatedValue = null;
                this.imp.bonus = 0;
                return;
            }
            this.imp.value = this.getImpValue(this.currency.value).toFixed(this.decimals);
            this.imp.formatedValue = this.getImpValue(this.currency.value).toFixed(0);
            this.imp.bonus = this.getBonusValue(this.imp.value);
        },
        getImpValue: function(currencyValue) {
            return this.formatDecimal(currencyValue).
                times(this.formatDecimal(this.currenciesInfo[this.currency.selected])).
                div(this.formatDecimal(this.imp.price));
        },
        calculateCurrency: function() {
            this.imp.value = this.imp.formatedValue;
            this.imp.bonus = this.getBonusValue(this.imp.value);
            if (this.formatDecimal(this.imp.value).equals(0) || !this.currency.selected) {
                this.currency.value = '';
                return;
            }
            this.currency.value = this.formatDecimal(this.imp.value).
                times(this.formatDecimal(this.imp.price)).
                div(this.formatDecimal(this.currenciesInfo[this.currency.selected])).toFixed(this.decimals);
        },
        getBonusValue: function(impValue) {
            let impPackage = this.getBonusPackageOfImp(impValue);
            return impPackage.bonusValue || 0;
        },
        getBonusPackageOfImp: function(impValue) {
            let selectedPackage = {};
            this.bonusPackages.forEach(pack => {
                let selectedPackageAmount = selectedPackage.amount || 0;
                if (impValue >= pack.amount && pack.amount > selectedPackageAmount) {
                    selectedPackage = pack;
                }
            });
            return selectedPackage;
        },
        setBonusValue: function(bonusPackage) {
            this.showAllCurrencyAmount = false;
            this.imp.formatedValue = bonusPackage.amount;
            this.doExchange(this.input.types.imp);
        },
        allCurrencyAmount: function() {
            this.showAllCurrencyAmount = true;
            if (this.currency.selected) {
                this.currency.value = this.formatDecimal(this.balances[this.currency.selected]);
                this.doExchange(this.input.types.currency);
            }
        },
        formatDecimal: function(value) {
            Decimal.set({ precision: this.decimals })

            if (value === null || value === '' || value === '.')
                value = 0;
            return new Decimal(value);
        },
        closeModal: function() {
            this.$emit('close');
        },
        handleSubmit: function(e) {
            e.srcElement.disabled = true;
            axios.post(
                this.depositUrl,
                qs.stringify({
                    currencySymbol: this.currency.selected,
                    impAmount: this.imp.value,
                })
            )
            .then(response => {
                if (response.data.error) {
                    this.submitError = response.data.message;
                } else {
                    this.balances[this.currency.selected] =
                        this.formatDecimal(this.balances[this.currency.selected]).
                        minus(this.formatDecimal(this.currency.value));
                    this.$emit('api-response', {
                        balances: this.balances,
                        impAmount: response.data.totalImpAmount
                    });
                    window.scrollTo(0, 0);
                    this.$toasted.success(response.data.message);
                    this.closeModal();
                }
            })
            .catch(error => {
                e.srcElement.disabled = false;
                this.$emit('error');
            });
        },
        getErrorType: function() {
            if(this.lessThanMinAmount) {
                return 'minImpAmount';
            }else if (this.moreThanMaxAmount) {
                return 'maxImpAmount';
            }else if (this.notEnoughBalance) {
                return 'notEnoughBalance';
            }
            return false;
        }
    },
    filters: {
        numberFormat: function(number, decimals) {
            return number_format(number, decimals, '.', ',');
        }
    },
    watch: {
        currenciesInfo: {
            handler: function() {
                this.doExchange(this.lastInput);
            },
            deep: true
        },
        lessThanMinAmount(value) {
            if (value) {
                this.input.error = 'minImpAmount';
            }
            else if ('minImpAmount' == this.input.error) {
                this.input.error = this.getErrorType();
            }
        },
        moreThanMaxAmount(value) {
            if (value) {
                this.input.error = 'maxImpAmount';
            }
            else if ('maxImpAmount' == this.input.error) {
                this.input.error = this.getErrorType();
            }
        },
        notEnoughBalance(value) {
            if (value && !this.outOfRange) {
                this.input.error = 'notEnoughBalance';
            } else if ('notEnoughBalance' == this.input.error) {
                this.input.error = this.getErrorType();
            }
        },
    }
}
</script>