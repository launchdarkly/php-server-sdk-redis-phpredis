<?php

namespace LaunchDarkly\Impl\Integrations\Tests\Impl\Integrations;

use LaunchDarkly\Impl\Model\FeatureFlag;
use LaunchDarkly\Impl\Model\Segment;
use LaunchDarkly\Integrations\PHPRedis;
use LaunchDarkly\Subsystems;
use LaunchDarkly\Subsystems\FeatureRequester;
use PHPUnit\Framework\TestCase;
use Redis;

class PHPRedisFeatureRequesterTest extends TestCase
{
    const TEST_PREFIX = 'testprefix';

    /**
     * @dataProvider prefixParameters
     */
    public function testGetFeature(?string $clientPrefix, ?string $prefix): void
    {
        $client = $this->client($clientPrefix);
        $this->clearExistingData($client);
        $fr = $this->makeRequester($client, $prefix);

        $flagKey = 'foo';
        $flagVersion = 10;
        $flagJson = self::makeFlagJson($flagKey, $flagVersion);
        $this->putSerializedItem($client, $prefix, 'features', $flagKey, $flagVersion, $flagJson);

        $fr = $this->makeRequester($client, $prefix);
        $flag = $fr->getFeature($flagKey);

        $this->assertInstanceOf(FeatureFlag::class, $flag);
        $this->assertEquals($flagVersion, $flag->getVersion());
    }

    /**
     * @dataProvider prefixParameters
     */
    public function testGetMissingFeature(?string $clientPrefix, ?string $prefix): void
    {
        $client = $this->client($clientPrefix);
        $this->clearExistingData($client);
        $fr = $this->makeRequester($client, $prefix);

        $flag = $fr->getFeature('unavailable');
        $this->assertNull($flag);
    }

    /**
     * @dataProvider prefixParameters
     */
    public function testGetDeletedFeature(?string $clientPrefix, ?string $prefix): void
    {
        $client = $this->client($clientPrefix);
        $this->clearExistingData($client);
        $fr = $this->makeRequester($client, $prefix);

        $flagKey = 'foo';
        $flagVersion = 10;
        $flagJson = self::makeFlagJson($flagKey, $flagVersion, true);
        $this->putSerializedItem($client, $prefix, 'features', $flagKey, $flagVersion, $flagJson);

        $flag = $fr->getFeature($flagKey);

        $this->assertNull($flag);
    }

    /**
     * @dataProvider prefixParameters
     */
    public function testGetAllFeatures(?string $clientPrefix, ?string $prefix): void
    {
        $client = $this->client($clientPrefix);
        $this->clearExistingData($client);
        $fr = $this->makeRequester($client, $prefix);

        $flagKey1 = 'foo';
        $flagKey2 = 'bar';
        $flagKey3 = 'deleted';
        $flagVersion = 10;
        $flagJson1 = self::makeFlagJson($flagKey1, $flagVersion);
        $flagJson2 = self::makeFlagJson($flagKey2, $flagVersion);
        $flagJson3 = self::makeFlagJson($flagKey3, $flagVersion, true);

        $this->putSerializedItem($client, $prefix, 'features', $flagKey1, $flagVersion, $flagJson1);
        $this->putSerializedItem($client, $prefix, 'features', $flagKey2, $flagVersion, $flagJson2);
        $this->putSerializedItem($client, $prefix, 'features', $flagKey3, $flagVersion, $flagJson3);

        $flags = $fr->getAllFeatures();

        $this->assertEquals(2, count($flags));
        $flag1 = $flags[$flagKey1];
        $this->assertEquals($flagKey1, $flag1->getKey());
        $this->assertEquals($flagVersion, $flag1->getVersion());
        $flag2 = $flags[$flagKey2];
        $this->assertEquals($flagKey2, $flag2->getKey());
        $this->assertEquals($flagVersion, $flag2->getVersion());
    }

    /**
     * @dataProvider prefixParameters
     */
    public function testAllFeaturesWithEmptyStore(?string $clientPrefix, ?string $prefix): void
    {
        $client = $this->client($clientPrefix);
        $this->clearExistingData($client);
        $fr = $this->makeRequester($client, $prefix);

        $flags = $fr->getAllFeatures();
        $this->assertEquals([], $flags);
    }

    /**
     * @dataProvider prefixParameters
     */
    public function testGetSegment(?string $clientPrefix, ?string $prefix): void
    {
        $client = $this->client($clientPrefix);
        $this->clearExistingData($client);
        $fr = $this->makeRequester($client, $prefix);

        $segKey = 'foo';
        $segVersion = 10;
        $segJson = self::makeSegmentJson($segKey, $segVersion);
        $this->putSerializedItem($client, $prefix, 'segments', $segKey, $segVersion, $segJson);

        $segment = $fr->getSegment($segKey);

        $this->assertInstanceOf(Segment::class, $segment);
        $this->assertEquals($segVersion, $segment->getVersion());
    }

    /**
     * @dataProvider prefixParameters
     */
    public function testGetMissingSegment(?string $clientPrefix, ?string $prefix): void
    {
        $client = $this->client($clientPrefix);
        $this->clearExistingData($client);
        $fr = $this->makeRequester($client, $prefix);

        $segment = $fr->getSegment('unavailable');
        $this->assertNull($segment);
    }

