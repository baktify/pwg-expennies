<?php

namespace App\Services;

use App\Contracts\AuthInterface;
use App\DataObjects\CsvTransactionData;
use App\Entities\Transaction;
use App\Exceptions\ValidationException;
use Clockwork\Clockwork;
use Clockwork\Request\LogLevel;
use Doctrine\ORM\EntityManagerInterface;

class TransactionImportService
{
    public function __construct(
        private readonly CategoryService        $categoryService,
        private readonly AuthInterface          $auth,
        private readonly Clockwork              $clockwork,
        private readonly EntityManagerInterface $em,
    )
    {
    }

    public function import(array $records): void
    {
        try {
            $this->clockwork->log(LogLevel::DEBUG, 'Memory usage before: ' . memory_get_usage());
            $this->clockwork->log(LogLevel::DEBUG, 'UoW before: ' . $this->em->getUnitOfWork()->size());

            $this->em->wrapInTransaction(function (EntityManagerInterface $em) use ($records) {
                $user = $this->auth->user();
                $databaseCategories = $this->categoryService->getAllKeyedWithNameArray();

                $queuedCategories = [];

                $count = 1;
                $batch = 250;

                /** @var CsvTransactionData $record */
                foreach ($records as $record) {
                    $transaction = new Transaction();
                    $transaction->setDate($record->date);
                    $transaction->setDescription($record->description);
                    $transaction->setAmount($record->amount);
                    $transaction->setUser($user);

                    $categoryFromCsv = strtolower($record->category);
                    $category = null;

                    if ($databaseCategories[$categoryFromCsv] ?? null) {
                        $category = $databaseCategories[$categoryFromCsv];
                    } else if (array_key_exists($categoryFromCsv, $queuedCategories)) {
                        $category = $queuedCategories[$categoryFromCsv];
                    } else if ($categoryFromCsv) {
                        $category = $this->categoryService->create($categoryFromCsv, $user);
                        $queuedCategories[strtolower($category->getName())] = $category;
                    }

                    $transaction->setCategory($category);
                    $em->persist($transaction);

                    unset($category);

                    if ($count % $batch === 0) {
                        $count = 1;

                        $em->flush();
                        $em->clear(Transaction::class);
                    } else {
                        $count++;
                    }
                }
                $em->flush();
                $em->clear();
            });

            $this->clockwork->log(LogLevel::DEBUG, 'Memory usage after: ' . memory_get_usage());
            $this->clockwork->log(LogLevel::DEBUG, 'UoW after: ' . $this->em->getUnitOfWork()->size());
        } catch (\Throwable $e) {
            throw new ValidationException(['csv' => ['Something went wrong, try again later']]);
        }
    }
}