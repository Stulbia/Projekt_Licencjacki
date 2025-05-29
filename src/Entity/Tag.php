<?php
/**
 * Tag entity.
 */

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Tag entity.
 *
 * Represents a tag entity with title, slug, and timestamps for creation and update.
 */
#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'tags')]
#[ORM\UniqueConstraint(name: 'uq_tags_title', columns: ['title'])]
#[UniqueEntity(fields: ['title'])]
class Tag
{
    /**
     * @var int|null The unique identifier of the tag
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var \DateTimeImmutable|null The date and time when the tag was created
     */
    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    #[Assert\Type(\DateTimeImmutable::class)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var \DateTimeImmutable|null The date and time when the tag was last updated
     */
    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'update')]
    #[Assert\Type(\DateTimeImmutable::class)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var string|null The slug generated from the title
     */
    #[ORM\Column(length: 64)]
    #[Gedmo\Slug(fields: ['title'])]
    private ?string $slug = null;

    /**
     * @var string|null The title of the tag
     */
    #[ORM\Column(length: 64)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 3, max: 64)]
    private ?string $title = null;

    /**
     * Get the tag ID.
     *
     * @return int|null The tag ID
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the creation timestamp.
     *
     * @return \DateTimeImmutable|null The creation timestamp
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set the creation timestamp.
     *
     * @param \DateTimeImmutable $createdAt The creation timestamp
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get the update timestamp.
     *
     * @return \DateTimeImmutable|null The update timestamp
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Set the update timestamp.
     *
     * @param \DateTimeImmutable $updatedAt The update timestamp
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get the slug.
     *
     * @return string|null The slug
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * Set the slug.
     *
     * @param string $slug The slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * Get the title.
     *
     * @return string|null The title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the title.
     *
     * @param string $title The title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
