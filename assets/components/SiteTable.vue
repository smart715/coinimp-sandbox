<template>
<feature-table
    id="sites-table"
    :columns="siteColumns"  
    :records="visibleSites"
    @edit-record="editSite"
    @delete-record="deleteSite"
    @sort="sortSiteBy"
    >
    <template slot-scope="props" slot="rows">
        <site-row
            v-for="site in props.records"
            @edit-site="props.editRecord"
            @delete-site="props.deleteRecord"
            :key="site.words"
            :pool-error="poolError"
            v-bind="site"
            :scripts-url="scriptsUrl"
            :miner-url="minerUrl"
            :php-script="phpScript"
            :js-script="jsScript"
            :ads-option="adsOption"
            :crypto="crypto">
        </site-row>
    </template>
</feature-table>   
</template>


<script>
import FeatureTable from './FeatureTable.vue';
import SiteRow from './SiteRow.vue';

export default {
    name: 'SiteTable',
    components: {
        SiteRow,
        FeatureTable
    },
    data () {
        return {
            siteColumns: [
                { key: 'name', name: 'Name', icon: 'sort', sort: '', class: '', isAplha: true },
                { key: 'hashesRate', name: 'Hashes/s', icon: 'sort', sort: '', class: 'text-right', isAplha: false },
                { key: 'hashesTotal', name: 'Hashes', icon: 'sort', sort: '', class: 'text-right', isAplha: false },
                { key: 'reward', name: 'Total ' + this.crypto.toUpperCase(), icon: 'sort', sort: '', class: 'text-right', isAplha: false },
                { key: false, name: 'Actions', icon: '', sort: '', class: 'text-center', isAplha: false }
            ]
        }
    },
    props: {
        records: Array,
        minerUrl: String,
        phpScript: String,
        jsScript: String,
        scriptsUrl: String,
        visibleSites: Array,
        adsOption: Boolean,
        crypto: String,
        poolError: Boolean
    },
    methods: {
        editSite: function(site) {
           this.$emit('edit-site', site);
        },
        deleteSite: function(site) {
            this.$emit('delete-site', site);
        },
        sortSiteBy: function(newSortKey, newSortOrder, isAplha) {
            this.$emit('sort', newSortKey, newSortOrder, isAplha);
        }
    }
}
</script>