    /**
     * @dataProvider prefixParameters
     */
    public function testGetDeletedSegment(?string $clientPrefix, ?string $prefix): void
    {
        $client = $this->client($clientPrefix);
        $this->clearExistingData($client);
        $fr = $this->makeRequester($client, $prefix);

        $segKey = 'foo';
        $segVersion = 10;
        $segJson = self::makeSegmentJson($segKey, $segVersion, true);
        $this->putSerializedItem($client, $prefix, 'segments', $segKey, $segVersion, $segJson);

        $segment = $fr->getSegment($segKey);

        $this->assertNull($segment);
    }

    public function testPrefixIndependence(): void
    {
        $prefix1 = 'prefix1';
        $prefix2 = 'prefix2';

        $client = $this->client(null);
        $this->clearExistingData($client);

        $flagKey = 'my-flag';
        $segmentKey = 'my-segment';
        $version0 = 10;
        $version1 = 11;
        $version2 = 12;
        $this->setupForPrefix($client, null, $flagKey, $segmentKey, $version0);
        $this->setupForPrefix($client, $prefix1, $flagKey, $segmentKey, $version1);
        $this->setupForPrefix($client, $prefix2, $flagKey, $segmentKey, $version2);

        $this->verifyForPrefix($this->makeRequester($client, null), $flagKey, $segmentKey, $version0);
        $this->verifyForPrefix($this->makeRequester($client, ''), $flagKey, $segmentKey, $version0);
        $this->verifyForPrefix($this->makeRequester($client, $prefix1), $flagKey, $segmentKey, $version1);
        $this->verifyForPrefix($this->makeRequester($client, $prefix2), $flagKey, $segmentKey, $version2);
    }

    private function clearExistingData(Redis $client): void
    {
        $client->flushAll();
    }

    private function makeRequester(Redis $client, ?string $prefix): Subsystems\FeatureRequester
    {
        $factory = PHPRedis::featureRequester($client, ['prefix' => $prefix]);
        return $factory('', '', []);
    }

    private function putSerializedItem(
        Redis $client,
        ?string $prefix,
        string $namespace,
        string $key,
        int $version,
        string $json
    ): void {
        $p = self::realPrefix($prefix);
        $client->hset("$p:$namespace", $key, $json);
    }

    private static function realPrefix(?string $prefix): string
    {
        if ($prefix === null || $prefix === '') {
            return 'launchdarkly';
        }
        return $prefix;
    }


    private function setupForPrefix(Redis $client, ?string $prefix, string $flagKey, string $segmentKey, int $flagVersion): void
    {
        $segmentVersion = $flagVersion * 2;
        $this->putSerializedItem(
            $client,
            $prefix,
            'features',
            $flagKey,
            $flagVersion,
            self::makeFlagJson($flagKey, $flagVersion)
        );
        $this->putSerializedItem(
            $client,
            $prefix,
            'segments',
            $segmentKey,
            $segmentVersion,
            self::makeSegmentJson($flagKey, $segmentVersion)
        );
    }

    private function verifyForPrefix(FeatureRequester $fr, string $flagKey, string $segmentKey, int $flagVersion): void
    {
        $segmentVersion = $flagVersion * 2;

        $flag = $fr->getFeature($flagKey);
        $this->assertNotNull($flag);
        $this->assertEquals($flagVersion, $flag->getVersion());

        $flags = $fr->getAllFeatures();
        $this->assertEquals(1, count($flags));
        $this->assertEquals($flagVersion, $flags[$flagKey]->getVersion());

        $segment = $fr->getSegment($segmentKey);
        $this->assertNotNull($segment);
        $this->assertEquals($segmentVersion, $segment->getVersion());
    }

    /**
     * @return array<array<?string>>
     */
    public function prefixParameters(): array
    {
        return [
            [self::TEST_PREFIX, self::TEST_PREFIX],
            [self::TEST_PREFIX, ''],
            [self::TEST_PREFIX, null],

            ['', self::TEST_PREFIX],
            ['', ''],
            ['', null],

            [null, self::TEST_PREFIX],
            [null, ''],
            [null, null],
        ];
    }

    private static function makeFlagJson(string $key, int $version, bool $deleted = false): string|bool
    {
        return json_encode([
            'key' => $key,
            'version' => $version,
            'on' => true,
            'prerequisites' => [],
            'salt' => '',
            'targets' => [],
            'rules' => [],
            'fallthrough' => [
                'variation' => 0,
            ],
            'offVariation' => null,
            'variations' => [
                true,
                false,
            ],
            'deleted' => $deleted
        ]);
    }

    private static function makeSegmentJson(string $key, int $version, bool $deleted = false): string|bool
    {
        return json_encode([
            'key' => $key,
            'version' => $version,
            'included' => [],
            'excluded' => [],
            'rules' => [],
            'salt' => '',
            'deleted' => $deleted
        ]);
    }

    private function client(?string $prefix): Redis
    {
        $client = new Redis();
        $client->pconnect(
            'localhost',
            6379,
            0,
            self::class,
        );

        if ($prefix !== null) {
            $client->setOption(Redis::OPT_PREFIX, $prefix);
        }

        return $client;
    }
}
