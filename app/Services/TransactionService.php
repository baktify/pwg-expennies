<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\DataObjects\CsvTransactionData;
use App\DataObjects\DataTableQueryParamsData;
use App\Entities\Category;
use App\Entities\Receipt;
use App\Entities\Transaction;
use App\Entities\User;
use DateTime;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TransactionService
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManager)
    {
    }

    public function getPaginatedTransactions(DataTableQueryParamsData $params): Paginator
    {
        $query = $this->entityManager->createQueryBuilder()
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
        User $user,
        ?Category     $category = null,
    ): Transaction
    {
        $transaction = new Transaction();
        $transaction->setDescription($description);
        $transaction->setAmount($amount);
        $transaction->setDate($date);
        $transaction->setUser($user);

        $transaction->setCategory($category);

        return $transaction;
    }

    public function getDataTableMapper(): \Closure
    {
        return fn(Transaction $transaction) => [
            'id' => $transaction->getId(),
            'isReviewed' => $transaction->isReviewed(),
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
                $structure['category'] = [
                    'id' => $category->getId(),
                    'name' => $category->getName()
                ];
            }
        }

        if ($withUser) {
            $structure['user'] = $transaction->getUser()?->getName();
        }

        return $structure;
    }

    public function getById(int $transactionId): ?Transaction
    {
        return $this->entityManager->getRepository(Transaction::class)->find($transactionId);
    }

    public function update(Transaction $transaction, array $data, ?Category $category = null): Transaction
    {
        $transaction->setDescription($data['description']);
        $transaction->setAmount((float)$data['amount']);
        $transaction->setDate(new DateTime($data['date']));

        $transaction->setCategory($category);

        return $transaction;
    }

    public function toggleReview(Transaction $transaction): void
    {
        $transaction->setReviewed(!$transaction->isReviewed());
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

    public function getTotals(\DateTime $startDate, \DateTime $endDate): array
    {
        // TODO: Implement

        return ['net' => 800, 'income' => 3000, 'expense' => 2200];
    }

    public function getRecentTransactions(int $limit): array
    {
        // TODO: Implement

        return [];
    }

    public function getMonthlySummary(int $year): array
    {
        // TODO: Implement

        return [
            ['income' => 1500, 'expense' => 1100, 'm' => '3'],
            ['income' => 2000, 'expense' => 1800, 'm' => '4'],
            ['income' => 2500, 'expense' => 1900, 'm' => '5'],
            ['income' => 2600, 'expense' => 1950, 'm' => '6'],
            ['income' => 3000, 'expense' => 2200, 'm' => '7'],
        ];
    }
}