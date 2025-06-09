<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $rating = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $comment = null;
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reviews')]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Book $book = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, ReviewTagAssignment>
     */
    #[ORM\OneToMany(mappedBy: 'review', targetEntity: ReviewTagAssignment::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $tagAssignments;

    public function __construct()
    {
        $this->tagAssignments = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;
        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;
        return $this;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): static
    {
        $this->book = $book;
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

    /**
     * @return Collection<int, ReviewTagAssignment>
     */
    public function getTagAssignments(): Collection
    {
        return $this->tagAssignments;
    }

    public function addTagAssignment(ReviewTagAssignment $assignment): static
    {
        if (!$this->tagAssignments->contains($assignment)) {
            $this->tagAssignments->add($assignment);
            $assignment->setReview($this);
        }

        return $this;
    }

    public function removeTagAssignment(ReviewTagAssignment $assignment): static
    {
        if ($this->tagAssignments->removeElement($assignment)) {
            if ($assignment->getReview() === $this) {
                $assignment->setReview(null);
            }
        }

        return $this;
    }
}
