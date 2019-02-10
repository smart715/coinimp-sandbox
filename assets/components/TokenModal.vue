<template>
    <modal @close="closeModal"
           :class="'primary-modal token-modal'"
    >
        <h2 slot="header" class="modal-title">OUR TOKEN PACKAGES</h2>
        <token slot="body"
               :currencies-usd-rate="currenciesUsdRate"
               :refresh-miliseconds="currenciesUsdRateRefreshDuration"
               :balance="balance"
               :imp-price-in-usd="impPriceInUsd"
               :available-imp="availableImp"
               :deposit-url="depositUrl"
               :bonus-packages="bonusPackages"
               :imp-min-amount="impMinAmount"
               :imp-max-amount="impMaxAmount"
               :decimals="decimals"
               @api-response="updateData"
               @error="handleModalError"
               @close="closeModal"
        />
    </modal>
</template>

<script>
import Modal from './Modal.vue';
import Token from './Token.vue';

export default {
    name: 'TokenModal',
    components: {
        Modal,
        Token,
    },
    props: {
        balance: Object,
        currenciesUsdRate: Object,
        impPriceInUsd: Number,
        availableImp: Number,
        depositUrl: String,
        depositImpBonusPackages: String,
        impMinAmount: Number,
        impMaxAmount: Number,
        currenciesUsdRateRefreshDuration: Number,
        decimals: Number,
    },
    computed: {
        bonusPackages: function() {
            return JSON.parse(this.depositImpBonusPackages);
        }
    },
    methods: {
        updateData: function(newData) {
            this.$emit('api-response', newData);
        },
        handleModalError: function() {
            this.$emit('error');
        },
        closeModal: function() {
            this.$emit('close');
        }
    }
}
</script>
