<?php

namespace App\Entity;

use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\ConvocationStatusEnum;
use App\Repository\ConvocationRepository;

#[ORM\Entity(repositoryClass: ConvocationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Convocation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'convocations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Player $player = null;

    #[ORM\ManyToOne(inversedBy: 'convocations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\Column(length: 255, enumType:ConvocationStatusEnum::class)]
    private ?ConvocationStatusEnum $status = ConvocationStatusEnum::PENDING;

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

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getStatus(): ?ConvocationStatusEnum
    {
        return $this->status;
    }

    public function setStatus(ConvocationStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }
}
