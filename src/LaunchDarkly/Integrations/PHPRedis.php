<?php

namespace LaunchDarkly\Integrations;

use LaunchDarkly\Impl\Integrations;
use LaunchDarkly\Subsystems;
use Psr\Log\LoggerInterface;
use Redis;

/**
 * Integration with a Redis data store using the `phpredis` extension.
 */
class PHPRedis
{
    const DEFAULT_PREFIX = 'launchdarkly';

    /**
     * Configures an adapter for reading feature flag data from Redis using persistent connections.
     *
     * To use this method, you must have installed the `phpredis` extension. After calling this
     * method, store its return value in the `feature_requester` property of your client configuration:
     *
     *     $fr = LaunchDarkly\Integrations\PHPRedis::featureRequester([ "redis_prefix" => "env1" ]);
     *     $config = [ "feature_requester" => $fr ];
     *     $client = new LDClient("sdk_key", $config);
     *
     * For more about using LaunchDarkly with databases, see the
     * [SDK reference guide](https://docs.launchdarkly.com/sdk/features/storing-data).
     *
     * @param array $options  Configuration settings (can also be passed in the main client configuration):
     *   - `redis_host`: hostname of the Redis server; defaults to `localhost`
     *   - `redis_port`: port of the Redis server; defaults to 6379
     *   - `redis_password`: password to auth against the Redis server; optional
     *   - `redis_timeout`: connection timeout in seconds; defaults to 5
     *   - `redis_prefix`: a string to be prepended to all database keys; corresponds to the prefix
     * setting in ld-relay
     *   - `phpredis_client`: an already-configured Redis client instance if you wish to reuse one
     *   - `apc_expiration`: expiration time in seconds for local caching, if `APCu` is installed
     * @return mixed  an object to be stored in the `feature_requester` configuration property
     */
    public static function featureRequester($options = [])
    {
        if (!extension_loaded('redis')) {
            throw new \RuntimeException("phpredis extension is required to use Integrations\\PHPRedis");
        }

        return function (string $baseUri, string $sdkKey, array $baseOptions) use ($options) {
            return new Integrations\PHPRedisFeatureRequester($baseUri, $sdkKey, array_merge($baseOptions, $options));
        };
    }

    /**
     * @param array<string,mixed> $options
     *   - `prefix`: namespace prefix to add to all hash keys
     * @return callable(LoggerInterface, array): Subsystems\BigSegmentsStore
     */
    public static function bigSegmentsStore(Redis $client, array $options = []): callable
    {
        if (!extension_loaded('redis')) {
            throw new \RuntimeException("phpredis extension is required to use Integrations\\PHPRedis");
        }

        return function (LoggerInterface $logger, array $baseOptions) use ($client, $options): Subsystems\BigSegmentsStore {
            return new Integrations\PHPRedisBigSegmentsStore($client, $logger, array_merge($baseOptions, $options));
        };
    }
}
