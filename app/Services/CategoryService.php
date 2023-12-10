<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\UserInterface;
use App\Entities\Category;
use Doctrine\ORM\EntityManager;

class CategoryService
{
    public function __construct(private readonly EntityManager $em)
    {
    }

    public function create(string $name, UserInterface $user): Category
    {
        $category = new Category();

        $category->setName($name);
        $category->setUser($user);

        $this->em->persist($category);
        $this->em->flush();

        return $category;
    }

    public function getAll(): array
    {
        return $this->em->getRepository(Category::class)->findAll();
    }

    public function delete(int $id): void
    {
        $category = $this->em->getRepository(Category::class)->find($id);

        $this->em->remove($category);
        $this->em->flush();
    }

    public function getById(int $id): ?Category
    {
        return $this->em->getRepository(Category::class)->find($id);
    }

    public function update(int $categoryId, string $name): Category
    {
        $category = $this->em->getRepository(Category::class)->find($categoryId);
        $category->setName($name);

        $this->em->flush();

        return $category;
    }
}