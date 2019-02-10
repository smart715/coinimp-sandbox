Configuration
=============

The configuration file for administrators is `config/parameters.yaml`. It must not be included to the repository because it contains options which may vary from installation to installation. However, it is automatically created during `composer install` (if only `--no-scripts` option is not used) from `config/parameters.yaml.dist` template file. The explanation of each of the configurable options are avaiable in the .dist file.

Other .yaml configuration files in `config/` are used to properly set up Symfony components, services and bundles, and should not be changed by system administrators, but only by developers.