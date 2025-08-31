<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ClubRepository;

#[ORM\Entity(repositoryClass: ClubRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Club
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToOne(inversedBy: 'club', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Address $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    /**
     * @var Collection<int, VisitorTeam>
     */
    #[ORM\OneToMany(targetEntity: VisitorTeam::class, mappedBy: 'club')]
    private Collection $visitorTeams;

    public function __construct()
    {
        $this->visitorTeams = new ArrayCollection();
    }
    public function __toString(): string
    {
        return $this->name ?? 'Club sans nom';
    }

    #[ORM\PrePersist]
    public function generateUuid(): void
    {
        if (null === $this->id) {
            $this->id = Uuid::v4();
        }
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * @return Collection<int, VisitorTeam>
     */
    public function getVisitorTeams(): Collection
    {
        return $this->visitorTeams;
    }

    public function addVisitorTeam(VisitorTeam $visitorTeam): static
    {
        if (!$this->visitorTeams->contains($visitorTeam)) {
            $this->visitorTeams->add($visitorTeam);
            $visitorTeam->setClub($this);
        }

        return $this;
    }

    public function removeVisitorTeam(VisitorTeam $visitorTeam): static
    {
        if ($this->visitorTeams->removeElement($visitorTeam)) {
            // set the owning side to null (unless already changed)
            if ($visitorTeam->getClub() === $this) {
                $visitorTeam->setClub(null);
            }
        }

        return $this;
    }
}
