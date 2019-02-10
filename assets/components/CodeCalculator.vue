<template>
    <div class="card">
        <div class="card-header text-center">
            Start Earning Now!
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="text-center">
                        <span class="highlight-text">Statistics</span><hr>
                    </div>
                    <div class="form-group">
                        <label for="expectedHashRatePerUser">Expected hash rate per user</label>
                        <input 
                            type="text"
                            id="expectedHashRatePerUser"
                            v-model="expectedHashRatePerUser"
                            @keypress="validationExpectedHashRatePerUser"
                            class="form-control"
                        >
                    </div>
                    <div class="form-group">
                        <label for="expectedUsersPerDay">Expected users per day</label>
                        <input
                            type="text"
                            id="expectedUsersPerDay"
                            v-model="expectedUsersPerDay"
                            @keypress="validationExpectedUserPerDay"
                            class="form-control"
                        >
                    </div>
                    <div class="form-group">
                        <label>Current payout: {{ payoutPerMillion | toXMR }} {{ crypto | upper }} per 1M hashes</label>
                    </div>
                    <div class="form-group">
                        <label for="period" class="d-block">How long user stays on page</label>
                        <input
                            type="text"
                            id="period"
                            v-model="expectedPeriod"
                            @keypress="validationExpectedPeriod"
                            class="form-control d-inline-block col-6"
                        />
                        <select
                            title="Time format"
                            id="period-format"
                            v-model="periodSelected"
                            class="form-control custom-select d-inline-block align-baseline"
                        >
                            <option
                                v-for="period in periods"
                                :value="period"
                            >
                                {{ period.label }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="text-center">
                        <span class="highlight-text">Results</span><hr>
                    </div>
                    <div class="form-group">
                        <label for="expectedXmrPerDay">Expected {{ crypto | upper }} per day</label>
                        <input 
                            type="text"
                            class="form-control" 
                            id="expectedXmrPerDay"
                            :value="expectedXmrPerDay | toXMR" disabled
                        >
                    </div>
                    <div class="form-group">
                        <label for="expectedXmrPerMonth">Expected {{ crypto | upper }} per month</label>
                        <input
                            type="text"
                            class="form-control"
                            id="expectedXmrPerMonth"
                            :value="expectedXmrPerMonth | toXMR" disabled
                        >
                    </div>
                    <div class="form-group">
                        <label for="expectedXmrPerYear">Expected {{ crypto | upper }} per year</label>
                        <input 
                            type="text"
                            class="form-control"
                            id="expectedXmrPerYear"
                            :value="expectedXmrPerYear | toXMR" disabled
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { asXmr} from '../js/util.js';

var periods = [
    { label: 'Seconds', value: 1,    limit: 86400 },
    { label: 'Minutes', value: 60,   limit: 1440  },
    { label: 'Hours',   value: 3600, limit: 24    }
];

export default {
    name: "CodeCalculator",
    props: {
        payoutPerMillion: {
            type: Number,
            required: true
        },
        crypto: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            expectedHashRatePerUser: 0,
            expectedUsersPerDay: 0,
            expectedPeriod: 1,
            periods: periods,
            periodSelected: periods[periods.length - 1],
        }
    },
    methods: {
        preventLimitedPeriodOnInput: function(e) {
            var charCode = (e.which || e.keyCode);
            var charVal = String.fromCharCode(charCode);

            if (parseInt(this.expectedPeriod + charVal) > this.periodSelected.limit &&
                (charCode !== 8 ) &&
                (charCode !== 9))
                e.preventDefault();

            return true;
        },
        preventInvalidValOnInput: function(e) {
            var charCode = (e.which || e.keyCode);
            var charVal = String.fromCharCode(charCode);
            if((isNaN(charVal)) && (charCode !== 8 ) && (charCode !== 9))
                e.preventDefault();
            return true;
        },
        preventEmptyValOnInput: function(e, valueField) {
            var charCode = (e.which || e.keyCode);
            var charVal = String.fromCharCode(charCode);
            if (charCode === 8 && valueField < 9) {
                e.preventDefault();
                return 0;
            }
            if (valueField === 0 && !isNaN(charVal)) {
                e.preventDefault();
                return String.fromCharCode(charCode);
            }
            return valueField;
        },
        validationExpectedHashRatePerUser: function(e) {
            this.preventInvalidValOnInput(e);
            this.expectedHashRatePerUser = this.preventEmptyValOnInput(e, this.expectedHashRatePerUser);
        },
        validationExpectedUserPerDay: function(e) {
            this.preventInvalidValOnInput(e);
            this.expectedUsersPerDay = this.preventEmptyValOnInput(e, this.expectedUsersPerDay);
        },
        validationExpectedPeriod: function(e) {
            this.preventInvalidValOnInput(e);
            this.preventLimitedPeriodOnInput(e);
            this.expectedPeriod = this.preventEmptyValOnInput(e, this.expectedPeriod);
        }
    },
    computed: {
        resPeriod: function() {
            return this.expectedPeriod * this.periodSelected.value;
        },
        resExpectedHashes: function() {
            return this.expectedHashRatePerUser * this.expectedUsersPerDay * this.resPeriod;
        },
        expectedXmrPerDay: function() {
            return (this.payoutPerMillion * this.resExpectedHashes) / Math.pow(10, 6);
        },
        expectedXmrPerMonth: function() {
            return this.expectedXmrPerDay * 30;
        },
        expectedXmrPerYear : function() {
            return this.expectedXmrPerMonth * 12;
        }
    },
    watch: {
        periodSelected: function (value) {
            if (this.expectedPeriod > value.limit)
                this.expectedPeriod = value.limit;
        }
    },
    filters: {
        toXMR: function(value, digits) {
            return asXmr(value, digits);
        },
        upper: function (value) {
            return value.toUpperCase();
        }
    }
}
</script>
