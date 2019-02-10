let Encore = require('@symfony/webpack-encore');

Encore
// the project directory where all compiled assets will be stored
    .setOutputPath('public/build/')

    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')

    // build scripts which are used as assets in templates
    .addEntry('main', './assets/js/main.js')
    .addEntry('index', './assets/js/landing.js')
    .addEntry('dashboard', './assets/js/dashboard.js')
    .addEntry('wallet', './assets/js/wallet.js')
    .addEntry('documentation', './assets/js/documentation.js')
    .addEntry('buy', './assets/js/buy.js')
    .addEntry('admin', './assets/js/admin/admin.js')
    .addEntry('jquery-password', './assets/js/admin/jquery.jquery-password-generator-plugin.min.js')
    .addEntry('confirmed', './assets/js/confirmed.js')
    .addEntry('token-sale', './assets/js/token-sale.js')
    .addEntry('profile', './assets/js/profile.js')

    // this script's purpose is solely to "touch" images so that webpack
    // will "notice" them and include to the build.
    .addEntry('assets', './assets/js/assets.js')

    // allow sass/scss files to be processed
    .enableSassLoader()

    .enablePostCssLoader()

    .enableVueLoader()

    .enableSourceMaps(!Encore.isProduction())

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // create hashed filenames (e.g. app.abc123.css)
    .enableVersioning()

    .configureFilenames({
        'images': 'images/[name].[hash:8].[ext]'
    })
;

// export the final configuration
let config = Encore.getWebpackConfig();
config.resolve.alias['vue$'] = Encore.isProduction() ? 'vue/dist/vue.min.js' : 'vue/dist/vue.js';

module.exports = config;
