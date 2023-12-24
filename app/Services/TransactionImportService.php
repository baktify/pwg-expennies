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

    public function import(array $records): void
    {
        try {
            $this->entityManager->wrapInTransaction(function (EntityManagerInterface $em) use ($records) {
                $user = $this->auth->user();
                $databaseCategories = $this->categoryService->getAllKeyedWithNameArray();

                $queuedCategories = [];

                $count = 1;
                $batch = 250;

                /** @var CsvTransactionData $record */
                foreach ($records as $record) {
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
}