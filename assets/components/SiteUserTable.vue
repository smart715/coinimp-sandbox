<template>
    <feature-table
            id="site-user-table"
            :columns="siteColumns"
            :records="users"
    >
        <template slot-scope="props" slot="rows">
            <tr v-for="user in users"
                :key="user.site"
                v-bind="user">
                <td class="site-name">{{ user.name }}</td>
                <td class="text-right">{{ user.site }}</td>
                <td class="text-right">{{ user.hashRate|numberFormat(0) }}</td>
                <td class="text-right">{{ user.hashes|numberFormat(0) }}</td>
                <td class="text-right">{{ user.hashes - user.withdrawn|numberFormat(0) }}</td>
                <td class="text-right">{{ user.withdrawn|numberFormat(0) }}</td>
            </tr>
        </template>
    </feature-table>
</template>

<script>
import FeatureTable from './FeatureTable.vue';
import number_format from 'locutus/php/strings/number_format';

export default {
    name: "site-user-table",
    components: {
        FeatureTable
    },
    props: {
        users: Array
    },
    data () {
        return {
            siteColumns: [
                { key: 'name', name: 'Name', icon: 'sort', sort: '', class: '', isAplha: true },
                { key: 'site', name: 'Site', icon: 'sort', sort: '', class: '', isAplha: true },
                { key: 'hashesRate', name: 'Hashes/s', icon: 'sort', sort: '', class: 'text-right', isAplha: false },
                { key: 'hashesTotal', name: 'Total Hashes', icon: 'sort', sort: '', class: 'text-right', isAplha: false },
                { key: 'pending', name: 'Pending', icon: 'sort', sort: '', class: 'text-right', isAplha: false },
                { key: 'withdrawn', name: 'Withdrawn', icon: '', sort: '', class: 'text-center', isAplha: false }
            ]
        }
    },
    filters: {
        numberFormat: function (value, digits) {
            return number_format(value, digits, '.', ' ');
        }
    }
}
</script>
