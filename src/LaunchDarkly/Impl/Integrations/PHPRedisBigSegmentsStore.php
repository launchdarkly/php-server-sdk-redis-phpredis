<?php

declare(strict_types=1);

namespace LaunchDarkly\Impl\Integrations;

use Exception;
use LaunchDarkly\Integrations;
use LaunchDarkly\Subsystems;
use LaunchDarkly\Types;
use Psr\Log\LoggerInterface;
use Redis;

/**
 * Internal implementation of the php-redis BigSegmentsStore interface.
 */
class PHPRedisBigSegmentsStore implements Subsystems\BigSegmentsStore
{
    private const KEY_LAST_UP_TO_DATE = ':big_segments_synchronized_on';
    private const KEY_CONTEXT_INCLUDE = ':big_segment_include:';
    private const KEY_CONTEXT_EXCLUDE = ':big_segment_exclude:';

    private readonly string $prefix;

    /**
     * @param array<string,mixed> $options
     *   - `prefix`: namespace prefix to add to all hash keys
     */
    public function __construct(
        private readonly Redis $connection,
        private readonly LoggerInterface $logger,
        readonly array $options = []
    ) {
        /** @var string */
        $this->prefix = $options['prefix'] ?? Integrations\PHPRedis::DEFAULT_PREFIX;
    }

    public function getMetadata(): Types\BigSegmentsStoreMetadata
    {
        try {
            /** @var string|false */
            $lastUpToDate = $this->connection->get($this->prefix . self::KEY_LAST_UP_TO_DATE);
        } catch (Exception $e) {
            $this->logger->warning('Error getting last-up-to-date time from Redis', ['exception' => $e->getMessage()]);
            return new Types\BigSegmentsStoreMetadata(lastUpToDate: null);
        }

        if ($lastUpToDate === false) {
            $lastUpToDate = null;
        } else {
            $lastUpToDate = (int)$lastUpToDate;
        }

        return new Types\BigSegmentsStoreMetadata(lastUpToDate: $lastUpToDate);
    }

    public function getMembership(string $contextHash): ?array
    {
        try {
            /** @var array<string> */
            $includeRefs = $this->connection->sMembers($this->prefix . self::KEY_CONTEXT_INCLUDE . $contextHash);
            /** @var array<string> */
            $excludeRefs = $this->connection->sMembers($this->prefix . self::KEY_CONTEXT_EXCLUDE . $contextHash);
        } catch (Exception $e) {
            $this->logger->warning('Error getting big segments membership from Redis', ['exception' => $e->getMessage()]);
            return null;
        }

        $membership = [];
        foreach ($excludeRefs as $ref) {
            $membership[$ref] = false;
        }

        foreach ($includeRefs as $ref) {
            $membership[$ref] = true;
        }

        return $membership;
    }
}
