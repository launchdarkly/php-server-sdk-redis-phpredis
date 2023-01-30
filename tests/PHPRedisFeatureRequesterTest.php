<?php

namespace LaunchDarkly\Impl\Integrations\Tests;

use LaunchDarkly\Impl\Integrations\PHPRedisFeatureRequester;
use LaunchDarkly\Integrations\PHPRedis;
use LaunchDarkly\SharedTest\DatabaseFeatureRequesterTestBase;
use \Redis;

class PHPRedisFeatureRequesterTest extends DatabaseFeatureRequesterTestBase
{
    /** @var ClientInterface */
    private static $redisClient;

    public static function setUpBeforeClass(): void
    {
        self::$redisClient = new \Redis();
        self::$redisClient->pconnect(
            'localhost',
            6379,
            null,
            'RedisFeatureRequesterTest'
        );
    }

    protected function clearExistingData($prefix): void
    {
        $p = self::realPrefix($prefix);
        $keys = self::$redisClient->keys("$p:*");
        foreach ($keys as $key) {
            self::$redisClient->del($key);
        }
    }

    protected function makeRequester($prefix)
    {
        $factory = PHPRedis::featureRequester([
            'redis_prefix' => $prefix
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
