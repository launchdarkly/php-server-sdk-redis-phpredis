<?php

namespace LaunchDarkly\SharedTest;

use LaunchDarkly\FeatureRequester;
use LaunchDarkly\Impl\Model\FeatureFlag;
use LaunchDarkly\Impl\Model\Segment;
use PHPUnit\Framework\TestCase;

/**
 * A base class providing standardized PHPUnit tests for database integrations.
 */
class DatabaseFeatureRequesterTestBase extends TestCase
{
    const TEST_PREFIX = 'testprefix';

    /**
     * Override this method to remove all data from the underlying data store for
     * the specified prefix string.
     * 
     * @param string $prefix the key prefix; may be empty/null, in which case we should
     *   use whatever the default prefix is for this database integration.
     */
    protected function clearExistingData(?string $prefix): void
    {
        throw new \RuntimeException("test class did not implement clearExistingData");
    }

    /**
     * Override this method to create an instance of the feature requester class being
     * tested.
     * 
     * @param string $prefix the key prefix; may be empty/null, in which case we should
     *   use whatever the default prefix is for this database integration.
     * 
     * @return an implementation instance
     */
    protected function makeRequester(?string $prefix): FeatureRequester
    {
        throw new \RuntimeException("test class did not implement makeRequester");
    }

    /**
     * Override this method to insert an item into the data store.
     *
     * @param string $prefix the key prefix; may be empty/null, in which case we should
     *   use whatever the default prefix is for this database integration.
     * @param string $namespace the namespace string, such as "features"
     * @param string $key the flag/segment key
     * @param int $version the version number
     * @param string $json the JSON data
     */
    protected function putSerializedItem(
        ?string $prefix,
        string $namespace,
        string $key,
        int $version,
        string $json): void
    {
        throw new \RuntimeException("test class did not implement putSerializedItem");
    }

    /**
     * @dataProvider prefixParameters
     */
    public function testGetFeature(?string $prefix)
    {
        $this->clearExistingData($prefix);
        $fr = $this->makeRequester($prefix);

        $flagKey = 'foo';
        $flagVersion = 10;
        $flagJson = self::makeFlagJson($flagKey, $flagVersion);
        $this->putSerializedItem($prefix, 'features', $flagKey, $flagVersion, $flagJson);

        $fr = $this->makeRequester($prefix);
        $flag = $fr->getFeature($flagKey);

        $this->assertInstanceOf(FeatureFlag::class, $flag);
        $this->assertEquals($flagVersion, $flag->getVersion());
    }

    /**
     * @dataProvider prefixParameters
     */
    public function testGetMissingFeature(?string $prefix)
    {
        $this->clearExistingData($prefix);
        $fr = $this->makeRequester($prefix);

        $flag = $fr->getFeature('unavailable');
        $this->assertNull($flag);
    }

    /**
     * @dataProvider prefixParameters
     */
    public function testGetDeletedFeature(?string $prefix)
    {
        $this->clearExistingData($prefix);
        $fr = $this->makeRequester($prefix);

        $flagKey = 'foo';
        $flagVersion = 10;
        $flagJson = self::makeFlagJson($flagKey, $flagVersion, true);
        $this->putSerializedItem($prefix, 'features', $flagKey, $flagVersion, $flagJson);

        $flag = $fr->getFeature($flagKey);

        $this->assertNull($flag);
    }

