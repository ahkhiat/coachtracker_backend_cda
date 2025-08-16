<?php

namespace App\Entity;

use Symfony\Component\Uid\Uuid;
use App\Enum\PresenceStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PresenceRepository;

#[ORM\Entity(repositoryClass: PresenceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Presence
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')] 
    #[ORM\Column(type: "uuid", unique: true)]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'presences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Player $player = null;

    #[ORM\ManyToOne(inversedBy: 'presences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\Column(length: 255)]
    private ?PresenceStatusEnum $status = null;

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

    public function getStatus(): ?PresenceStatusEnum
    {
        return $this->status;
    }

    public function setStatus(PresenceStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }
}
