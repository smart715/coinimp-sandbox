<template>
    <button class="btn btn-dark" type="button" :title="tooltipMessage" v-tippy="tooltipOptions">
        <slot>Copy to clipboard</slot>
    </button>
</template>

<script>
import Clipboard from 'clipboard';
import VueTippy from 'vue-tippy';
Vue.use(VueTippy);

var clipboard = new Clipboard('button');

export default {
    name: 'CopyButton',
    data () {
        return {
            tooltipMessage: '',
            tooltipOptions: {
                placement: 'bottom',
                arrow: true,
                trigger: 'click',
                delay: [100, 2000]
            }
        }
    },
    created: function() {
        clipboard.on('success', () => {
            this.tooltipMessage = 'Copied!';
        });
        clipboard.on('error', () => {
            this.tooltipMessage = 'Press Ctrl+C to copy';
        });
    }
}
</script>
