<template>
    <div class="modal referral-ico" tabindex="-1" role="dialog" @click="closeModal">
        <div class="modal-dialog modal-lg" role="document" @click.stop>
            <div class="modal-content py-0 px-auto m-auto w-85 border-0">
                <div class="modal-body py-0 px-auto border-0">
                    <slot name="body"></slot>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ReferralModal',
    methods: {
        closeModal: function() {
            this.$emit('close');
        }
    }
}
</script>
