<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\UserInterface;
use App\DataObjects\DataTableQueryParamsData;
use App\Entities\Category;
use App\Entities\Transaction;
use App\Entities\User;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TransactionService
{
    public function __construct(
        private readonly EntityManager   $em,
        private readonly CategoryService $categoryService,
    )
    {
    }

    public function getPaginatedTransactions(DataTableQueryParamsData $params): Paginator
    {
        $query = $this->em->createQueryBuilder()
            ->select('t', 'c', 'u')
            ->from(Transaction::class, 't')
            ->join('t.user', 'u')
            ->join('t.category', 'c')
            ->setFirstResult($params->offset)
            ->setMaxResults($params->limit);

        $allowedColumns = ['description', 'date', 'amount', 'user', 'category', 'createdAt', 'updatedAt'];
        $orderBy = in_array($params->orderBy, $allowedColumns) ? $params->orderBy : 'updatedAt';
        $orderDir = $params->orderDir === 'desc' ? 'desc' : 'asc';
        $query->orderBy('t.' . $orderBy, $orderDir);

        match ($orderBy) {
            'category' => $query->orderBy('c.name', $orderDir),
            'user' => $query->orderBy('u.name', $orderDir),
            default => $query->orderBy('t.' . $orderBy, $orderDir),
        };

        $searchTerm = str_replace(['%', '_'], ['\%', '\_'], $params->searchTerm);
        $query->where('t.description LIKE :description')
            ->orWhere('u.name LIKE :userName')
            ->orWhere('c.name LIKE :categoryName')
            ->setParameter(':description', '%' . $searchTerm . '%')
            ->setParameter(':userName', '%' . $searchTerm . '%')
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
            'user' => $transaction->getUser()?->getName(),
            'category' => $transaction->getCategory()?->getName(),
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

    public function getOne(int $transactionId): ?Transaction
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
}