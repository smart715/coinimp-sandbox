<template>
    <div class="alert alert-warning">
        You are logged out, please <a :href="loginUrl" @click.prevent="login()">log in</a> to resume Dashboard normal behaviour.
        <font-awesome-layers>
            <font-awesome-icon icon="circle-notch"  v-show="fetching" spin class="spinner" />
        </font-awesome-layers>
    </div>
</template>


<script>
import axios from 'axios';
import Routing from '../js/routing';
import { FontAwesomeIcon, FontAwesomeLayers } from '@fortawesome/vue-fontawesome'

export default {
    name: 'LoginAlert',
    components: {
        FontAwesomeLayers,
        FontAwesomeIcon
    },
    data () {
        return {
            loginUrl: Routing.generate('fos_user_security_login'),
            fetching: false
        }
    },
    methods: {
        login: function() {
            this.fetching = true;
            let sessionUrl = Routing.generate('api_get_session_status');
            axios.get(sessionUrl)
                .then(response => {
                    this.$emit('click-login', response.data.loggedIn);
                    this.fetching = false;
                })
                .catch(error => {
                    this.fetching = false;
                });
        }
    }
}
</script>