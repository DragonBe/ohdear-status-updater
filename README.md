# Oh Dear! Status Updater for Slack

This codebase provides the logic to set up your own endpoint for updating [Oh Dear! Status Pages](https://ohdear.app/feature/status-pages) with custom status messages right from [Slack](https://www.slack.com).

It allows you to stay focussed on team collaboration while providing your customers essential information about service failures, maintenance or upcoming features.

## Requirements

In order to use this functionality, you must meet the following requirements:

- Oh Dear! Subscription
- Slack subscription with permission to install apps
- PHP 7.3 or higher if you want to run it yourself

### Oh Dear! subscription

This code updates the status pages of [Oh Dear!](https://www.ohdear.app/) so you or your company needs to have a subscription with their service. If you don't have one yet, you can register for a 10-day free trial at <https://ohdear.app/register>.

[![Oh Dear! Registration Page](docs/images/01_ohdear_register.png.png)](https://ohdear.app/register)

### Slack subscription

If you want to use this integration, a [Slack](https://www.slack.com) subscription is required where you have the ability to install apps for your Slack subscription. If you don't have one, you can sign up for free at <https://slack.com/get-started#/>.

[![Slack Registration Page](docs/images/02_slack_register.png)](https://slack.com/get-started#/)

### PHP 7.3 or higher

If you want to run this code base yourself make sure your server has at least PHP 7.3 installed. Higher is always good, but lower versions are not supported. You also need [composer](https://getcomposer.org) to install required dependencies.

## How to set the endpoint up yourself?

**WARNING:** DO NOT INSTALL DEV TOOLS ON PRODUCTION SYSTEMS!

```sh
cd /path/to/installation
git clone https://github.com/DragonBe/ohdear-status-updater.git
composer install --no-dev
```

Point your web server's document root to the `/web` directory of this project. In this example it would be `/path/to/installation/web`.

## How to activate the OhDear Status Updater in Slack?

## How to use the OhDear! Status Updater in Slack?

## Known problems and restrictions

There's a problem processing unicode characters in the messages, probably has to do with how I filter the incoming text. Keep it plain text for now as I will work to solve this issue soon.

## License and trademarks

This software is provided as-is and licensed under [MIT License](LICENSE.md). [Oh Dear!](https://www.ohdear.app) is a trademark of [Immutable VOF](https://www.immutable.be/), [Slack](https://www.slack.com) is a trademark of [Slack Technologies, Inc.](https://www.slack.com). This software is not part of Oh Dear! or Slack and they will not provide any support for this software. 