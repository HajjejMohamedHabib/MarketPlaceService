<?php

namespace App\Entity;

use App\Repository\AdminActionRepository;
use App\traits\TimeStampTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminActionRepository::class)]
#[ORM\Table(name: "admin_action")]
#[ORM\HasLifecycleCallbacks()]
class AdminAction
{
    use TimeStampTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $action_type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActionType(): ?string
    {
        return $this->action_type;
    }

    public function setActionType(string $action_type): static
    {
        $this->action_type = $action_type;

        return $this;
    }
}
