<template>
    <div class="card wordpress-card">
        <div class="card-header tabs">
            <div class="tab selected">WordPress Plugin</div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12 text-center mt-3">
                <p>Get our <a :href="pluginDownloadURL" rel="nofollow">WordPress plugin</a>
                    with pre-configured site-keys for easy installation</p>
            </div>
            <div id="code-text-5" class="col-12">
            <div class="wordpress-instruction m-2">
                <ol>
                    <li>Download the <a :href="pluginDownloadURL" rel="nofollow">plugin</a> from your CoinIMP dashboard.</li>
                    <li>Go to the WordPress admin dashboard.</li>
                    <li>Go to <b>Plugins</b>, and then select <b>Add New</b>.</li>
                    <li>Click on <b>Upload Plugin</b>, search the .zip file you downloaded and click on <b>Install now</b>.</li>
                    <li>If the installation was successful, go to <b>Installed Plugins</b> and activate CoinIMP Miner.</li>
                    <li>Find <b>CoinIMP Miner</b> in <b>Settings</b> to customize the miner.</li>
                    <li>Choose the currency you want to mine, the CPU usage, customize the message for your site visitors, enable or disable the AV-Friendly solution and more.</li>
                    <li>Save Settings and the CoinIMP miner script will be automatically implemented.</li>
                </ol>
            </div>
            </div>
        </div>
    </div>
</template>
<script>
    import Routing from '../js/routing';

    export default {
        name: 'WordpressCard',
        data() {
            return {
                pluginDownloadURL: Routing.generate('wp_downloadplugin')
            }
        }
    }
</script>