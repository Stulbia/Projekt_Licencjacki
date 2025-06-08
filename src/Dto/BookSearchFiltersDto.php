<?php

namespace App\Dto;

use App\Entity\Author;
use App\Entity\Enum\BookStatus;
use App\Entity\Tag;

/**
* Sparsowane filtry do wyszukiwania książek (repozytorium/QueryBuilder).
*/
class BookSearchFiltersDto
{
    public function __construct(
        public readonly ?Tag $tag,
        public readonly ?BookStatus $bookStatus,
        public readonly ?string $titlePattern,
        public readonly ?string $descriptionPattern,
        public readonly ?string $sortBy,
        public readonly ?int $minRating,
        public readonly ?Author $author
    ) {
    }
}
