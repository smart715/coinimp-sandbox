<template>
    <div>
        <div class="col-12 mb-1">
            <span class="info">Example preview</span>
        </div>
        <div class="mb-4" :style="sampleDivStyle">
        {{ notification.text }}
        </div>
        <div class="form-group form-row form-inline">
            <label class="text-left">Notification text:
                <input class="form-control bg-light ml-2 w-100" v-model="notification.text" @keyup="changeNotification"
                       size="43" maxlength="90">
            </label>
            <label class="ml-lg-auto">Background Color:
                <input class="ml-2" v-model="notification.backgroundColor" type="color" @keyup="changeNotification">
            </label>
        </div>
        <div class="form-group form-row form-inline">
            <label class="d-inline-flex">Position:
                <select class="form-control bg-light ml-2" v-model="notification.position" @change="changeNotification">
                    <option>Top</option>
                    <option>Bottom</option>
                    <option>Floating Top</option>
                    <option>Floating Bottom</option>
                </select>
            </label>
            <label class="ml-lg-auto">Font Color:
                <input class="ml-2" v-model="notification.color" type="color" @keyup="changeNotification">
            </label>
        </div>
        <div class="form-group form-inline">
            <label class="d-inline-flex">Height:
                <input class="form-control bg-light ml-2 mr-1" type="number"
                       @change="changeNotificationHeight"
                       @blur="changeNotificationHeight" :value="notification.height" min="20" max="200"> px
            </label>
        </div>
    </div>
</template>

<script>
    export default {
        name: "MinerNotificationOptions",
        data() {
            return {
                notification: {
                    text: 'This site is running JavaScript miner from coinimp.com',
                    position: 'Top',
                    height: '40',
                    backgroundColor: '#cccccc',
                    color: '#3d3d3d'
                }
            }
        },
        created: function() {
            this.changeNotification();
        },
        methods: {
            changeNotification: function() {
                this.$emit('notification-changed', this.notification)
            },

            changeNotificationHeight: function(event) {
                var max = parseInt(event.currentTarget.getAttribute('max'));
                var min = parseInt(event.currentTarget.getAttribute('min'));
                if (event.currentTarget.value > max){
                    event.currentTarget.value = max;
                }
                if (event.currentTarget.value < min){
                    event.currentTarget.value = min;
                }
                this.notification.height = event.currentTarget.value;
                this.$emit('notification-changed', this.notification)
            }
        },
        computed: {
            sampleDivStyle: function() {
                return 'width: 100%; color: ' + this.notification.color + '; background-color: '+ this.notification.backgroundColor +'; height: '+ this.notification.height+'px; text-align: center; font-size: 1rem; overflow: hidden; line-height:' + this.notification.height+'px;"'
            }
        }
    }
</script>