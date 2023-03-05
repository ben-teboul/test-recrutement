<?php

namespace App\Repository;

use App\Entity\City;
use Exception;

final class CitySQLiteRepository implements CityRepositoryInterface
{
    private \SQLite3 $sqlite;

    public function __construct(string $databaseFile)
    {
        $this->sqlite = new \SQLite3($databaseFile);
        $this->sqlite->busyTimeout(1000);
        $this->sqlite->enableExceptions(true);
    }

    public function fetchByDepartmentId(int $departmentId): array
    {
        $stmt = $this->sqlite->prepare('SELECT * FROM main.cities WHERE department_id=:id');
        $stmt->bindValue(':id', $departmentId, SQLITE3_INTEGER);
        $cities = $stmt->execute();

        $result = [];
        while ($row = $cities->fetchArray()) {
            $city = new City();
            $city->setName($row['name']);
            $city->setId($row['id']);
            $result[] = $city;
        }

        return $result;
    }

    /**
     * @param City[] $cities
     * @throws Exception
     */
    public function saveCities(\Traversable $cities)
    {
        // Démarre une transaction
        $this->sqlite->exec('BEGIN');
        try {
            $stmt = $this->sqlite->prepare(
                'INSERT INTO main.cities (id, department_id, slug, name) VALUES (:id, :department_id, :slug, :name)'
            );
            foreach ($cities as $city) {
                $stmt->bindValue(':id', $city->getId(), SQLITE3_INTEGER);
                $stmt->bindValue(':department_id', $city->getDepartmentId(), SQLITE3_INTEGER);
                $stmt->bindValue(':slug', $city->getSlug(), SQLITE3_TEXT);
                $stmt->bindValue(':name', $city->getName(), SQLITE3_TEXT);

                $stmt->execute();
            }
            //Valide la transaction
            $this->sqlite->exec('COMMIT');
        } catch (Exception $e) {
            // Annule la transaction en cas d'erreur
            $this->sqlite->exec('ROLLBACK');
            throw $e;
        }
    }

    public function truncate()
    {
        $this->sqlite->exec('DELETE FROM main.cities');
    }

    public function createTable()
    {
        $this->sqlite->exec('create table IF NOT EXISTS cities
            (
                id            int          not null
                    constraint cities_pk
                        primary key,
                department_id int          not null,
                slug          varchar(255) not null,
                name          varchar(255) not null
            );
        ');
    }
}
