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
        string    $description,
        float     $amount,
        DateTime  $date,
        User      $user,
        ?Category $category = null,
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

    public function getTotals(\DateTime $startDate, \DateTime $endDate): array
    {
        $x = $this->entityManager->getRepository(Transaction::class)->createQueryBuilder('t')
            ->select('t.amount')
            ->getQuery()
            ->getResult();

        $transactionAmounts = array_map(fn($item) => $item['amount'], $x);

        $totals = ['income' => 0, 'expense' => 0, 'net' => 0];
        foreach ($transactionAmounts as $amount) {
            if ($amount < 0) {
                $totals['expense'] += $amount;
            } else {
                $totals['income'] += $amount;
            }
            $totals['net'] += $amount;
        }

        return $totals;
    }

    public function getRecentTransactions(int $limit): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('t.description', 't.amount', 't.date', 'c.name as categoryName')
            ->from(Transaction::class, 't')
            ->leftJoin('t.category', 'c')
            ->setFirstResult(0)
            ->setMaxResults($limit)
            ->orderBy('t.date', 'DESC')
            ->getQuery()->getResult();
    }

    public function getMonthlySummary(int $year): array
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('t.amount', 't.date')
            ->from(Transaction::class, 't');

        $transactionsResult = [];
        for ($i = 1; $i <= 12; $i++) {
            $transactions = $query
                ->where('MONTH(t.date) = :month', 'YEAR(t.date) = :year')
                ->setParameter(':month', $i)
                ->setParameter(':year', $year)
                ->getQuery()->getResult();

            $transactionsResult[] = ['m' => $i] + $this->getTotalsFrom($transactions);
        }

        return $transactionsResult;
    }

    public function getTotalsFrom(array $transactions): array
    {
        $totals = ['income' => 0, 'expense' => 0];

        foreach ($transactions as $transaction) {
            $amount = (float) $transaction['amount'];

            if ($amount < 0) {
                $totals['expense'] += abs($amount);
            } else {
                $totals['income'] += $amount;
            }
        }

        return $totals;
    }
}