<?php

namespace App\traits;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks()]
trait TimeStampTrait
{
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
    }
}