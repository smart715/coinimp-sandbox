<template>
    <modal @close="closeFormModal" :class="formLoaded ? 'primary-modal' : ''">
        <template v-if="formLoaded">
            <h2 slot="header" class="modal-title">{{ formTitle }}</h2>
            <div v-if="formBodyWithErrors" v-html="formBodyWithErrors" @click.capture="handleFormClick" slot="body"></div>
            <div v-else v-html="formBody" @click.capture="handleFormClick" slot="body"></div>
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
import axios from 'axios';
import serialize from 'form-serialize';
import { FontAwesomeIcon, FontAwesomeLayers } from '@fortawesome/vue-fontawesome'

export default {
    name: 'FormModal',
    components: {
        Modal,
	FontAwesomeLayers,
        FontAwesomeIcon
    },
    data () {
        return {
            formLoaded: false,
            formTitle: '',
            formBody: '',
            formBodyWithErrors: ''
        }
    },
    methods: {
        loadForm: function(url) {
            axios.get(url)
                .then(response => {
                    this.formTitle = response.data.header;
                    this.formBody = response.data.body;
                    this.formLoaded = true;
                })
                .catch(error => {
                    this.$emit('error');
                });
        },
        handleFormClick: function(e) {
            if (e.target.tagName == 'BUTTON' && e.target.type == 'submit') {
                e.preventDefault();
                e.target.setAttribute('disabled','');
                let data = serialize(e.target.form, {
                    hash: false,
                    empty: true
                });
                data += '&' + e.target.name + '='
                    + encodeURIComponent(e.target.value);
                let url = e.target.form.action;

                axios.post(url, data)
                    .then(response => {
                        e.target.removeAttribute('disabled');
                        if (response.data.action) {
                            this.formBodyWithErrors = '';
                            this.$emit('success-submit', response, e.target);
                        }
                        else {
                            this.formTitle = response.data.header;
                            this.formBodyWithErrors = response.data.body;
                        }
                    })
                    .catch(error => {
                        this.closeFormModal();
                        this.$emit('error');
                    });
            }
	    if (e.target.tagName == 'BUTTON' && e.target.type == 'button') {
	    	this.$emit('button-clicked', e.target);
	    }
        },
        closeFormModal: function() {
            this.formLoaded = false;
            this.$emit('close');
        }
    }
}
</script>

