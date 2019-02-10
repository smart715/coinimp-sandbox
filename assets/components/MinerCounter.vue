<template>
    <div class="miner-field">
        <span>
            <span :id="'miner-'+name+'-value'">{{ value }}</span>
            <slot name="value-postfix"></slot>
        </span>
        <button class="btn btn-primary btn-sm btn-miner btn-miner-up" :id="'miner-'+name+'-up'" type="button" @click="increment">
            <font-awesome-icon icon="angle-up" />
        </button>
        <button class="btn btn-primary btn-sm btn-miner btn-miner-down" :id="'miner-'+name+'-down'" type="button" @click="decrement">
            <font-awesome-icon icon="angle-down" />
        </button>
    </div>
</template>

<script>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

export default {
    name: 'MinerCounter',
    components: {
        FontAwesomeIcon
    },
    props: {
        name: String,
        initial: Number,
        min: Number,
        max: Number,
        multiplier: Number
    },
    data () {
        return {
            value: this.initial
        }
    },
    watch: {
        value: function(newValue, oldValue) {
            if (newValue !== oldValue)
                this.$emit('change-value', newValue);
        }
    },
    methods: {
        increment: function() {
            let newValue = this.value + this.multiplier;
            this.value = newValue <= this.max ? newValue : this.value;
        },
        decrement: function() {
            let newValue = this.value - this.multiplier;
            this.value = newValue >= this.min ? newValue : this.value;
        }
    }
}
</script>
