<?php

namespace App\Repository;

use App\Entity\City;

final class CityRepository implements CityRepositoryInterface
{
    private array $dataByDepartmentId = [];

    public function __construct(string $filePath)
    {
        $handle = fopen($filePath, 'r');
        fgetcsv($handle); // Remove header
        while (($row = fgetcsv($handle)) !== false) {
            if (array_key_exists($row[1], $this->dataByDepartmentId) === false) {
                $this->dataByDepartmentId[$row[1]] = [];
            }
            $this->dataByDepartmentId[$row[1]][] = [
                'id' => $row[0],
                'name' => $row[3],
                'slug' => $row[2]
            ];
        }
    }

    public function fetchByDepartmentId(int $departmentId): array
    {
        $cities = [];
        if (array_key_exists($departmentId, $this->dataByDepartmentId) === true) {
            $cities = [];
            foreach ($this->dataByDepartmentId[$departmentId] as $cityData) {
                $city = new City();
                $city->setId($cityData['id']);
                $city->setName($cityData['name']);
                $cities[] = $city;
            }
        }

        return $cities;
    }

    public function getAll(): \Traversable
    {
        foreach($this->dataByDepartmentId as $departmentId => $cities) {
            foreach ($cities as $cityData) {
                $city = new City();
                $city
                    ->setId($cityData['id'])
                    ->setDepartmentId($departmentId)
                    ->setName($cityData['name'])
                    ->setSlug($cityData['slug'])
                ;
                yield $city;
            }
        }
    }
}
