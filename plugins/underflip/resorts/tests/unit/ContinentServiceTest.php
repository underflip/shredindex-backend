<?php

namespace Underflip\Resorts\Tests\Classes;

use Underflip\Resorts\Classes\ContinentService;
use PHPUnit\Framework\TestCase;

class ContinentServiceTest extends TestCase
{
    private ContinentService $continentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->continentService = new ContinentService();
    }

    public function testContinentLookupReturnsArray()
    {
        $lookup = $this->continentService->continentLookup();
        $this->assertIsArray($lookup);
    }

    public function testGetContinentCodeForExistingCountries()
    {
        $this->assertEquals('EU', $this->continentService->getContinentCode('FR')); // France
        $this->assertEquals('NA', $this->continentService->getContinentCode('US')); // United States
        $this->assertEquals('AS', $this->continentService->getContinentCode('JP')); // Japan
        $this->assertEquals('AF', $this->continentService->getContinentCode('ZA')); // South Africa
        $this->assertEquals('OC', $this->continentService->getContinentCode('AU')); // Australia
        $this->assertEquals('SA', $this->continentService->getContinentCode('BR')); // Brazil
        // Add more test cases for other countries in your lookup
    }

    public function testGetContinentCodeForMissingCountryReturnsNull()
    {
        $this->assertNull($this->continentService->getContinentCode('XX')); // Invalid country code
    }

    public function testContinentLookupContainsKeyValuePairs()
    {
        $lookup = $this->continentService->continentLookup();

        // Check a few examples
        $this->assertArrayHasKey('FR', $lookup);
        $this->assertArrayHasKey('US', $lookup);
        $this->assertArrayHasKey('JP', $lookup);
        // Add more checks for other countries
    }
}
