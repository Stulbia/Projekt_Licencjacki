<?php

/**
 * Book list filters DTO.
 */

namespace App\Dto;

use App\Entity\Enum\BookStatus;
use App\Entity\Gallery;
use App\Entity\Tag;

/**
 * Class BookListFiltersDto.
 */
class BookListFiltersDto
{
    /**
     * Constructor.
     *
     * @param Gallery|null $gallery     Gallery entity
     * @param Tag|null     $tag         Tag entity
     * @param BookStatus  $bookStatus Book status
     */
    public function __construct(public readonly ?Gallery $gallery, public readonly ?Tag $tag, public readonly BookStatus $bookStatus)
    {
    }
}
