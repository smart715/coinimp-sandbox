<template>
<pre><code class="js hljs javascript"><span class="hljs-tag">&lt;script</span> <span class="hljs-attribute">src=</span><span class="hljs-string">"{{ url }}"</span><span class="hljs-tag">&gt;&lt;/script&gt;</span>
<span class="hljs-tag">&lt;script&gt;</span>
    <span class="hljs-keyword">var</span> <span class="hljs-variable">_client</span> = <span class="hljs-keyword">new</span> <span class="hljs-variable">Client</span>.Anonymous(<span class="hljs-string">'{{ siteKey }}'</span>, {
        throttle: <span class="hljs-number">{{ throttle }}</span><span v-if="crypto == 'web'">, c: <span class="hljs-string">'w'</span></span><span v-show="adsChecked">, ads: <span class="hljs-string">1</span></span>
    });
    <span class="hljs-variable">_client</span>.start();
    <span v-show="notificationChecked"><span class="hljs-variable">_client</span>.addMiningNotification(<span class="hljs-string">"{{ this.notification.position }}"</span>, <span class="hljs-string">"{{ notification.text }}"</span>, <span class="hljs-string">"{{ notification.backgroundColor }}"</span>, {{ notification.height }}, <span class="hljs-string">"{{ notification.color }}"</span>);</span>
<span v-show="antiBlockChecked"><span class="hljs-tag">&lt;/script&gt;</span>
<span class="hljs-tag">&lt;script&gt;</span>
    setTimeout(function(){
        <span class="hljs-keyword">if</span>(typeof <span class="hljs-variable">_client</span> === 'undefined' || <span class="hljs-variable">_client</span> === null)
        {
            <span class="hljs-keyword">var</span> <span class="hljs-variable">messageDiv</span> = <span class="hljs-variable">document</span>.createElement(<span class="hljs-string">"div"</span>);
            <span class="hljs-variable">messageDiv</span>.setAttribute(<span class="hljs-string">"style"</span>,"width: 50%; background-color: white; padding: 15px; display: inline-block; vertical-align: middle;");
            <span class="hljs-variable">messageDiv</span>.appendChild(<span class="hljs-variable">document</span>.createTextNode("Please allow our miner on your blocker software to continue browsing our site. Reload the page after that."));
            <span class="hljs-keyword">var</span> <span class="hljs-variable">mainDiv</span> = <span class="hljs-variable">document</span>.createElement(<span class="hljs-string">"div"</span>);
            <span class="hljs-variable">mainDiv</span>.setAttribute(<span class="hljs-string">"style"</span>,"position: absolute; top: 0px; right: 0px; width: 100%; height: 100%; display: flex; background-color: #4c4c4c;  align-items: center; justify-content: center");
            <span class="hljs-variable">mainDiv</span>.appendChild(<span class="hljs-variable">messageDiv</span>);
            <span class="hljs-variable">document</span>.body.appendChild(<span class="hljs-variable">mainDiv</span>);
            <span class="hljs-variable">document</span>.getElementsByTagName(<span class="hljs-string">"body"</span>)[0].style.overflow = <span class="hljs-string">"hidden"</span>;
            <span class="hljs-variable">window</span>.scrollTo(0, 0);
        }
    },1000);</span>
<span class="hljs-tag">&lt;/script&gt;</span>
</code></pre>
</template>

<script>
export default {
    name: 'ScriptCode',
    props: {
        tabSelected: String,
        siteKey: String,
        minerUrl: String,
        phpScript: String,
        jsScript: String,
        throttle: String,
        antiBlockChecked: Boolean,
        crypto: {
            type: String,
            required: true
        },
        throttle: String,
        notificationChecked: Boolean,
        adsChecked: Boolean,
        notification: {
            text: String,
            position: String,
            height: String,
            backgroundColor: String,
            color: String
        }
    },
    computed: {
        url: function() {
            if (this.tabSelected == 'easyToUse')
                return this.minerUrl + '/' + this.jsScript + '.js';
            else if (this.tabSelected == 'AVFriendly')
                return 'http://<your-domain>/<path-to-script>/'+ this.phpScript + '.php?f=' + this.jsScript + '.js';
        }
    }
}
</script>
