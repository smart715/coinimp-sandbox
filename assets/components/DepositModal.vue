<template>
    <modal @close="closeModal" class="deposit-modal primary-modal">
        <h2 slot="header" class="modal-title">DEPOSIT {{ currency | toUpperCase }}</h2>
        <deposit-form
            slot="body"
            :currency="currency"
            :currencySymbol="currencySymbol"
            :address="address"
            :payment-id="paymentId"
            :support-email="supportEmail"
            @close="closeModal"
        >
        </deposit-form>
    </modal>
</template>

<script>
import Modal from './Modal.vue';
import DepositForm from './DepositForm.vue';

export default {
    name: 'DepositModal',
    components: {
        Modal,
        DepositForm
    },
    props: {
        currency: String,
        currencySymbol: String,
        address: String,
        paymentId: String,
        supportEmail: String
    },
    methods: {
        closeModal: function() {
            this.$emit('close');
        }
    },
    filters: {
        toUpperCase: function(str) {
            return str.toUpperCase();
        }
    }
}
</script>
