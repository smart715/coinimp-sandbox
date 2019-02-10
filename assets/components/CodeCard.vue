<template>
    <div class="card code-card">
        <div class="card-header tabs">
            <div class="tab" :class="tabSelected == 'easyToUse' ? 'selected' : ''" @click="tabSelected = 'easyToUse'">Easy To Use</div>
            <div class="tab" :class="tabSelected == 'AVFriendly' ? 'selected' : ''" @click="tabSelected = 'AVFriendly'">AV-Friendly Solution</div>
        </div>
        <div class="card-body">
            <code-body
                :tab-selected="tabSelected"
                :site-key="siteKey"
                :scripts-url="scriptsUrl"
                :miner-url="minerUrl"
                :php-script="phpScript"
                :js-script="jsScript"
                :ads-option="adsOption"
                :crypto="crypto"
            >
            </code-body>
        </div>
    </div>
</template>

<script>
import CodeBody from './CodeBody.vue';

export default {
    name: "CodeCard",
    components: {
        CodeBody
    },
    props: {
        siteKey: String,
        scriptsUrl: String,
        minerUrl: String,
        phpScript: String,
        jsScript: String,
        adsOption: Boolean,
        crypto: {
            type: String,
            required: true
        }
    },
    data () {
        return {
            tabSelected: 'easyToUse',
        }
    }
}
</script>
