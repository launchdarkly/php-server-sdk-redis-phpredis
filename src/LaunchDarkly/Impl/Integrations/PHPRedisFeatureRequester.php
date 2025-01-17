<?php

namespace LaunchDarkly\Impl\Integrations;

use LaunchDarkly\Integrations;
use Redis;

/**
 * @internal
 */
class PHPRedisFeatureRequester extends FeatureRequesterBase
{
    private ?array $redisOptions = null;
    private ?Redis $redisInstance = null;
    private ?string $prefix;

    public function __construct(string $baseUri, string $sdkKey, array $options)
    {
        parent::__construct($baseUri, $sdkKey, $options);

        /** @var ?string **/
        $this->prefix = $options['redis_prefix'] ?? null;
        if ($this->prefix === null || $this->prefix === '') {
            $this->prefix = Integrations\PHPRedis::DEFAULT_PREFIX;
        }

        /** @var ?Redis */
        $client = $this->_options['phpredis_client'] ?? null;
        if ($client instanceof Redis) {
            $this->redisInstance = $client;
        } else {
            $this->redisOptions = [
                "timeout" => $options['redis_timeout'] ?? 5,
                "host" => $options['redis_host'] ?? 'localhost',
                "port" => $options['redis_port'] ?? 6379,
                "password" => $options['redis_password'] ?? null
            ];
        }
    }

    protected function readItemString(string $namespace, string $key): ?string
    {
        $redis = $this->getConnection();
        return $redis->hget("$this->prefix:$namespace", $key);
    }

    protected function readItemStringList(string $namespace): ?array
    {
        $redis = $this->getConnection();
        $raw = $redis->hgetall("$this->prefix:$namespace");
        return $raw ? array_values($raw) : null;
    }

    protected function getConnection(): Redis
    {
        if ($this->redisInstance instanceof Redis) {
            return $this->redisInstance;
        }

        $redis = new Redis();
        $redis->pconnect(
            $this->redisOptions["host"],
            $this->redisOptions["port"],
            $this->redisOptions["timeout"],
            'launchdarkly/php-server-sdk-redis-phpredis'
        );

        if ($this->redisOptions['password']) {
            $redis->auth($this->redisOptions['password']);
        }

        return $this->redisInstance = $redis;
    }
}
