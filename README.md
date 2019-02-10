CoinImp — JavaScript miner panel & landing site
===============================================

[![coverage report](https://gitlab.abchosting.org/abc-hosting/cryptocurrencies/coinimp/badges/v1.2.2/coverage.svg)](http://www.phpunit-v122.coinimp.cba.pl/)

This is the main CoinImp project, which aggregates info from various services, counts final reward for each user, and presents data to users via front-end or HTTP API.
It does also contain landing pages, admin panel and blog (see scope of responsibilities below). Despite of having many responsibilities, short name is "CoinImp panel" for convenience.

The panel has configurable fees and data poll rates (see [Configuration.md](docs/Configuration.md)).

It is written using PHP 7.2 (7.2.2 or higher is required) and Symfony 4 Flex framework. MySQL (MariaDB) is used as data storage solution.

These services are required for normal operation:
* [php](https://secure.php.net/downloads.php) 7.2.2+ and composer
* a webserver (you can use [symfony's](https://packagist.org/packages/symfony/web-server-bundle) from dev composer dependencies)
* [nodejs](https://nodejs.org/) 8+, npm 5.6+
* [mysql](https://www.mysql.com/downloads/) 5.6 or compatible DBMS

The following services are optional but are needed for some of features to work:

Service|Purpose
---|---
mail server|Sending emails: e.g. for completing the registration or changing user's email, and for Contact Us form requests
[rabbitmq](https://www.rabbitmq.com/download.html)|Communication between this project and coinimp-payment
[monerod](https://getmonero.org/resources/user-guides/vps_run_node.html)|Fetch Monero network stats like block reward or difficulty to calculate payouts
[coinimp-pool-aggregator](https://gitlab.abchosting.org/abc-hosting/cryptocurrencies/coinimp-pool-aggregator)|Collect and aggregate data from several mining pools to fetch user stats
[coinimp-payment](https://gitlab.abchosting.org/abc-hosting/cryptocurrencies/coinimp-payment)|Perform payouts and also for `app:wallet:balance` CLI command

You also need to launch `app:listen` CLI command in order to listen for coinimp-payment's payout pingbacks.

Scope of responsibilities
-------------------------
* landing pages;
* blog;
* user management (registration, authentication);
* user panel: Dashboard, Wallet page;
* admin panel;
* HTTP API (see [documentation](https://www.coinimp.com/documentation/http-api));
* storing and processing amount of mined hashes, payouts, and other user data;
* calculating payout amount (based on mined hashes, monero network stats, and configured fees), initiating payout process.

Installation
------------
1. Make sure the required services are up and running;
2. Clone this repository and checkout needed branch;
3. Create a dedicated clean MySQL (or compatible) database;
4. Install required dependencies: `composer install` for development environment or `composer install --no-dev` for staging/production environment;
5. [Configure](docs/Configuration.md) the settings in `config/parameters.yaml` file which will be created on the previous step;
6. Configure [your](https://symfony.com/doc/current/setup/web_server_configuration.html#content_wrapper) webserver to pass all requests to the `public/app.php` file.
 In dev environment you can just run [Symfony's](https://symfony.com/doc/current/setup/built_in_web_server.html) webserver via `server:start` console command;
7. Build frontend: `npm install` and `npm run dev` (or `npm run prod` in staging/production). Re-run `npm run dev` after making changes to frontend to reflect them in your local webserver;
8. Check if everything's up by visiting your page in the web browser, and you are done!

Contribution
------------
1. Take an issue from the [Redmine](https://redmine.abchosting.org/projects/coinimp/issues);
2. On top of the current version's branch (e.g. `v1.1.5`) create branch named `issue-xxxx` where `xxxx` is the issue number (e.g. `issue-3483`);
3. Create a merge request for your branch to the current version's branch in [GitLab](https://gitlab.abchosting.org/abc-hosting/cryptocurrencies/coinimp/merge_requests/new).

Unit tests
----------
You can run unit tests by running `vendor/bin/phpunit` with your PHP executable (e.g. like this `php vendor/bin/phpunit`).

There are also code coverage sites available for `master` and `v*` branches on sites like `http://www.phpunit-<branch name>.coinimp.cba.pl`.

FAQ
---
**What is console (CLI) command?**

You can not only interact with CoinImp via web browser, but also using command line interface.
 This feature is using [symfony/console](https://packagist.org/packages/symfony/console) package.
 To execute a command, run `bin/console` file with your PHP executable (e.g. like this: `php bin/console <command>`).
