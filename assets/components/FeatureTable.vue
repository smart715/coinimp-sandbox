<template>
<div>
    <table id="id" class="table">
        <thead class="feature-header">
    	    <tr>
	        <th v-for="column in columns" v-bind:class="column.class">
        	    <a  @click="sortBy(column.key)"  v-if="column.key !== false && column.icon !== ''">
                    <font-awesome-layers class="fa-1x">
                        <font-awesome-icon :icon="column.icon"/>
                    </font-awesome-layers>
                    {{ column.name }}
        	    </a>
		    <span v-else>
	    	        {{ column.name }}
		    </span>
    	        </th>
  	    </tr>
        </thead>
        <paginate
            v-if="showRecords"
            name="records"
            :list="records"
            :per="10"
            tag="tbody"
            ref="paginator"
            >
            <slot :records="paginated('records')" :editRecord="editRecord" :deleteRecord="deleteRecord" name="rows">
            </slot>
        </paginate>
        <tbody v-else>
            <tr id="empty-table-row">
                <td class="text-center" :colspan="getColumnnLength">
                    <em>No record were added yet</em>
                </td>
            </tr>
        </tbody>
    </table>
    <paginate-links
        :async="true"
        for="records"
        :show-step-links="true"
        :hide-single-page="true">
    </paginate-links>
</div>
</template>

<script>
import { FontAwesomeIcon, FontAwesomeLayers } from '@fortawesome/vue-fontawesome'
import _ from 'lodash';
import VuePaginate from 'vue-paginate';
Vue.use(VuePaginate);

export default {
    name: 'FeatureTable',
    components: {
        FontAwesomeLayers,
        FontAwesomeIcon
    },
    props: {
        columns: Array,
        records: Array
    },
    data () {
        return {
            paginate: ['records']
        }
    },
    computed: {
        showRecords: function() {
            return this.records.length > 0;
        },
        getColumnnLength: function() {
            return this.columns.length;
        }
    },
    watch: {
        records: function(newRecords, oldRecords) {
            if ( newRecords.length < 10 && oldRecords.length > 0 ) {
                setTimeout(() => {
                    if (this.$refs.paginator)
                        this.$refs.paginator.goToPage(1);
                }, 100);
            }
	    }
    },

    methods: {
        editRecord: function(record) {
            this.$emit('edit-record', record);
        },
        deleteRecord: function(record) {
            this.$emit('delete-record', record);
        },
        sortBy: function(key) {
            let index  = _.findIndex(this.columns, ['key', key]);
            _.forEach(this.columns, function(column) {
                if (column.key !== false && column.key !== key) {
                    column.sort  =  '';
                    column.icon = 'sort';
                }
            });
            if(this.columns[index].sort === 'asc') {
                this.columns[index].sort =  'desc';
                this.columns[index].icon = 'sort-down';
            }
            else {
                this.columns[index].sort  =  'asc';
                this.columns[index].icon = 'sort-up';
            }
            this.$emit('sort', key, this.columns[index].sort, this.columns[index].isAplha);
        }

    }
}
</script>
