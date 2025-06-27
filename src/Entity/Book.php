<?php

namespace App\Entity;

use App\Entity\Enum\BookStatus;
use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\Table(name: 'books')]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Assert\Type(\DateTimeImmutable::class)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    #[Assert\Type(\DateTimeImmutable::class)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(enumType: BookStatus::class)]
    private BookStatus $status = BookStatus::PUBLIC;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 3, max: 64)]
    private ?string $title = null;

    #[ORM\ManyToOne(targetEntity: Author::class, inversedBy: 'books', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Author $author = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Gedmo\Slug(fields: ['title'])]
    private ?string $slug = null;

    #[Assert\Valid]
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'books', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(name: 'books_tags')]
    private Collection $tags;

    #[ORM\Column(type: Types::TEXT, length: 255, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'book', targetEntity: Review::class, orphanRemoval: true)]
    private Collection $reviews;

    #[ORM\OneToMany(mappedBy: 'book', targetEntity: UserBookRelation::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $userBookRelations;


    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $coverFilename;




    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->userBookRelations = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getStatus(): BookStatus
    {
        return $this->status;
    }

    public function setStatus(BookStatus $status): void
    {
        $this->status = $status;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): void
    {
        $this->author = $author;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }


    public function addTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addBook($this);
        }
    }

    public function removeTag(Tag $tag): void
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeBook($this);
        }
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setBook($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getBook() === $this) {
                $review->setBook(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserBookRelation>
     */
    public function getUserBookRelations(): Collection
    {
        return $this->userBookRelations;
    }

    public function addUserBookRelation(UserBookRelation $relation): static
    {
        if (!$this->userBookRelations->contains($relation)) {
            $this->userBookRelations->add($relation);
            $relation->setBook($this);
        }

        return $this;
    }

    public function removeUserBookRelation(UserBookRelation $relation): static
    {
        if ($this->userBookRelations->removeElement($relation)) {
            if ($relation->getBook() === $this) {
                $relation->setBook(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? 'Book #' . ($this->id ?? 'N/A');
    }

    public function getCoverFilename(): ?string
    {
        return $this->coverFilename;
    }

    public function setCoverFilename(?string $coverFilename): self
    {
        $this->coverFilename = $coverFilename;
        return $this;
    }
}
