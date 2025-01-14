<?php

declare(strict_types=1);

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
        /** @var string|false */
        $result = $this->client->hget("$this->prefix:$namespace", $key);
        if ($result === false) {
            return null;
        }

        return $result;
    }

    protected function readItemStringList(string $namespace): ?array
    {
        /** @var ?array<string, string>|false */
        $raw = $this->client->hgetall("$this->prefix:$namespace");

        if ($raw === null || $raw === false) {
            return null;
        }

        return array_values($raw);
    }
}
