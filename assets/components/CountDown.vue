<template>
    <div v-if="!isDisabled">
        <div class="count-down row" v-if="!isEndDate">
            <div class="col px-2 px-lg-3 mt-3 mb-1">
                <div class="digit">{{ padDigits(days, 2) }} </div>
                <div class="text">Days</div>
            </div>
            <div class="col px-2 px-lg-3 mt-3 mb-1">
                <div class="digit">{{ padDigits(hours, 2) }} </div>
                <div class="text">Hours</div>
            </div>
            <div class="col px-2 px-lg-3 mt-3 mb-1">
                <div class="digit">{{ padDigits(minutes, 2) }} </div>
                <div class="text">Minutes</div>
            </div>
            <div class="col px-2 px-lg-3 mt-3 mb-1">
                <div class="digit">{{ padDigits(seconds, 2) }}</div>
                <div class="text">Seconds</div>
            </div>
        </div>
    </div>
</template>



<script>
    export default {
        name: 'CountDown',
        props : {
            endDate: String,
            currentDate: String,
            disabled: Boolean
        },
        data() {
            return {
                now: Math.trunc((new Date(this.currentDate.replace(/-/g, '/'))).getTime() / 1000)
            }
        },
        mounted() {
            window.setInterval(() => {
                this.now = this.now + 1;
            },1000);
        },
        computed: {
            formattedDate: function() {
                return Math.trunc(Date.parse(this.endDate.replace(/-/g, '/')) / 1000)
            },
            seconds: function() {
                return (this.formattedDate - this.now) % 60;
            },
            minutes: function() {
                return Math.trunc((this.formattedDate - this.now) / 60) % 60;
            },
            hours: function() {
                return Math.trunc((this.formattedDate - this.now) / 60 / 60) % 24;
            },
            days: function() {
                return Math.trunc((this.formattedDate - this.now) / 60 / 60 / 24);
            },
            isEndDate: function() {
                return ((this.days <= 0) &&
                        (this.hours <= 0) &&
                        (this.minutes <=0) &&
                        (this.seconds <= 0));
            },
            isDisabled: function() {
                return this.disabled;
            }
        },
        methods: {
            padDigits: function (value, digits) {
                return Array(Math.max(digits - String(value).length + 1, 0)).join(0) + value;
            }
        }
    };
</script>

<style lang="scss" scoped>
@import '~bootstrap/scss/bootstrap';
@import url(https://fonts.googleapis.com/css?family=Roboto+Condensed:400|Roboto:100);

.count-down {
    align-items: center;
    background-color: #4F4F4F;
    justify-content: center;
    border-radius: 1rem;

    & .digit {
        color: #35789D;
        font-size: 2.5rem;
        font-weight: bold;
        font-family: 'Roboto', serif;
        text-align: center;
        background-color: #FFFFFF;
        padding: 0 0.5rem;
        @include media-breakpoint-down(xs) {
            font-size: 1.8rem;
        }
    }

    & .text {
        color: #08A2E8;
        font-size: 1rem;
        font-family: 'Roboto Condensed', serif;
        text-align: center;
        @include media-breakpoint-down(xs) {
            font-size: 0.8rem;
        }
    }
}

</style>
