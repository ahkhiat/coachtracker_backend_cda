<?php

namespace App\Entity;

use App\Enum\EventStatusEnum;
use App\Enum\EventTypeEnum;
use App\Enum\SeasonEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\EventRepository;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $id = null;

    #[ORM\Column]
    private ?\DateTime $date = null;

    #[ORM\Column(length: 255)]
    private ?EventTypeEnum $eventType = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Team $team = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: true)]
    private ?VisitorTeam $visitorTeam = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Stadium $stadium = null;

    #[ORM\Column(length: 255)]
    private ?SeasonEnum $season = null;

    private ?bool $isRecurring = false;

    /**
     * @var Collection<int, Convocation>
     */
    #[ORM\OneToMany(targetEntity: Convocation::class, mappedBy: 'event')]
    private Collection $convocations;

    #[ORM\Column(length: 255, enumType: EventStatusEnum::class)]
    private ?EventStatusEnum $status = EventStatusEnum::UPCOMING;

    /**
     * @var Collection<int, Presence>
     */
    #[ORM\OneToMany(targetEntity: Presence::class, mappedBy: 'event')]
    private Collection $presences;

    /**
     * @var Collection<int, Goal>
     */
    #[ORM\OneToMany(targetEntity: Goal::class, mappedBy: 'event')]
    private Collection $goals;

    public function __construct()
    {
        $this->convocations = new ArrayCollection();
        $this->presences = new ArrayCollection();
        $this->goals = new ArrayCollection();
    }

    #[ORM\PrePersist] 
    public function generateUuid(): void
    {
        if ($this->id === null) {
            $this->id = Uuid::v4();
        }
    }

    public function __toString(): string
    {
        return sprintf(
            '%s vs %s (%s)',
            $this->getTeam()?->getName(),
            $this->getVisitorTeam()?->getClub()->getName(),
            $this->getDate()?->format('d/m/Y')
        );
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getEventType(): ?EventTypeEnum
    {
        return $this->eventType;
    }

    public function setEventType(EventTypeEnum $eventType): static
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;

        return $this;
    }

    public function getVisitorTeam(): ?VisitorTeam
    {
        return $this->visitorTeam;
    }

    public function setVisitorTeam(?VisitorTeam $visitorTeam): static
    {
        $this->visitorTeam = $visitorTeam;

        return $this;
    }

    public function getStadium(): ?Stadium
    {
        return $this->stadium;
    }

    public function setStadium(?Stadium $stadium): static
    {
        $this->stadium = $stadium;

        return $this;
    }

    public function getSeason(): ?SeasonEnum
    {
        return $this->season;
    }

    public function setSeason(SeasonEnum $season): static
    {
        $this->season = $season;

        return $this;
    }

    /**
     * @return Collection<int, Convocation>
     */
    public function getConvocations(): Collection
    {
        return $this->convocations;
    }

    public function addConvocation(Convocation $convocation): static
    {
        if (!$this->convocations->contains($convocation)) {
            $this->convocations->add($convocation);
            $convocation->setEvent($this);
        }

        return $this;
    }

    public function removeConvocation(Convocation $convocation): static
    {
        if ($this->convocations->removeElement($convocation)) {
            // set the owning side to null (unless already changed)
            if ($convocation->getEvent() === $this) {
                $convocation->setEvent(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?EventStatusEnum
    {
        return $this->status;
    }

    public function setStatus(EventStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Presence>
     */
    public function getPresences(): Collection
    {
        return $this->presences;
    }

    public function addPresence(Presence $presence): static
    {
        if (!$this->presences->contains($presence)) {
            $this->presences->add($presence);
            $presence->setEvent($this);
        }

        return $this;
    }

    public function removePresence(Presence $presence): static
    {
        if ($this->presences->removeElement($presence)) {
            // set the owning side to null (unless already changed)
            if ($presence->getEvent() === $this) {
                $presence->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Goal>
     */
    public function getGoals(): Collection
    {
        return $this->goals;
    }

    public function addGoal(Goal $goal): static
    {
        if (!$this->goals->contains($goal)) {
            $this->goals->add($goal);
            $goal->setEvent($this);
        }

        return $this;
    }

    public function removeGoal(Goal $goal): static
    {
        if ($this->goals->removeElement($goal)) {
            // set the owning side to null (unless already changed)
            if ($goal->getEvent() === $this) {
                $goal->setEvent(null);
            }
        }

        return $this;
    }
    public function getHomeScore(): int
    {
        return $this->goals
            ->filter(fn(Goal $goal) =>
                $goal->getPlayer() !== null &&
                $goal->getPlayer()->getPlaysInTeam() === $this->team
            )
            ->count();
    }

    // Calcul du score pour la team visiteuse
    public function getVisitorScore(): int
{
    return $this->goals
        ->filter(fn(Goal $goal) =>
            $goal->getVisitorPlayer() !== null &&
            $goal->getVisitorPlayer()->getVisitorTeam() === $this->visitorTeam
        )
        ->count();
}

    // Optionnel : score format "2 - 1"
    public function getScore(): string
    {
        return $this->getHomeScore() . ' - ' . $this->getVisitorScore();
    }

    
    public function getIsRecurring(): bool
    {
        return $this->isRecurring;
    }

   
    public function setIsRecurring($isRecurring): self
    {
        $this->isRecurring = $isRecurring;

        return $this;
    }
    public function isOngoing(): bool
    {
        return $this->status === EventStatusEnum::ONGOING;
    }
    public function isFinished(): bool
    {
        return $this->status === EventStatusEnum::FINISHED;
    }
    public function isMatch(): bool
    {
        return $this->eventType === EventTypeEnum::MATCH;
    }
}
