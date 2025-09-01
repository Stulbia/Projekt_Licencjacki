<?php

namespace App\Entity;

use App\Repository\ReviewTagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewTagRepository::class)]
class ReviewTag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private ?string $name = null;

    /**
     * @var Collection<int, ReviewTagAssignment>
     */
    #[ORM\OneToMany(mappedBy: 'tag', targetEntity: ReviewTagAssignment::class)]
    #[ORM\ManyToOne(inversedBy: 'reviewTagAssignments')]
    private Collection $reviewTagAssignments;

    public function __construct()
    {
        $this->reviewTagAssignments = new ArrayCollection();
    }

    public function getId(): ?int
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

    /**
     * @return Collection<int, ReviewTagAssignment>
     */
    public function getReviewTagAssignments(): Collection
    {
        return $this->reviewTagAssignments;
    }

    public function addReviewTagAssignment(ReviewTagAssignment $reviewTagAssignment): static
    {
        if (!$this->reviewTagAssignments->contains($reviewTagAssignment)) {
            $this->reviewTagAssignments->add($reviewTagAssignment);
            $reviewTagAssignment->setTag($this);
        }

        return $this;
    }

    public function removeReviewTagAssignment(ReviewTagAssignment $reviewTagAssignment): static
    {
        if ($this->reviewTagAssignments->removeElement($reviewTagAssignment)) {
            // set the owning side to null (unless already changed)
            if ($reviewTagAssignment->getTag() === $this) {
                $reviewTagAssignment->setTag(null);
            }
        }

        return $this;
    }
    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
