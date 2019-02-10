<template>
    <modal @close="closeModal" :class="loadStatus ? 'primary-modal' : ''">
        <template v-if="loadStatus">
            <h2 slot="header" class="modal-title">Your API keys:</h2>
            <template slot="body">
                <div class="row justify-content-center">
                    <div class="col-12 text-center">
                        <p>Do not share these keys to anyone for security reasons</p>
                    </div>
                    <div class="col-12 mb-1" id="api-keys-text">
                        <pre><code class="js hljs javascript"><span class="hljs-tag">{{ apiKeys | stringifyJson }}</span></code></pre>
                    </div>
                    <div class="col-12 mb-2 text-center">
                        <copy-button data-clipboard-target="#api-keys-text">Copy to clipboard</copy-button>
                    </div>
                </div>
            </template>
        </template>
        <template slot="body" v-else>
            <div class="row mb-3">
                <div class="col text-center">
                    <font-awesome-layers class="fa-3x">
                        <font-awesome-icon icon="circle-notch" spin class="loading-spinner" fixed-width  />
                    </font-awesome-layers>
                </div>
            </div>
        </template>
    </modal>
</template>

<script>
import Modal from './Modal.vue';
import CopyButton from './CopyButton.vue';
import { FontAwesomeIcon, FontAwesomeLayers } from '@fortawesome/vue-fontawesome'

export default {
    name: 'ApiKeysModal',
    components: {
        Modal,
        CopyButton,
	FontAwesomeLayers,
        FontAwesomeIcon
    },
    props: { loadStatus: Boolean, apiKeys: Object},
    methods: {
        closeModal: function() {
            this.$emit('close');
        }
    },
    filters: {
        stringifyJson: function (value) {
            return JSON.stringify(value, null, 2);
        }
    }
}
</script>

