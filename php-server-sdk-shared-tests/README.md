# LaunchDarkly Server-Side PHP SDK Shared Test Code

[![CircleCI](https://circleci.com/gh/launchdarkly/php-server-sdk-shared-tests.svg?style=svg)](https://circleci.com/gh/launchdarkly/php-server-sdk-shared-tests)

This project provides support code for testing LaunchDarkly PHP SDK integrations. Feature store implementations, etc., should use this code whenever possible to ensure consistent test coverage and avoid repetition. An example of a project using this code is [php-server-sdk-redis](https://github.com/launchdarkly/php-server-sdk-redis).

The code is not published to Packagist, since it isn't of any use in any non-test context. Instead, it's meant to be used as a Git subtree. Add the subtree to your project like this:

    git remote add php-server-sdk-shared-tests git@github.com:launchdarkly/php-server-sdk-shared-tests.git
    git subtree add --squash --prefix=php-server-sdk-shared-tests/ php-server-sdk-shared-tests main

Then, in your project that uses the shared tests, add this to `composer.json`:

```json
    "repositories": [
        {
            "type": "path",
            "url": "php-server-sdk-shared-tests"
        },
    ],
```

And add this dependency:

```json
    "launchdarkly/server-sdk-shared-tests": "@dev"
```

To update the copy of `php-server-sdk-shared-tests` in your repository to reflect changes in this one:

    git subtree pull --squash --prefix=php-server-sdk-shared-tests/ php-server-sdk-shared-tests main
