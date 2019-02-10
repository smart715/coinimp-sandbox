<template>
    <div>
        <div class="row px-4">
            <div class="col-12 pb-3 text-danger">
                Just deposit {{ currency | upperCaseOnlyFirst }} to your {{ currencySymbol | toUpperCase }} address below!
            </div>
            <div class="col-12 pb-4">
                It's OK when sending {{ currencySymbol | toUpperCase }} from an exchange
            </div>
            <small class="col-12 pb-1 font-weight-bold">Your {{ currencySymbol | toUpperCase }} Address</small>
            <div class="col-12 pb-3 address-container">
                <input
                    type="text"
                    class="form-control form-control-md pr-5"
                    readonly
                    :value="address"
                    @click="$event.currentTarget.select()"
                >
                <button
                    type="button"
                    class="btn btn-primary text-center copy-button"
                    :title="tooltipMessage"
                    v-tippy="tooltipOptions"
                    :data-clipboard-text="address"
                >
                    <font-awesome-icon :icon="['far', 'copy']"/>
                </button>
            </div>
            <small v-if="paymentId" class="col-12 pb-1 font-weight-bold">Your {{ currencySymbol | toUpperCase }} Payment ID</small>
            <div v-if="paymentId" class="col-12 pb-3 address-container">
                <input
                    type="text"
                    class="form-control form-control-md pr-5"
                    readonly
                    :value="paymentId"
                    @click="$event.currentTarget.select()"
                >
                <button
                    type="button"
                    class="btn btn-primary text-center copy-button"
                    :title="tooltipMessage"
                    v-tippy="tooltipOptions"
                    :data-clipboard-text="paymentId"
                >
                    <font-awesome-icon :icon="['far', 'copy']"/>
                </button>
            </div>
            <div class="col-12 pt-4">
                <div class="row">
                    <div class="col-12 col-sm-4">
                        <div class="font-weight-bold pb-2">QR Code</div>
                        <div v-if="paymentId" class="text-left pb-2">
                            <div class="btn-group btn-group-sm btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-secondary"
                                       :class="{active: 'address' === activeQr}"
                                       @click="activeQr = 'address'"
                                >
                                    <input type="radio" autocomplete="off">
                                    <small>Address</small>
                                </label>
                                <label class="btn btn-secondary"
                                       :class="{active: 'id' === activeQr}"
                                       @click="activeQr = 'id'"
                                >
                                    <input type="radio" autocomplete="off">
                                    <small>Payment ID</small>
                                </label>
                            </div>
                        </div>
                        <qrcode-vue v-if="'address' === activeQr" :value="address" :size="qrCodeSize" level="H"/>
                        <qrcode-vue v-if="'id' === activeQr" :value="paymentId" :size="qrCodeSize" level="H"/>
                    </div>
                    <div class="pt-4 pt-sm-0 col-12 col-sm-8 d-flex align-items-center">
                        <div class="col-12 p-0">
                            <p>You should check your transaction here</p>
                            <p>If you need help, please contact us at <a :href="mailTo" target="_blank">{{ supportEmail }}</a></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 pt-3">
                <button type="button" class="btn btn-primary float-right" @click="closeModal">
                    Close
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { library } from '@fortawesome/fontawesome-svg-core';
import { far } from '@fortawesome/free-regular-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import QrcodeVue from 'qrcode.vue';
import Clipboard from 'clipboard';
import VueTippy from 'vue-tippy';

library.add(far);
Vue.use(VueTippy);
var clipboard = new Clipboard('.copy-button');

export default {
    name: 'DepositForm',
    components: {
        FontAwesomeIcon,
        QrcodeVue
    },
    props: {
        currency: String,
        currencySymbol: String,
        address: String,
        paymentId: String,
        supportEmail: String
    },
    data() {
        return {
            qrCodeSize: 150,
            tooltipMessage: '',
            tooltipOptions: {
                placement: 'top',
                arrow: true,
                trigger: 'click',
                delay: [100, 2000]
            },
            currentQr: 'address',
        }
    },
    computed: {
        mailTo: function() {
            return 'mailto:' + this.supportEmail;
        },
        activeQr: {
            set(value) {
                this.currentQr = value;
            },
            get() {
                return this.currentQr;
            }
        }
    },
    created: function() {
        clipboard.on('success', () => {
            this.tooltipMessage = 'Copied!';
        });
        clipboard.on('error', () => {
            this.tooltipMessage = 'Click on address and press Ctrl+C to copy';
        });
    },
    methods: {
        closeModal: function() {
            this.$emit('close');
        }
    },
    filters: {
        upperCaseOnlyFirst: function(str) {
            return str.charAt(0).toUpperCase() + str.toLowerCase().slice(1);
        },
        toUpperCase: function(str) {
            return str.toUpperCase();
        }
    }
}
</script>
