<?php

namespace App\DataObjects;

class DataTableQueryParamsData
{
    public function __construct(
        public readonly int    $draw,
        public readonly int    $offset,
        public readonly int    $limit,
        public readonly string $orderBy,
        public readonly string $orderDir,
        public readonly string $searchTerm,
    )
    {
    }
}