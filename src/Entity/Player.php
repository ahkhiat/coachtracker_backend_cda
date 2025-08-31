<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PlayerRepository;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')] 
    #[ORM\Column(type: "uuid", unique: true)]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'players')]
    private ?Team $playsInTeam = null;

    #[ORM\OneToOne(inversedBy: 'player', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    /**
     * @var Collection<int, Convocation>
     */
    #[ORM\OneToMany(targetEntity: Convocation::class, mappedBy: 'player')]
    private Collection $convocations;

    /**
     * @var Collection<int, Presence>
     */
    #[ORM\OneToMany(targetEntity: Presence::class, mappedBy: 'player')]
    private Collection $presences;

    /**
     * @var Collection<int, Goal>
     */
    #[ORM\OneToMany(targetEntity: Goal::class, mappedBy: 'player')]
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
        return $this->user ? (string)$this->user : 'Joueur sans nom';
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getPlaysInTeam(): ?Team
    {
        return $this->playsInTeam;
    }

    public function setPlaysInTeam(?Team $playsInTeam): static
    {
        $this->playsInTeam = $playsInTeam;

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
            $convocation->setPlayer($this);
        }

        return $this;
    }

    public function removeConvocation(Convocation $convocation): static
    {
        if ($this->convocations->removeElement($convocation)) {
            // set the owning side to null (unless already changed)
            if ($convocation->getPlayer() === $this) {
                $convocation->setPlayer(null);
            }
        }

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
            $presence->setPlayer($this);
        }

        return $this;
    }

    public function removePresence(Presence $presence): static
    {
        if ($this->presences->removeElement($presence)) {
            // set the owning side to null (unless already changed)
            if ($presence->getPlayer() === $this) {
                $presence->setPlayer(null);
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
            $goal->setPlayer($this);
        }

        return $this;
    }

    public function removeGoal(Goal $goal): static
    {
        if ($this->goals->removeElement($goal)) {
            // set the owning side to null (unless already changed)
            if ($goal->getPlayer() === $this) {
                $goal->setPlayer(null);
            }
        }

        return $this;
    }
}
