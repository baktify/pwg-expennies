<?php

namespace App\Entities\Traits;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\PrePersist;

trait HasTimestamps
{
    #[Column(name: 'created_at')]
    private \DateTime $createdAt;

    #[Column(name: 'updated_at')]
    private \DateTime $updatedAt;

    #[PrePersist]
    public function updateTimestamps(PrePersistEventArgs $args): void
    {
        if (!isset($this->created_at)) {
            $this->setCreatedAt(new \DateTime());
        }

        $this->setUpdatedAt(new \DateTime());
    }
}