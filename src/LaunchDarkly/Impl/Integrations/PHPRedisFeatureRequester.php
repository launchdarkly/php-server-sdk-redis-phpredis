<?php

namespace LaunchDarkly\Impl\Integrations;

use LaunchDarkly\Integrations;
use Redis;

/**
 * @internal
 */
class PHPRedisFeatureRequester extends FeatureRequesterBase
{
    private readonly string $prefix;

    public function __construct(
        private readonly Redis $client,
        string $baseUri,
        string $sdkKey,
        array $options
    ) {
        parent::__construct($baseUri, $sdkKey, $options);

        /** @var ?string */
        $prefix = $options['prefix'] ?? null;
        if ($prefix === null || $prefix === '') {
            $prefix = Integrations\PHPRedis::DEFAULT_PREFIX;
        }
        $this->prefix = $prefix;
    }

    protected function readItemString(string $namespace, string $key): ?string
    {
        return $this->client->hget("$this->prefix:$namespace", $key);
    }

    protected function readItemStringList(string $namespace): ?array
    {
        $raw = $this->client->hgetall("$this->prefix:$namespace");
        return $raw ? array_values($raw) : null;
    }
}
