<?php

namespace App\Dto;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ReviewSearchFiltersDto
{
    private Collection $tagIds;
    private ?string $search = null;
    private ?int $minRating = null;

    public function __construct()
    {
        $this->tagIds = new ArrayCollection();
    }

    public function getTagIds(): Collection
    {
        return $this->tagIds;
    }

    public function setTagIds(Collection $tagIds): void
    {
        $this->tagIds = $tagIds;
    }

    /**
     * For use in query filters
     * @return array<int>
     */
    public function getTagIdValues(): array
    {
        return $this->tagIds->map(fn($tag) => $tag->getId())->toArray();
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): void
    {
        $this->search = $search;
    }

    public function getMinRating(): ?int
    {
        return $this->minRating;
    }

    public function setMinRating(?int $minRating): void
    {
        $this->minRating = $minRating;
    }
}
