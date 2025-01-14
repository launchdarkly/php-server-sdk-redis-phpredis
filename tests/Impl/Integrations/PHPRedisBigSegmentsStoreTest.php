<?php

declare(strict_types=1);

namespace LaunchDarkly\Impl\Integrations\Tests\Impl\Integrations;

use LaunchDarkly\Impl\Integrations\PHPRedisBigSegmentsStore;
use PHPUnit\Framework;
use Psr\Log;
use Redis;

class PHPRedisBigSegmentsStoreTest extends Framework\TestCase
{
    public function testGetMetadata(): void
    {
        $now = time();
        $logger = new Log\NullLogger();

        $connection = new Redis();
        $connection->flushAll();
        $store = new PHPRedisBigSegmentsStore($connection, $logger, []);

        $metadata = $store->getMetadata();
        $this->assertNull($metadata->getLastUpToDate());
        $this->assertTrue($metadata->isStale(10));

        $connection->set('launchdarkly:big_segments_synchronized_on', $now);
        $metadata = $store->getMetadata();
        $this->assertEquals($now, $metadata->getLastUpToDate());
        $this->assertFalse($metadata->isStale(10));
    }

    public function testGetMetadataWithInvalidConfiguration(): void
    {
        $logger = new Log\NullLogger();

        $connection = new Redis(['port' => 33_333, 'connectTimeout' => 1]);
        $store = new PHPRedisBigSegmentsStore($connection, $logger, []);

        $metadata = $store->getMetadata();

        $this->assertNull($metadata->getLastUpToDate());
        $this->assertTrue($metadata->isStale(10));
    }

    public function testCanDetectInclusion(): void
    {
        $logger = new Log\NullLogger();

        $connection = new Redis();
        $connection->flushAll();
        $connection->sAdd('launchdarkly:big_segment_include:ctx', 'key1', 'key2');
        $connection->sAdd('launchdarkly:big_segment_exclude:ctx', 'key1', 'key3');

        $store = new PHPRedisBigSegmentsStore($connection, $logger, []);

        $membership = $store->getMembership('ctx') ?? [];

        $this->assertCount(3, $membership);
        $this->assertTrue($membership['key1']);
        $this->assertTrue($membership['key2']);
        $this->assertFalse($membership['key3']);
    }

    public function testCanDetectInclusionWithEmptyData(): void
    {
        $logger = new Log\NullLogger();

        $connection = new Redis();
        $connection->flushAll();

        $store = new PHPRedisBigSegmentsStore($connection, $logger, []);

        $membership = $store->getMembership('ctx');

        $this->assertNotNull($membership);
        $this->assertCount(0, $membership);
    }

    public function testCanDetectInclusionWithInvalidConfiguration(): void
    {
        $logger = new Log\NullLogger();

        $connection = new Redis(['port' => 33_333, 'connectTimeout' => 1]);
        $store = new PHPRedisBigSegmentsStore($connection, $logger, []);
        $membership = $store->getMembership('ctx');

        $this->assertNull($membership);
    }
}
