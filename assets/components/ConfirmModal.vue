<template>
    <modal class="primary-modal" @close="closeModal">
        <h2 slot="header" class="modal-title">Warning</h2>
        <div slot="body" class="row justify-content-center">
            <div class="col-12 text-center my-2">
                <slot>Are you sure?</slot>
            </div>
            <div class="col-12 mb-2 text-center">
                <button class="btn btn-primary" @click="onConfirm"><slot name="confirm">Confirm</slot></button>
                <button class="btn btn-primary ml-3" @click="onCancel"><slot name="cancel">Cancel</slot></button>
            </div>
        </div>
    </modal>
</template>

<script>
    import Modal from './Modal.vue';

    export default {
        name: 'confirm-modal',
        components: { Modal },
        methods: {
            closeModal: function () {
                this.$emit('close');
            },
            onConfirm: function () {
                this.closeModal();
                this.$emit('confirm');
            },
            onCancel: function () {
                this.closeModal();
                this.$emit('cancel');
            }
        }
    }
</script>