<?php

namespace App\Entity;

use App\Repository\ReviewTagAssignmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewTagAssignmentRepository::class)]
class ReviewTagAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(inversedBy: 'tagAssignments')]
    private ?Review $review = null;

    #[ORM\ManyToOne(inversedBy: 'reviewTagAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ReviewTag $tag = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReview(): ?Review
    {
        return $this->review;
    }

    public function setReview(?Review $review): static
    {
        $this->review = $review;

        return $this;
    }

    public function getTag(): ?ReviewTag
    {
        return $this->tag;
    }

    public function setTag(?ReviewTag $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getTag();
    }
}
