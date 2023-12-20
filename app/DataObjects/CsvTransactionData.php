<?php

declare(strict_types=1);

namespace App\DataObjects;

class CsvTransactionData
{
    public function __construct(
        public readonly \DateTime $date,
        public readonly string $description,
        public readonly string $category,
        public readonly float $amount
    )
    {
    }
}