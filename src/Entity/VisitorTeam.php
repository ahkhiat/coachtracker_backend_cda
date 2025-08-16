<?php

namespace App\Entity;

use App\Enum\AgeCategoryEnum;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\VisitorTeamRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: VisitorTeamRepository::class)]
#[UniqueEntity(
    fields: ['club', 'ageCategory'],
    message: '⚠️ Ce club a déjà une équipe dans cette catégorie d\'âge.'
)]
#[ORM\UniqueConstraint(name: 'visitor_team_name_and_age_unique', columns: ['club_id', 'age_category'])]
#[ORM\HasLifecycleCallbacks]
class VisitorTeam
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    private ?AgeCategoryEnum $ageCategory = null;

    #[ORM\ManyToOne(inversedBy: 'visitorTeams')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Club $club = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'visitorTeam')]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }
    public function __tostring()
    {
        return $this->club->getName() . " - " . $this->ageCategory->value;
    }

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

    public function getAgeCategory(): ?AgeCategoryEnum
    {
        return $this->ageCategory;
    }

    public function setAgeCategory(AgeCategoryEnum $ageCategory): static
    {
        $this->ageCategory = $ageCategory;

        return $this;
    }

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): static
    {
        $this->club = $club;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setVisitorTeam($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getVisitorTeam() === $this) {
                $event->setVisitorTeam(null);
            }
        }

        return $this;
    }
}
