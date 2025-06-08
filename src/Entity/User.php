<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'email_idx', columns: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 3, max: 180)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    #[Assert\Type('boolean')]
    private bool $banned = false;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Review::class, orphanRemoval: true)]
    private Collection $reviews;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: UserBookRelation::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $bookRelations;


    #[ORM\Column(nullable: true)]
    private ?string $avatarFilename = null;

    public function getAvatarFilename(): ?string
    {
        return $this->avatarFilename;
    }

    public function setAvatarFilename(?string $avatarFilename): self
    {
        $this->avatarFilename = $avatarFilename;
        return $this;
    }

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->bookRelations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getUsername(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }
        return array_unique($roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }

    public function isBanned(): bool
    {
        return $this->banned;
    }

    public function setBanned(bool $banned): void
    {
        $this->banned = $banned;
    }



    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): void
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setAuthor($this);
        }
    }

    public function removeReview(Review $review): void
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getAuthor() === $this) {
                $review->setAuthor(null);
            }
        }
    }

    /**
     * @return Collection<int, UserBookRelation>
     */
    public function getBookRelations(): Collection
    {
        return $this->bookRelations;
    }

    public function addBookRelation(UserBookRelation $relation): static
    {
        if (!$this->bookRelations->contains($relation)) {
            $this->bookRelations[] = $relation;
            $relation->setOwner($this);
        }

        return $this;
    }

    public function removeBookRelation(UserBookRelation $relation): static
    {
        if ($this->bookRelations->removeElement($relation)) {
            if ($relation->getOwner() === $this) {
                $relation->setOwner(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    private ?string $plainPassword = null;

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }
}
