<?php

namespace LaunchDarkly\SharedTest\Tests;

use LaunchDarkly\FeatureRequester;
use LaunchDarkly\Impl\Model\FeatureFlag;
use LaunchDarkly\Impl\Model\Segment;
use LaunchDarkly\SharedTest\DatabaseFeatureRequesterTestBase;

class FakeDatabase
{
	public static $data = [];

	public static function getItem(string $prefix, string $namespace, string $key): ?array
	{
		$dataSet = self::$data[$prefix] ?? null;
		if ($dataSet) {
			$items = $dataSet[$namespace] ?? null;
			if ($items) {
				$json = $items[$key] ?? null;
				return $json ? json_decode($json, true) : null;
			}
		}
		return null;
	}

	public static function getAllItems(string $prefix, string $namespace): array
	{
		$itemsOut = [];
		$dataSet = self::$data[$prefix] ?? [];
		$items = $dataSet[$namespace] ?? [];
		foreach ($items as $key => $json) {
			$itemsOut[$key] = json_decode($json, true);
		}
		return $itemsOut;
	}

	public static function putSerializedItem(string $prefix, string $namespace, string $key, string $json): void
	{
		if (!isset(self::$data[$prefix])) {
    		self::$data[$prefix] = [];
    	}
    	if (!isset(self::$data[$prefix][$namespace])) {
    		self::$data[$prefix][$namespace] = [];
    	}
    	self::$data[$prefix][$namespace][$key] = $json;
	}
}

class FakeDatabaseFeatureRequester implements \LaunchDarkly\FeatureRequester
{
	private $prefix;

	public function __construct($prefix)
	{
		$this->prefix = $prefix;
	}

	public function getFeature(string $key): ?FeatureFlag
	{
		$json = FakeDatabase::getItem($this->prefix, 'features', $key);
		if ($json) {
			$flag = FeatureFlag::decode($json);
			return $flag->isDeleted() ? null : $flag;
		}
		return null;
	}

    public function getSegment(string $key): ?Segment
    {
    	$json = FakeDatabase::getItem($this->prefix, 'segments', $key);
		if ($json) {
			$segment = Segment::decode($json);
			return $segment->isDeleted() ? null : $segment;
		}
		return null;
    }

    public function getAllFeatures(): array
    {
    	$jsonList = FakeDatabase::getAllItems($this->prefix, 'features');
    	$itemsOut = [];
        foreach ($jsonList as $json) {
            $flag = FeatureFlag::decode($json);
            if ($flag && !$flag->isDeleted()) {
                $itemsOut[$flag->getKey()] = $flag;
            }
        }
        return $itemsOut;
    }
}

class DatabaseFeatureRequesterTestBaseTest extends DatabaseFeatureRequesterTestBase
{
	const DEFAULT_PREFIX = 'defaultprefix';

	protected function clearExistingData(?string $prefix): void
    {
    	FakeDatabase::$data[$this->actualPrefix($prefix)] = [ 'features' => [], 'segments' => [] ];
    }

	protected function makeRequester(?string $prefix): FeatureRequester
    {
    	return new FakeDatabaseFeatureRequester($this->actualPrefix($prefix));
    }

    protected function putSerializedItem(
    	?string $prefix,
    	string $namespace,
    	string $key,
    	int $version,
    	string $json): void
    {
    	FakeDatabase::putSerializedItem($this->actualPrefix($prefix), $namespace, $key, $json);
    }

    private function actualPrefix(?string $prefix): string
    {
    	return ($prefix === null || $prefix === '') ? self::DEFAULT_PREFIX : $prefix;
    }
}

?>