<?php

namespace LaunchDarkly\Impl\Integrations\Tests;

use LaunchDarkly\Impl\Integrations\PHPRedisFeatureRequester;
use LaunchDarkly\Integrations\PHPRedis;
use LaunchDarkly\SharedTest\DatabaseFeatureRequesterTestBase;
use \Redis;

class PHPRedisFeatureRequesterWithClientTest extends DatabaseFeatureRequesterTestBase
{
    const CLIENT_PREFIX = 'clientprefix';

    /** @var ClientInterface */
    private static $redisClient;

    public static function setUpBeforeClass(): void
    {
        self::$redisClient = new \Redis();
        self::$redisClient->pconnect(
            'localhost',
            6379,
            null,
            'RedisFeatureRequesterWithClientTest'
        );
        self::$redisClient->setOption(\Redis::OPT_PREFIX, self::CLIENT_PREFIX);

        // Setting a prefix parameter on the Redis client causes it to prepend
        // that string to every key *in addition to* the other prefix that the SDK
        // integration is applying. This is done transparently so we do not need to
        // add CLIENT_PREFIX in putItem below. We're doing it so we can be sure
        // that the PHPRedisFeatureRequester really is using the same client we
        // passed to it; if it didn't, the tests would fail because putItem was
        // creating keys with both prefixes but PHPRedisFeatureRequester was
        // looking for keys with only one prefix.
    }

    protected function clearExistingData($prefix): void
    {
        $p = self::realPrefix($prefix);
        $keys = self::$redisClient->keys("$p:*");
        foreach ($keys as $key) {
            if (substr($key, 0, strlen(self::CLIENT_PREFIX)) === self::CLIENT_PREFIX) {
                // remove extra prefix from the queried keys because del() will re-add it
                $key = substr($key, strlen(self::CLIENT_PREFIX));
            }
            self::$redisClient->del($key);
        }
    }

    protected function makeRequester($prefix)
    {
        $factory = PHPRedis::featureRequester([
            'redis_prefix' => $prefix,
            'phpredis_client' => self::$redisClient
        ]);
        return $factory('', '', []);
    }

    protected function putSerializedItem($prefix, $namespace, $key, $version, $json): void
    {
        $p = self::realPrefix($prefix);
        self::$redisClient->hset("$p:$namespace", $key, $json);
    }

    private static function realPrefix($prefix)
    {
        if ($prefix === null || $prefix === '') {
            return 'launchdarkly';
        }
        return $prefix;
    }
}
