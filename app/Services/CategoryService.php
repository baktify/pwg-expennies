<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\Contracts\UserInterface;
use App\DataObjects\DataTableQueryParamsData;
use App\Entities\Category;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CategoryService
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManager)
    {
    }

    public function create(string $name, UserInterface $user): Category
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

    public function getByName(string $name): ?Category
    {
        return $this->entityManager->getRepository(Category::class)->findOneBy(['name' => $name]);
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

    public function getByNameOrNew(string $categoryName, UserInterface $user): ?Category
    {
        $category = $this->getByName($categoryName);

        if (!$category && !empty($categoryName)) {
            $category = $this->create($categoryName, $user);
        }

        return $category;
    }
}