<?php

namespace LaunchDarkly\Integrations;

use LaunchDarkly\Impl\Integrations;
use LaunchDarkly\Subsystems;
use LaunchDarkly\Subsystems\FeatureRequester;
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
     *   - `prefix`: a string to be prepended to all database keys; corresponds
     *   to the prefix setting in ld-relay
     *   - `apc_expiration`: expiration time in seconds for local caching, if `APCu` is installed
     * @return callable(string, string, array): FeatureRequester  an object to be stored in the `feature_requester` configuration property
     */
    public static function featureRequester(Redis $client, $options = []): callable
    {
        if (!extension_loaded('redis')) {
            throw new \RuntimeException("phpredis extension is required to use Integrations\\PHPRedis");
        }

        return function (string $baseUri, string $sdkKey, array $baseOptions) use ($client, $options): FeatureRequester {
            return new Integrations\PHPRedisFeatureRequester($client, $baseUri, $sdkKey, array_merge($baseOptions, $options));
        };
    }

    /**
     * Configures a big segments store instance backed by Redis.
     *
     * After calling this method, store its return value in the `store` property of your Big Segment configuration:
     *
     *     $store = LaunchDarkly\Integrations\PHPRedis::bigSegmentsStore(["prefix" => "env1"]);
     *     $bigSegmentsConfig = new LaunchDarkly\BigSegmentConfig(store: $store);
     *     $config = ["big_segments" => $bigSegmentsConfig];
     *     $client = new LDClient("sdk_key", $config);
     *
     * @param array<string,mixed> $options
     *   - `prefix`: a string to be prepended to all database keys; corresponds
     *   to the prefix setting in ld-relay
     */
    public static function bigSegmentsStore(Redis $client, LoggerInterface $logger, array $options = []): Subsystems\BigSegmentsStore
    {
        if (!extension_loaded('redis')) {
            throw new \RuntimeException("phpredis extension is required to use Integrations\\PHPRedis");
        }

        return new Integrations\PHPRedisBigSegmentsStore($client, $logger, $options);
    }
}
