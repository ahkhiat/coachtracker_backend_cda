<?php

namespace App\Entity;

use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CoachRepository;

#[ORM\Entity(repositoryClass: CoachRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Coach
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')] 
    #[ORM\Column(type: "uuid", unique: true)]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'coaches')]
    private ?Team $isCoachOf = null;

    #[ORM\OneToOne(inversedBy: 'coach', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;
    
    #[ORM\PrePersist] 
    public function generateUuid(): void
    {
        if ($this->id === null) {
            $this->id = Uuid::v4();
        }
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getIsCoachOf(): ?Team
    {
        return $this->isCoachOf;
    }

    public function setIsCoachOf(?Team $isCoachOf): static
    {
        $this->isCoachOf = $isCoachOf;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
