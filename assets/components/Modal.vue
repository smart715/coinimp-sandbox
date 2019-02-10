<template>
    <div class="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" :class="modalSize" role="document">
                <div class="modal-content">
                    <modal-header @close="closeModal()" :class="headerClass">
                        <slot name="header"></slot>
                    </modal-header>
                    <div class="modal-body">
                        <slot name="body"></slot>
                    </div>
                </div>
            </div>
        </div>
</template>

<script>
import ModalHeader from './ModalHeader.vue';

export default {
    name: 'Modal',
    components: {
        ModalHeader
    },
    props: { modalSize: String, headerClass: String },
    methods: {
        closeModal: function() {
            this.$emit('close');
        }
    }
}
</script>

