# Contributing to this project

LaunchDarkly has published an [SDK contributor's guide](https://docs.launchdarkly.com/docs/sdk-contributors-guide) that provides a detailed explanation of how our SDKs work. See below for additional information on how to contribute to this SDK.

## Submitting bug reports and feature requests
 
The LaunchDarkly SDK team monitors the [issue tracker](https://github.com/launchdarkly/php-server-sdk-redis-phpredis/issues) in this repository. Bug reports and feature requests specific to this SDK should be filed in this issue tracker. The SDK team will respond to all newly filed issues within two business days.

## Submitting pull requests
 
We encourage pull requests and other contributions from the community. Before submitting pull requests, ensure that all temporary or unintended code is removed. Don't worry about adding reviewers to the pull request; the LaunchDarkly SDK team will add themselves. The SDK team will acknowledge all pull requests within two business days.

## Build instructions

### Prerequisites

The project uses [Composer](https://getcomposer.org/) for managing dependencies.

### Installing dependencies

Run `composer install` or `php composer.phar install` (depending on how you have set up Composer) in the project root directory.

### Testing

To run all unit tests:

```
./vendor/bin/phpunit
```

The tests expect you to have Redis running locally on the default port, 6379. One way to do this is with Docker:

```
docker run -p 6379:6379 redis
```

Also, to run the tests, your PHP environment must include the `phpredis` extension. See https://github.com/phpredis/phpredis for how to install this.
