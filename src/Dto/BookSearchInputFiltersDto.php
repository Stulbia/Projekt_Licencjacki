<?php

namespace App\Dto;

/**
 * DTO z parametrami filtrowania wyszukiwania książek z zapytania HTTP.
 */
class BookSearchInputFiltersDto
{
    /**
     * @param int|null    $tagId               Tag ID z query param
     * @param string      $bookStatus          Status jako string (np. "PUBLIC")
     * @param string|null $titlePattern        Fragment tytułu do wyszukania
     * @param string|null $descriptionPattern  Fragment opisu do wyszukania
     * @param string|null $sortBy              Pole sortowania (np. "rating", "title")
     * @param int|null    $minRating           Minimalna średnia ocena
     */
    public function __construct(
        public ?int $tagId = null,
        public string $bookStatus = 'PUBLIC',
        public ?string $titlePattern = null,
        public ?string $descriptionPattern = null,
        public ?string $sortBy = null,
        public ?string $author = null,
        public ?int $minRating = null,
        public ?int $reviewTagId = null,
    ) {
    }
}
