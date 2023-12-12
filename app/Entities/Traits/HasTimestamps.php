<?php

namespace App\Entities\Traits;

use App\Entities\User;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

trait HasTimestamps
{
    #[Column(name: 'created_at')]
    private \DateTime $createdAt;

    #[Column(name: 'updated_at')]
    private \DateTime $updatedAt;

    #[PrePersist]
    public function updateTimestamps(PrePersistEventArgs $args): void
    {
        if (!isset($this->createdAt)) {
            $this->setCreatedAt(new \DateTime());
        }

        if (!isset($this->updatedAt)) {
            $this->setUpdatedAt(new \DateTime());
        }
    }

    #[PreUpdate]
    public function updateUpdatedAt(PreUpdateEventArgs $args): void
    {
        $this->setUpdatedAt(new \DateTime());
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): User
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): User
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}