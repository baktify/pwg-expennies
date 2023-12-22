<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AuthInterface;
use App\Contracts\UserInterface;
use App\DataObjects\CsvTransactionData;
use App\DataObjects\DataTableQueryParamsData;
use App\Entities\Category;
use App\Entities\Receipt;
use App\Entities\Transaction;
use App\Exceptions\ValidationException;
use Clockwork\Clockwork;
use Clockwork\Request\LogLevel;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TransactionService
{
    public function __construct(
        private readonly EntityManager   $em,
        private readonly CategoryService $categoryService,
        private readonly AuthInterface   $auth,
        private readonly Clockwork       $clockwork,
    )
    {
    }

    public function getPaginatedTransactions(DataTableQueryParamsData $params): Paginator
    {
        $query = $this->em->createQueryBuilder()
            ->select('t', 'c', 'r')
            ->from(Transaction::class, 't')
            ->leftJoin('t.category', 'c')
            ->leftJoin('t.receipts', 'r')
            ->setFirstResult($params->offset)
            ->setMaxResults($params->limit);

        $allowedColumns = ['description', 'date', 'amount', 'category', 'createdAt', 'updatedAt'];
        $orderBy = in_array($params->orderBy, $allowedColumns) ? $params->orderBy : 'updatedAt';
        $orderDir = $params->orderDir === 'desc' ? 'desc' : 'asc';

        match ($orderBy) {
            'category' => $query->orderBy('c.name', $orderDir),
            default => $query->orderBy('t.' . $orderBy, $orderDir),
        };

        $searchTerm = str_replace(['%', '_'], ['\%', '\_'], $params->searchTerm);
        $query->where('t.description LIKE :description')
            ->orWhere('c.name LIKE :categoryName')
            ->setParameter(':description', '%' . $searchTerm . '%')
            ->setParameter(':categoryName', '%' . $searchTerm . '%');

        return new Paginator($query);
    }

    public function create(
        string        $description,
        float         $amount,
        DateTime      $date,
        UserInterface $user,
        ?Category     $category = null,
    ): Transaction
    {
        $transaction = new Transaction();
        $transaction->setDescription($description);
        $transaction->setAmount($amount);
        $transaction->setDate($date);
        $transaction->setUser($user);

        if ($category) {
            $transaction->setCategory($category);
        }

        $this->em->persist($transaction);
        $this->em->flush();

        return $transaction;
    }

    public function getDataTableMapper(): \Closure
    {
        return fn(Transaction $transaction) => [
            'id' => $transaction->getId(),
            'description' => $transaction->getDescription(),
            'date' => $transaction->getDate()->format('m/d/Y g:i A'),
            'amount' => $transaction->getAmount(),
            'createdAt' => $transaction->getCreatedAt()->format('m/d/Y g:i A'),
            'updatedAt' => $transaction->getUpdatedAt()->format('m/d/Y g:i A'),
            'category' => $transaction->getCategory()?->getName(),
            'receipts' => $transaction->getReceipts()->map(fn(Receipt $receipt) => [
                'id' => $receipt->getId(),
                'name' => $receipt->getFilename(),
            ])->toArray(),
        ];
    }

    public function toArray(
        Transaction $transaction,
        bool        $withTimestamps = true,
        bool        $withCategory = true,
        bool        $withUser = true,
    ): array
    {
        $structure = [
            'id' => $transaction->getId(),
            'description' => $transaction->getDescription(),
            'date' => $transaction->getDate()->format('Y-m-d H:i'),
            'amount' => $transaction->getAmount(),
        ];

        if ($withTimestamps) {
            $structure['createdAt'] = $transaction->getCreatedAt();
            $structure['updatedAt'] = $transaction->getUpdatedAt();
        }

        if ($withCategory) {
            $category = $transaction->getCategory();

            if ($category) {
                $structure['category'] = ['id' => $category->getId(), 'name' => $category->getName()];
            }
        }

        if ($withUser) {
            $structure['user'] = $transaction->getUser()?->getName();
        }

        return $structure;
    }

    public function getById(int $transactionId): ?Transaction
    {
        return $this->em->getRepository(Transaction::class)->find($transactionId);
    }

    public function delete(int $transactionId): bool
    {
        $transaction = $this->em->getRepository(Transaction::class)->find($transactionId);

        if (!$transaction) {
            return false;
        }

        $this->em->remove($transaction);
        $this->em->flush();

        return true;
    }

    public function update(int $id, array $data): Transaction
    {
        $transaction = $this->em->getRepository(Transaction::class)->find($id);

        $transaction->setDescription($data['description']);
        $transaction->setAmount((float)$data['amount']);
        $transaction->setDate(new DateTime($data['date']));

        if ($data['categoryId']) {
            $category = $this->categoryService->getById((int)$data['categoryId']);

            $transaction->setCategory($category);
        }

        $this->em->persist($transaction);
        $this->em->flush();

        return $transaction;
    }

    public function createFromArray(array $records): void
    {
        try {
            $this->clockwork->log(LogLevel::DEBUG, 'Memory usage before: ' . memory_get_usage());
            $this->clockwork->log(LogLevel::DEBUG, 'UoW before: ' . $this->em->getUnitOfWork()->size());

            $this->em->wrapInTransaction(function (EntityManager $em) use ($records) {
                $user = $this->auth->user();
                $databaseCategories = $this->categoryService->getAllKeyedNameArray();

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
                    }
                    else if (array_key_exists($categoryFromCsv, $queuedCategories)) {
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
            // TODO: Delete the $e->getMessage() later
            throw new ValidationException(['csv' => ['Something went wrong, try again later', $e->getMessage()]]);
        }
    }

    private function getUniqueCategories(array $records): array
    {
        $categories = [];

        /** @var CsvTransactionData $record */
        foreach ($records as $record) {
            $categories[] = $record->category;
        }

        $uniqueCategories = array_unique($categories);
        $categoriesFiltered = array_filter($uniqueCategories, fn($category) => !empty(trim($category)));
        $categoriesKeysToLower = array_map(fn($category) => strtolower($category), $categoriesFiltered);
        $categoriesReindexed = array_values($categoriesKeysToLower);

        return $categoriesReindexed;
    }
}