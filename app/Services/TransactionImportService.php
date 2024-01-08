<?php

namespace App\Services;

use App\Contracts\AuthInterface;
use App\Contracts\EntityManagerServiceInterface;
use App\DataObjects\CsvTransactionData;
use App\Entities\Transaction;
use App\Exceptions\ValidationException;
use Clockwork\Clockwork;
use Clockwork\Request\LogLevel;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Csv\Statement;

class TransactionImportService
{
    public function __construct(
        private readonly CategoryService               $categoryService,
        private readonly TransactionService            $transactionService,
        private readonly AuthInterface                 $auth,
        private readonly EntityManagerServiceInterface $entityManager,
    )
    {
    }

    public function import(string $csvPath): void
    {
        $csvFile = Reader::createFromPath($csvPath, 'r');
        $csvFile->setHeaderOffset(0);
        $records = Statement::create()->process($csvFile);

        try {
            $this->entityManager->wrapInTransaction(function (EntityManagerInterface $em) use ($records) {
                $user = $this->auth->user();
                $databaseCategories = $this->categoryService->getAllKeyedWithNameArray();

                $queuedCategories = [];

                $count = 1;
                $batch = 500;

                foreach ($records as $record) {
                    /** @var CsvTransactionData $record */
                    $record = $this->formatCsvRecord($record);

                    $categoryFromCsv = strtolower($record->category);

                    if ($databaseCategories[$categoryFromCsv] ?? null) {
                        $category = $databaseCategories[$categoryFromCsv];
                    } else if (array_key_exists($categoryFromCsv, $queuedCategories)) {
                        $category = $queuedCategories[$categoryFromCsv];
                    } else if (!empty($categoryFromCsv)) {
                        $category = $this->categoryService->create($categoryFromCsv, $user);
                        $queuedCategories[strtolower($category->getName())] = $category;
                    } else {
                        $category = null;
                    }

                    $transaction = $this->transactionService->create(
                        $record->description,
                        $record->amount,
                        $record->date,
                        $user,
                        $category
                    );

                    $this->entityManager->sync($transaction, false);
                    $this->entityManager->sync($category, false);

                    unset($category);

                    if ($count % $batch === 0) {
                        $count = 1;

                        $this->entityManager->sync();
                        $this->entityManager->clear(Transaction::class);
                    } else {
                        $count++;
                    }
                }
                $this->entityManager->sync();
                $this->entityManager->clear();
            });
        } catch (\Throwable $e) {
            throw new ValidationException(['csv' => ['Something went wrong, try again later', $e->getMessage()]]);
        }
    }

    private function formatCsvRecord($record): CsvTransactionData
    {
        $record = array_change_key_case($record);
        [
            'date' => $date,
            'description' => $description,
            'category' => $category,
            'amount' => $amount
        ] = $record;

        $amount = str_replace(['$', ','], ['', ''], $amount);

        return new CsvTransactionData(
            new \DateTime($date),
            $description,
            $category,
            (float)$amount
        );
    }
}