<template>
    <tr>
        <td class="site-name">{{ name }}</td>
        <td v-if="poolError" class="text-right"> - </td>
        <td v-else class="text-right">{{ hashesRate|numberFormat(0) }}</td>
        <td v-if="poolError" class="text-right"> - </td>
        <td v-else class="text-right">{{ hashesTotal|numberFormat(0) }}</td>
        <td class="text-right">{{ reward|toXMR }} {{ crypto|upper }}</td>
        <td class="actions text-center">
            <span class="sites-code-icon" title="Generate Site code for background mining" @click="showCodeModal = true" v-tippy="tooltipOptions" @mouseover="showTooltip" @mouseleave="hideTooltip"><font-awesome-icon icon="desktop" /></span>
            <span class="sites-edit-icon" title="Edit Site" @click="loadFormModal(editUrl)" @error="loadFormModal(editUrlE)" v-tippy="tooltipOptions" @mouseover="showTooltip" @mouseleave="hideTooltip"><font-awesome-icon icon="edit" /></span>
            <span class="sites-delete-icon" title="Delete Site" @click="loadFormModal(deleteUrl)" v-tippy="tooltipOptions" @mouseover="showTooltip" @mouseleave="hideTooltip"><font-awesome-icon icon="trash-alt" /></span>
        </td>
        <code-modal
            v-if="showCodeModal"
            @close="showCodeModal = false"
            :site-key="siteKey"
            :scripts-url="scriptsUrl"
            :miner-url="minerUrl"
            :php-script="phpScript"
            :js-script="jsScript"
            :ads-option="adsOption"
            :crypto="crypto"
        >
        </code-modal>
        <form-modal
            v-if="showFormModal"
            ref="form"
            @success-submit="handleFormSubmit"
            @error="loadFormModal(editUrlE)"
            @button-clicked="handleFormButton"
            @close="showFormModal = false">
        </form-modal>
    </tr>
</template>

<script>
import Modal from './Modal.vue';
import FormModal from './FormModal.vue';
import CodeModal from './CodeModal.vue';
import { asXmr } from '../js/util.js'
import number_format from 'locutus/php/strings/number_format';
import VueTippy from 'vue-tippy';
import Toasted from 'vue-toasted';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
Vue.use(VueTippy);
Vue.use(Toasted, {
    position: 'top-center',
    duration: 5000,
});

export default {
    name: 'site-row',
    components: {
        Modal,
        FormModal,
        CodeModal,
	FontAwesomeIcon
    },
    props: {
        poolError: Boolean,
        hashesRate: Number,
        hashesTotal: Number,
        isVisible: Boolean,
        siteKey: String,
        name: String,
        reward: Number,
        words: String,
        editUrl: String,
        editUrlE: String,
        deleteUrl: String,
        scriptsUrl: String,
        minerUrl: String,
        phpScript: String,
        jsScript: String,
        adsOption: Boolean,
        crypto: String
    },
    data () {
        return {
            showCodeModal: false,
            showFormModal: false,
            tooltipOptions : {
                placement: 'bottom',
                arrow: true,
                trigger: 'manual',
                delay: [200, 0]
            }
        }
    },
    methods: {
        showTooltip(e) {
            if (typeof e.target._tippy != 'undefined')
                e.target._tippy.show();
        },
        hideTooltip(e) {
            if (typeof e.target._tippy != 'undefined')
                e.target._tippy.hide();
        },
        loadFormModal: function(url) {
            this.showFormModal = true;
            Vue.nextTick(() => this.$refs.form.loadForm(url));
        },
        handleFormSubmit: function(response) {
            this.showFormModal = false;
            this.$toasted.success(response.data.message);
            this.$emit(response.data.action, response.data.site);
        },
        handleFormButton: function(target) {
            if (target.hasAttribute('close'))
		this.showFormModal = false;
        }
    },
    filters: {
        numberFormat: function(value, digits) {
            return number_format(value, digits, '.', ' ');
        },
        toXMR: function(value, digits) {
            return asXmr(value, digits);
        },
        upper: function(value) {
            return value.toUpperCase();
        }
    }
}
</script>
