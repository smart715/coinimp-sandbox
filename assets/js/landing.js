import AOS from 'aos';
import Visibility from 'visibilityjs'
import axios from "axios/index";
import Routing from '../js/routing';
import number_format from 'locutus/php/strings/number_format';
import {asXmr} from "./util";

const usersCountUrl = Routing.generate('api_get_registered_users_count');
const totalCoinsUrl = Routing.generate('api_get_total_coins');
const usersCountRefreshRate = document.getElementById('users-count-refresh-rate').value;

AOS.init({
    duration: 800,
    once: true,
});

let landing = new Vue({
    el: '#landing',
    data: {
        registeredUsersCount: '',
        totalXmr: '',
        totalWeb: '',
        totalWorth: '',
    },
    created: function() {
        this.getRegisteredUsersCount();
        this.getTotalCoins();
        Visibility.every(usersCountRefreshRate * 1000, () => {
            this.getRegisteredUsersCount();
            this.getTotalCoins();
        })
    },
    methods: {
        getRegisteredUsersCount: function() {
            axios.get(usersCountUrl).then(response => {
                this.registeredUsersCount = number_format(response.data.count, 0,'.',' ');
            });
        },
        getTotalCoins: function ()
        {
            axios.get(totalCoinsUrl).then(response => {
                this.totalXmr = asXmr(response.data.xmr, 0) || 0;
                this.totalWeb = asXmr(response.data.web, 0) || 0;
                this.totalWorth = response.data.worth_usd;
            });
        },

    },
    filters: {
        toXMR: function(value, digits) {
            return asXmr(value, digits);
        },
        toRate: function(value,  digits) {
            return number_format(asXmr(value, digits), 0,'.',' ');
        }
    }

});