    /**
     * @dataProvider prefixParameters
     */
    public function testGetAllFeatures(?string $prefix)
    {
        $this->clearExistingData($prefix);
        $fr = $this->makeRequester($prefix);

        $flagKey1 = 'foo';
        $flagKey2 = 'bar';
        $flagKey3 = 'deleted';
        $flagVersion = 10;
        $flagJson1 = self::makeFlagJson($flagKey1, $flagVersion);
        $flagJson2 = self::makeFlagJson($flagKey2, $flagVersion);
        $flagJson3 = self::makeFlagJson($flagKey3, $flagVersion, true);

        $this->putSerializedItem($prefix, 'features', $flagKey1, $flagVersion, $flagJson1);
        $this->putSerializedItem($prefix, 'features', $flagKey2, $flagVersion, $flagJson2);
        $this->putSerializedItem($prefix, 'features', $flagKey3, $flagVersion, $flagJson3);

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
    public function testAllFeaturesWithEmptyStore(?string $prefix)
    {
        $this->clearExistingData($prefix);
        $fr = $this->makeRequester($prefix);

        $flags = $fr->getAllFeatures();
        $this->assertEquals(array(), $flags);
    }

    /**
     * @dataProvider prefixParameters
     */
    public function testGetSegment(?string $prefix)
    {
        $this->clearExistingData($prefix);
        $fr = $this->makeRequester($prefix);

        $segKey = 'foo';
        $segVersion = 10;
        $segJson = self::makeSegmentJson($segKey, $segVersion);
        $this->putSerializedItem($prefix, 'segments', $segKey, $segVersion, $segJson);

        $segment = $fr->getSegment($segKey);

        $this->assertInstanceOf(Segment::class, $segment);
        $this->assertEquals($segVersion, $segment->getVersion());
    }

    /**
     * @dataProvider prefixParameters
     */
    public function testGetMissingSegment(?string $prefix)
    {
        $this->clearExistingData($prefix);
        $fr = $this->makeRequester($prefix);

        $segment = $fr->getSegment('unavailable');
        $this->assertNull($segment);
    }

    /**
     * @dataProvider prefixParameters
     */
    public function testGetDeletedSegment(?string $prefix)
    {
        $this->clearExistingData($prefix);
        $fr = $this->makeRequester($prefix);

        $segKey = 'foo';
        $segVersion = 10;
        $segJson = self::makeSegmentJson($segKey, $segVersion, true);
        $this->putSerializedItem($prefix, 'segments', $segKey, $segVersion, $segJson);

        $segment = $fr->getSegment($segKey);

        $this->assertNull($segment);
    }

    public function testPrefixIndependence()
    {
        $prefix1 = 'prefix1';
        $prefix2 = 'prefix2';

        $this->clearExistingData(null);
        $this->clearExistingData($prefix1);
        $this->clearExistingData($prefix2);

        $flagKey = 'my-flag';
        $segmentKey = 'my-segment';
        $version0 = 10;
        $version1 = 11;
        $version2 = 12;
        $this->setupForPrefix(null, $flagKey, $segmentKey, $version0);
        $this->setupForPrefix($prefix1, $flagKey, $segmentKey, $version1);
        $this->setupForPrefix($prefix2, $flagKey, $segmentKey, $version2);

        $this->verifyForPrefix($this->makeRequester(null), $flagKey, $segmentKey, $version0);
        $this->verifyForPrefix($this->makeRequester(''), $flagKey, $segmentKey, $version0);
        $this->verifyForPrefix($this->makeRequester($prefix1), $flagKey, $segmentKey, $version1);
        $this->verifyForPrefix($this->makeRequester($prefix2), $flagKey, $segmentKey, $version2);
    }

    private function setupForPrefix(?string $prefix, string $flagKey, string $segmentKey, int $flagVersion)
    {
        $segmentVersion = $flagVersion * 2;
        $this->putSerializedItem($prefix, 'features', $flagKey, $flagVersion,
            self::makeFlagJson($flagKey, $flagVersion));
        $this->putSerializedItem($prefix, 'segments', $segmentKey, $segmentVersion,
            self::makeSegmentJson($flagKey, $segmentVersion));
    }

    private function verifyForPrefix(FeatureRequester $fr, string $flagKey, string $segmentKey, int $flagVersion)
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

    public function prefixParameters()
    {
        return [
            [ self::TEST_PREFIX ],
            [ '' ],
            [ null ]
        ];
    }

    private static function makeFlagJson(string $key, int $version, bool $deleted = false)
    {
        return json_encode(array(
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
        ));
    }

    private static function makeSegmentJson(string $key, int $version, bool $deleted = false)
    {
        return json_encode(array(
            'key' => $key,
            'version' => $version,
            'included' => array(),
            'excluded' => array(),
            'rules' => [],
            'salt' => '',
            'deleted' => $deleted
        ));
    }
}

?>