<?php

namespace App\Entity;

use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\VisitorPlayerRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: VisitorPlayerRepository::class)]
#[ORM\HasLifecycleCallbacks]
class VisitorPlayer
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')] 
    #[ORM\Column(type: "uuid", unique: true)]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'visitorPlayers')]
    private ?VisitorTeam $visitorTeam = null;

     /**
     * @var Collection<int, Goal>
     */
    #[ORM\OneToMany(targetEntity: Goal::class, mappedBy: 'player')]
    private Collection $goals;

    public function __construct()
    {
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
        return 'Joueur visiteur';
    }

    public function getId(): ?Uuid
    {
        return $this->id;
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
            $goal->setVisitorPlayer($this);
        }

        return $this;
    }

    public function removeGoal(Goal $goal): static
    {
        if ($this->goals->removeElement($goal)) {
            // set the owning side to null (unless already changed)
            if ($goal->getVisitorPlayer() === $this) {
                $goal->setVisitorPlayer(null);
            }
        }

        return $this;
    }
}
