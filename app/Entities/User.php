<?php

namespace App\Entities;

use App\Contracts\UserInterface;
use App\Entities\Traits\HasTimestamps;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table('users'), HasLifecycleCallbacks]
class User implements UserInterface
{
    use HasTimestamps;

    #[Id, GeneratedValue, Column(options: ['unsigned' => true])]
    private int $id;

    #[Column]
    private string $email;

    #[Column]
    private string $password;

    #[Column]
    private string $name;

    #[OneToMany(mappedBy: 'user', targetEntity: Category::class, cascade: ['persist', 'remove'])]
    private Collection $categories;

    #[OneToMany(mappedBy: 'user', targetEntity: Transaction::class, cascade: ['persist', 'remove'])]
    private Collection $transactions;

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): User
    {
        $this->name = $name;
        return $this;
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): User
    {
        $this->categories->add($category);

        return $this;
    }

    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): User
    {
        $this->transactions->add($transaction);

        return $this;
    }
}