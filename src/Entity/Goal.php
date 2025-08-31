<?php

namespace App\Entity;

use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\GoalRepository;

#[ORM\Entity(repositoryClass: GoalRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Goal
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')] 
    #[ORM\Column(type: "uuid", unique: true)]
    private ?Uuid $id = null;

    #[ORM\Column]
    private ?int $minuteGoal = null;

    #[ORM\ManyToOne(inversedBy: 'goals')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Player $player = null;

    #[ORM\ManyToOne(inversedBy: 'goals')]
    #[ORM\JoinColumn(nullable: true)]
    private ?VisitorPlayer $visitorPlayer = null;

    #[ORM\ManyToOne(inversedBy: 'goals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

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

    public function getMinuteGoal(): ?int
    {
        return $this->minuteGoal;
    }

    public function setMinuteGoal(int $minuteGoal): static
    {
        $this->minuteGoal = $minuteGoal;

        return $this;
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
    public function getVisitorPlayer(): ?VisitorPlayer
    {
        return $this->visitorPlayer;
    }
    public function setVisitorPlayer(?VisitorPlayer $visitorPlayer): static
    {
        $this->visitorPlayer = $visitorPlayer;

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
}
