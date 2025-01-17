# Change log

All notable changes to the project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org).

## [2.0.0](https://github.com/launchdarkly/php-server-sdk-redis-phpredis/compare/1.3.0...2.0.0) (2025-01-17)


### Features

* Add Big Segment store support ([#25](https://github.com/launchdarkly/php-server-sdk-redis-phpredis/issues/25)) ([9c6ca70](https://github.com/launchdarkly/php-server-sdk-redis-phpredis/commit/9c6ca7037936a4d4aa70b86102b1e8c83d183abf))
* Bump LaunchDarkly to 6.4.0+ ([25c899d](https://github.com/launchdarkly/php-server-sdk-redis-phpredis/commit/25c899d4beb667bd01ddaa155dd85b7359736365))
* Bump PHP to 8.1+ ([25c899d](https://github.com/launchdarkly/php-server-sdk-redis-phpredis/commit/25c899d4beb667bd01ddaa155dd85b7359736365))
* FeatureRequester requires configured Redis instance ([6bcfdbd](https://github.com/launchdarkly/php-server-sdk-redis-phpredis/commit/6bcfdbd33cce6776a0b54b7889d1638a88ac8859))


### Miscellaneous Chores

* Add missing documentation on big segments store method ([c087baa](https://github.com/launchdarkly/php-server-sdk-redis-phpredis/commit/c087baac38a6b457eef6496231c7f884c848555f))
* Add psalm and cs-checker ([6aaaaa4](https://github.com/launchdarkly/php-server-sdk-redis-phpredis/commit/6aaaaa4184b7f79e82e50517864e0e291a4cbd0b))
* Cleanup and strict types ([8c0f461](https://github.com/launchdarkly/php-server-sdk-redis-phpredis/commit/8c0f461670a1b6c2441b609be427b7a7b83c29d5))
* Fix doc generation action ([#22](https://github.com/launchdarkly/php-server-sdk-redis-phpredis/issues/22)) ([d6ba2c9](https://github.com/launchdarkly/php-server-sdk-redis-phpredis/commit/d6ba2c9a6d6253edbd4c3276a186a52cdea53a5c))
* Inline shared test package ([c0e4524](https://github.com/launchdarkly/php-server-sdk-redis-phpredis/commit/c0e45249a86c2e5d4b00f4b839370e6a3453abdc))
* Run `composer cs-fix` to improve style ([32cfaf1](https://github.com/launchdarkly/php-server-sdk-redis-phpredis/commit/32cfaf1fa1f95bbeb305748fc5cc83162ef7fb02))
* Update type hints to quiet psalm ([ccb15fe](https://github.com/launchdarkly/php-server-sdk-redis-phpredis/commit/ccb15fe290f37e19017973f9563f545bd58b5cc5))

## [1.3.0](https://github.com/launchdarkly/php-server-sdk-redis-phpredis/compare/1.2.0...1.3.0) (2024-10-10)


### Features

* Add ability to specify password for the redis server ([#20](https://github.com/launchdarkly/php-server-sdk-redis-phpredis/issues/20)) ([233e481](https://github.com/launchdarkly/php-server-sdk-redis-phpredis/commit/233e481d5f5ae5ac7db237a5c786298cfa39e05e))

## [1.2.0] - 2023-10-25
### Changed:
- Expanded SDK version support to v6

## [1.1.0] - 2022-12-28
### Changed:
- Relaxed the SDK version dependency constraint to allow this package to work with the upcoming v5.0.0 release of the LaunchDarkly PHP SDK.

## [1.0.0] - 2021-08-06
Initial release, for use with version 4.x of the LaunchDarkly PHP SDK.
