<?php

/**
 * Book list input filters DTO.
 */

namespace App\Dto;

/**
 * Class BookListInputFiltersDto.
 */
//class BookListInputFiltersDto
//{
//    /**
//     * Constructor.
//     *
//     * @param int|null $tagId    Tag identifier
//     * @param string   $statusId Status identifier
//     */
//    public function __construct(
//        public readonly ?int $tagId = null,
//        public readonly string $statusId = 'PUBLIC'
//    ) {
//    }
//}

namespace App\Dto;

/**
 * DTO z parametrami filtrowania książek z query stringa (np. ?tagId=1&bookStatus=Published&sortBy=rating).
 */
class BookListInputFiltersDto
{
    public function __construct(
        public readonly ?int $tagId,
        public readonly ?string $bookStatus,
        public readonly ?string $sortBy,
    ) {
    }
}
