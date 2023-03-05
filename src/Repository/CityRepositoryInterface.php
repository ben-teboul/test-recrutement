<?php

namespace App\Repository;

interface CityRepositoryInterface
{

    public function fetchByDepartmentId(int $departmentId): array;
}