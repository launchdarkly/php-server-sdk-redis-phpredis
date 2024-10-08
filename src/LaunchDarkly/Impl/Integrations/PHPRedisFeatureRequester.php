<?php
namespace LaunchDarkly\Impl\Integrations;

use LaunchDarkly\Impl\Integrations\FeatureRequesterBase;

/**
 * @internal
 */
class PHPRedisFeatureRequester extends FeatureRequesterBase
{
    /** @var array */
    private $_redisOptions;
    /** @var \Redis */
    private $_redisInstance;
    /** @var string */
    private $_prefix;

    public function __construct($baseUri, $sdkKey, $options)
    {
        parent::__construct($baseUri, $sdkKey, $options);

        $this->_prefix = $options['redis_prefix'] ?? null;
        if ($this->_prefix === null || $this->_prefix === '') {
            $this->_prefix = 'launchdarkly';
        }

        $client = $this->_options['phpredis_client'] ?? null;
        if ($client instanceof \Redis) {
            $this->_redisInstance = $client;
        } else {
            $this->_redisOptions = [
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
        return $redis->hget("$this->_prefix:$namespace", $key);
    }

    protected function readItemStringList(string $namespace): ?array
    {
        $redis = $this->getConnection();
        $raw = $redis->hgetall("$this->_prefix:$namespace");
        return $raw ? array_values($raw) : null;
    }

    /**
     * @return \Redis
     */
    protected function getConnection()
    {
        if ($this->_redisInstance instanceof \Redis) {
            return $this->_redisInstance;
        }

        $redis = new \Redis();
        $redis->pconnect(
            $this->_redisOptions["host"],
            $this->_redisOptions["port"],
            $this->_redisOptions["timeout"],
            'launchdarkly/php-server-sdk-redis-phpredis'
        );

        if ($this->_redisOptions['password']) {
            $redis->auth($this->_redisOptions['password']);
        }

        return $this->_redisInstance = $redis;
    }
}
