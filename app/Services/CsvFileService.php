<?php

declare(strict_types=1);

namespace App\Services;

use App\DataObjects\CsvTransactionData;
use App\Exceptions\ValidationException;
use League\Csv\Reader;
use League\Csv\Statement;

class CsvFileService
{
    public function parseTransactionFile(string $csvPath): array
    {
        $parsedRecords = [];

        $csvFile = Reader::createFromPath($csvPath, 'r');
        $csvFile->setHeaderOffset(0);

        $records = Statement::create()->process($csvFile);

        foreach ($records as $record) {
            try {
                $record = array_change_key_case($record, CASE_LOWER);
                [
                    'date' => $date,
                    'description' => $description,
                    'category' => $category,
                    'amount' => $amount
                ] = $record;

                $amount = str_replace(['$', ','], ['', ''], $amount);
            } catch (\Throwable) {
                throw new ValidationException([
                    'csv' => ['Csv file should contain date, description, category, amount columns']
                ]);
            }

            $parsedRecords[] = new CsvTransactionData(
                new \DateTime($date),
                $description,
                $category,
                (float)$amount
            );
        }

        return $parsedRecords;
    }
}