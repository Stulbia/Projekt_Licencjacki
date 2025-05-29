<?php

/**
 * Book search filters DTO.
 */

namespace App\Dto;

use App\Entity\Enum\BookStatus;
use App\Entity\Gallery;
use App\Entity\Tag;

/**
 * Class BookSearchFiltersDto.
 */
class BookSearchFiltersDto
{
    /**
     * Constructor.
     *
     * @param Tag|null     $tag                Tag entity
     * @param BookStatus  $bookStatus        Book status
     * @param string|null  $titlePattern       Title pattern
     * @param string|null  $descriptionPattern Description pattern
     */
    public function __construct( public readonly ?Tag $tag, public readonly BookStatus $bookStatus, public readonly ?string $titlePattern, public readonly ?string $descriptionPattern)
    {
    }
}
