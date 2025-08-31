<?php

namespace App\Entity;

use App\Enum\RoleEnum;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks] 
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')] 
    #[ORM\Column(type: "uuid", unique: true)]
    private ?Uuid $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    private ?string $plainPassword = null;

    #[ORM\Column(length: 100)]
    private ?string $firstname = null;

    #[ORM\Column(length: 100)]
    private ?string $lastname = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $birthdate = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Address $address = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Coach $coach = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Player $player = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    /**
     * @var Collection<int, UserIsParentOf>
     */
    #[ORM\OneToMany(targetEntity: UserIsParentOf::class, mappedBy: 'user')]
    private Collection $isParentOfs;

    /**
     * @var Collection<int, UserIsParentOf>
     */
    #[ORM\OneToMany(targetEntity: UserIsParentOf::class, mappedBy: 'child')]
    private Collection $isChildOfs;


    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(
        pattern: "/^0[1-9](\d{8})$/",
        message: "The phone number must be a valid French number (10 digits starting with 0)."
    )]
    private ?string $phone = null;

    public function __construct()
    {
        $this->isParentOfs = new ArrayCollection();
        $this->isChildOfs = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    #[ORM\PrePersist] // important
    public function generateUuid(): void
    {
        if ($this->id === null) {
            $this->id = Uuid::v4();
        }
    }
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getRoleLabels(): array
    {
        return array_map(
            fn(string $role) => RoleEnum::from($role)->label(),
            array_filter(
                $this->roles, 
                fn(string $role) => $role !== RoleEnum::ROLE_USER->value
                )
        );
    }


    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTime $birthdate): static
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCoach(): ?Coach
    {
        return $this->coach;
    }

    public function setCoach(Coach $coach): static
    {
        // set the owning side of the relation if necessary
        if ($coach->getUser() !== $this) {
            $coach->setUser($this);
        }

        $this->coach = $coach;

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): static
    {
        // set the owning side of the relation if necessary
        if ($player->getUser() !== $this) {
            $player->setUser($this);
        }

        $this->player = $player;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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
     * @return Collection<int, UserIsParentOf>
     */
    public function getIsParentOfs(): Collection
    {
        return $this->isParentOfs;
    }

    public function addIsParentOf(UserIsParentOf $isParentOf): static
    {
        if (!$this->isParentOfs->contains($isParentOf)) {
            $this->isParentOfs->add($isParentOf);
            $isParentOf->setUser($this);
        }

        return $this;
    }

    public function removeIsParentOf(UserIsParentOf $isParentOf): static
    {
        if ($this->isParentOfs->removeElement($isParentOf)) {
            // set the owning side to null (unless already changed)
            if ($isParentOf->getUser() === $this) {
                $isParentOf->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserIsParentOf>
     */
    public function getIsChildOfs(): Collection
    {
        return $this->isChildOfs;
    }
    public function addIsChildOf(UserIsParentOf $isChildOfs): static
    {
        if (!$this->isChildOfs->contains($isChildOfs)) {
            $this->isChildOfs->add($isChildOfs);
            $isChildOfs->setChild($this);
        }       
        return $this;
    }
    public function removeIsChildOf(UserIsParentOf $isChildOfs): static
    {
        if ($this->isChildOfs->removeElement($isChildOfs)) {
            // set the owning side to null (unless already changed)
            if ($isChildOfs->getChild() === $this) {
                $isChildOfs->setChild(null);
            }
        }   
        return $this;
    }

        public function getTeam(): ?Team
    {
        return $this->player ? $this->player->getPlaysInTeam() : null;
    }

        public function getPhone(): ?string
        {
            return $this->phone;
        }

        public function setPhone(?string $phone): static
        {
            $this->phone = $phone;

            return $this;
        }
}
