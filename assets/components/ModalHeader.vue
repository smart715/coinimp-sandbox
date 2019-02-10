<template>
    <div class="modal-header">
        <slot></slot>
        <button @click="closeModal()" type="button" class="close" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</template>

<script>
export default {
    name: 'ModalHeader',
    methods: {
        closeModal: function() {
            this.$emit('close');
        }
    }
}
</script>
