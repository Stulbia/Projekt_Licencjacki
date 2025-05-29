<?php

/**
 * User entity.
 */

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'email_idx', columns: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Primary key.
     *
     * @var int|null The unique identifier of the user
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Name.
     *
     * @var string|null The name of the user
     */
    #[ORM\Column(type: 'string', length: 180, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 3, max: 180)]
    private ?string $name;

    /**
     * Email.
     *
     * @var string|null The email address of the user
     */
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email;

    /**
     * Roles.
     *
     * @var array<int, string> The roles assigned to the user
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * Password.
     *
     * @var string|null The hashed password of the user
     */
    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    private ?string $password;

    /**
     * Avatar.
     *
     * @var Avatar|null The avatar associated with the user
     */
    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Avatar $avatar = null;

    /**
     * Banned.
     *
     * @var bool The banned status of the user
     */
    #[ORM\Column(type: 'boolean')]
    #[Assert\Type('boolean')]
    private bool $banned = false;

    /**
     * Getter for id.
     *
     * @return int|null The unique identifier of the user
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for email.
     *
     * @return string|null The email address of the user
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Setter for name.
     *
     * @param string $name The name of the user
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Getter for name.
     *
     * @return string|null The name of the user
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Setter for email.
     *
     * @param string $email The email address of the user
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @return string User identifier
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     *
     * @return string Username
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * Getter for roles.
     *
     * @return array<int, string> The roles assigned to the user
     *
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        // $roles[] = UserRole::ROLE_USER->value;

        return array_unique($roles);
    }

    /**
     * Setter for roles.
     *
     * @param array<int, string> $roles The roles assigned to the user
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * Getter for password.
     *
     * @return string|null The hashed password of the user
     *
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Setter for password.
     *
     * @param string $password The hashed password of the user
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @return string|null Salt
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * Removes sensitive information from the token.
     *
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * Getter for avatar.
     *
     * @return Avatar|null The avatar associated with the user
     */
    public function getAvatar(): ?Avatar
    {
        return $this->avatar;
    }

    /**
     * Setter for avatar.
     *
     * Sets the avatar for the user and ensures the avatar's user reference is correctly set.
     *
     * @param Avatar|null $avatar The avatar to be associated with the user
     */
    public function setAvatar(?Avatar $avatar): void
    {
        $this->avatar = $avatar;
    }

    /**
     * Getter for banned.
     *
     * @return bool The banned status of the user
     */
    public function isBanned(): bool
    {
        return $this->banned;
    }

    /**
     * Setter for banned.
     *
     * Sets the banned status of the user.
     *
     * @param bool $banned The banned status of the user
     */
    public function setBanned(bool $banned): void
    {
        $this->banned = $banned;
    }
}
