<?php

namespace units\App\Repository;

use App\Entity\City;
use App\Repository\CityRepository;
use PHPUnit\Framework\TestCase;

class CityRepositoryTest extends TestCase
{
    private CityRepository $instance;

    public function setUp(): void
    {
        $this->instance = new CityRepository(dirname(__FILE__) . '/../../../../db/cities.csv');
    }

    public function testFetchByDepartmentId()
    {
        $this->assertInstanceOf(
            CityRepository::class,
            $this->instance
        );
    }
    public function testFetchByDepartmentIdReturnsExpectedCities(){
        $cities = $this->instance->fetchByDepartmentId(105);

        $this->assertCount(1, $cities);
        $this->assertInstanceOf(City::class, $cities[0]);
        $this->assertSame('Paris', $cities[0]->getName());
    }
    public function testFetchByDepartmentIdWithUnknownDepartmentIdReturnsEmptyArray()
    {
        $actualCities = $this->instance->fetchByDepartmentId(999);

        $this->assertEmpty($actualCities);
    }

    public function testFetchByDepartmentIdWithInvalidId(): void
    {
        $this->assertSame([], $this->instance->fetchByDepartmentId(999));
    }

    public function testFetchByDepartmentIdWithValidId(): void
    {
        $cities = $this->instance->fetchByDepartmentId(124);
        $this->assertInstanceOf(City::class, $cities[0]);
        $this->assertCount(47, $cities);
    }
}