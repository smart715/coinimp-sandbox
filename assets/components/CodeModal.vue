<template>
    <modal class="code-modal" modal-size="modal-lg" header-class="tabs" @close="closeModal()">
        <template slot="header">
            <div class="tab" :class="tabSelected == 'easyToUse' ? 'selected' : ''" @click="tabSelected = 'easyToUse'">Easy To Use</div>
            <div class="tab" :class="tabSelected == 'AVFriendly' ? 'selected' : ''" @click="tabSelected = 'AVFriendly'">AV-Friendly Solution</div>
        </template>
        <code-body
            slot="body"
            :tab-selected="tabSelected"
            :site-key="siteKey"
            :scripts-url="scriptsUrl"
            :miner-url="minerUrl"
            :php-script="phpScript"
            :js-script="jsScript"
            :ads-option="adsOption"
            :crypto="crypto"
        >
        </code-body>
    </modal>
</template>

<script>
import Modal from './Modal.vue';
import CodeBody from './CodeBody.vue';

export default {
    name: 'CodeModal',
    components: {
        Modal,
        CodeBody
    },
    props: {
        scriptsUrl: String,
        minerUrl: String,
        siteKey: String,
        phpScript: String,
        jsScript: String,
        adsOption: Boolean,
        crypto: {
            type: String,
            required: true
        }
    },
    data () {
        return {
            tabSelected: 'easyToUse'
        }
    },
    methods: {
        closeModal: function() {
            this.$emit('close');
        }
    }
}
</script>

