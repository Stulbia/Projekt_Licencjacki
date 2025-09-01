<?php

/**
 * Book list filters DTO.
 */

namespace App\Dto;

use App\Entity\Enum\BookStatus;
use App\Entity\Tag;

///**
// * Class BookListFiltersDto.
// */
//class BookListFiltersDto
//{
//
//    /**
//     * Constructor.
//     *
//     * @param Tag|null     $tag         Tag entity
//     * @param BookStatus   $bookStatus  Book status
//     */
//    public function __construct(
//        public readonly ?int $tagId,
//        public readonly ?string $bookStatus,
//        public readonly ?string $sortBy
//    ) {
//    }
//}

class BookListFiltersDto
{
    public function __construct(
        public readonly ?Tag $tag,
        public readonly ?BookStatus $bookStatus,
        public readonly ?string $sortBy,
    ) {
    }
}
