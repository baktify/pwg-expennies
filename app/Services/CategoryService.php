<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\Entities\Transaction;
use App\Entities\User;
use App\DataObjects\DataTableQueryParamsData;
use App\Entities\Category;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\SimpleCache\CacheInterface;

class CategoryService
{
    public function __construct(
        private readonly EntityManagerServiceInterface $entityManager,
        private readonly CacheInterface                $cache,
    )
    {
    }

    public function create(string $name, User $user): Category
    {
        $category = new Category();
        $category->setName($name);
        $category->setUser($user);

        return $category;
    }

    public function getAll(): array
    {
        return $this->entityManager->getRepository(Category::class)->findAll();
    }

    public function getAllKeyedWithNameArray(): array
    {
        $categories = $this->getAll();
        $categoriesMap = [];

        foreach ($categories as $category) {
            $categoriesMap[strtolower($category->getName())] = $category;
        }

        return $categoriesMap;
    }

    public function getById(int $id): ?Category
    {
        return $this->entityManager->getRepository(Category::class)->find($id);
    }

    public function update(Category $category, string $name): Category
    {
        $category->setName($name);

        return $category;
    }

    public function getPaginatedCategories(DataTableQueryParamsData $params): Paginator
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from(Category::class, 'c')
            ->setFirstResult($params->offset)
            ->setMaxResults($params->limit);

        $searchTerm = str_replace(['%', '_'], ['\%', '\_'], $params->searchTerm);
        $query->where('c.name LIKE :name')
            ->setParameter(':name', '%' . $searchTerm . '%');

        $allowedColumns = ['name', 'createdAt', 'updatedAt'];
        $orderBy = in_array($params->orderBy, $allowedColumns) ? $params->orderBy : 'updatedAt';

        $orderDir = strtolower($params->orderDir) === 'desc' ? 'desc' : 'asc';

        $query->orderBy('c.' . $orderBy, $orderDir);

        return new Paginator($query);
    }

    public function categoryMapper(bool $withTimestamps = false): callable
    {
        return function (Category $category) use ($withTimestamps) {
            $mapper = [
                'id' => $category->getId(),
                'name' => $category->getName(),
            ];

            if ($withTimestamps) {
                $mapper['createdAt'] = $category->getCreatedAt()->format('d/m/Y g:i A');
                $mapper['updatedAt'] = $category->getUpdatedAt()->format('d/m/Y g:i A');
            }

            return $mapper;
        };
    }

    public function getTopSpendingCategories(int $limit): array
    {
        // Get all categories with transactions
        $categories = $this->entityManager->createQueryBuilder()
            ->select('c', 't')
            ->from(Category::class, 'c')
            ->join('c.transactions', 't')
            ->getQuery()
            ->getResult();
        dd($categories);
        // Foreach category in categories -> calculate transactions expense
        $categoriesResult = [];
        for ($i = 0; $i < count($categories); $i++) {

            /** @var Category $category */
            $category = $categories[$i];
            $categoriesResult[$i]['name'] = $category->getName();
            $categoriesResult[$i]['amount'] = 0;

            /** @var Transaction $transaction */
            foreach ($category->getTransactions() as $transaction) {
                $categoriesResult[$i]['amount'] += $transaction->getAmount();
            }
        }

        // Sort categories
        uasort($categoriesResult, fn ($a, $b) => $a['amount'] <=> $b['amount']);

        return array_slice($categoriesResult, 0, $limit);
    }
}