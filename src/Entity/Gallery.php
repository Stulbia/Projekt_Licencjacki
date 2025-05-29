<?php
/**
 * Class Entity.
 */

namespace App\Entity;

use App\Repository\GalleryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Gallery.
 *
 * Represents a gallery entity with title, slug, and timestamps for creation and update.
 *
 * @psalm-suppress MissingConstructor
 */
#[ORM\Entity(repositoryClass: GalleryRepository::class)]
#[ORM\Table(name: 'galleries')]
class Gallery
{
    /**
     * Primary key.
     *
     * @var int|null The unique identifier of the gallery
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Created at.
     *
     * @var \DateTimeImmutable|null The date and time when the gallery was created
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Updated at.
     *
     * @var \DateTimeImmutable|null The date and time when the gallery was last updated
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Title.
     *
     * @var string|null The title of the gallery
     */
    #[ORM\Column(type: 'string', length: 64)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 64)]
    private ?string $title = null;

    /**
     * Slug.
     *
     * @var string|null The slug generated from the title
     */
    #[ORM\Column(length: 64, nullable: true)]
    #[Assert\Type('string')]
    #[Assert\Length(min: 3, max: 64)]
    #[Gedmo\Slug(fields: ['title'])]
    private ?string $slug = null;
    /**
     * @var Collection<int, User>
     */
    #[Assert\Valid]
    #[ORM\ManyToMany(targetEntity: User::class, fetch: 'EXTRA_LAZY', orphanRemoval: false)]
    #[ORM\JoinTable(name: 'galleries_users')]
    private Collection $users;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * Getter for users.
     *
     * @return Collection Users
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * Adds a user.
     *
     * @param User $user User
     */
    public function addUser(User $user): void
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
    }

    /**
     * Removes a user.
     *
     * @param User $user User
     */
    public function removeUser(User $user): void
    {
        $this->users->removeElement($user);
    }

    /**
     * Getter for Id.
     *
     * @return int|null The unique identifier of the gallery
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for created at.
     *
     * @return \DateTimeImmutable|null The date and time when the gallery was created
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Setter for created at.
     *
     * @param \DateTimeImmutable $createdAt The date and time when the gallery was created
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Getter for updated at.
     *
     * @return \DateTimeImmutable|null The date and time when the gallery was last updated
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Setter for updated at.
     *
     * @param \DateTimeImmutable $updatedAt The date and time when the gallery was last updated
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Getter for title.
     *
     * @return string|null The title of the gallery
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Setter for title.
     *
     * @param string $title The title of the gallery
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Getter for slug.
     *
     * @return string|null The slug generated from the title
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * Setter for slug.
     *
     * @param string $slug The slug generated from the title
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }
}
