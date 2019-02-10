<template>
    <input type="number" :value="value" @change="updateValueWithDigits" @keypress="filterKeys" @input="updateValue" onpaste="return false;">
</template>

<script>
    export default {
        props: {
            value: String
        },
        methods: {
            filterKeys: function(event) {
                if(! /^(\d|\.|,|arrowup|arrowdown|arrowleft|arrowright|backspace|delete)$/g.test(event.key.toLowerCase()))
                    event.preventDefault();
            },
            updateValueWithDigits: function(event) {
                let value = (event.target.value)? (event.target.value * 1).toFixed(8): event.target.value;
                this.$emit('input', value);
            },
            updateValue: function(event) {
                this.$emit('input', event.target.value);
            }
        }
    }
</script>
