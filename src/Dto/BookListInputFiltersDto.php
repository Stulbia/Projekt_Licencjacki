<?php
/**
 * Book list input filters DTO.
 */

namespace App\Dto;

/**
 * Class BookListInputFiltersDto.
 */
class BookListInputFiltersDto
{
    /**
     * Constructor.
     *
     * @param int|null $galleryId Gallery identifier
     * @param int|null $tagId     Tag identifier
     * @param int      $statusId  Status identifier
     */
    public function __construct(public readonly ?int $galleryId = null, public readonly ?int $tagId = null, public readonly string $statusId = 'PUBLIC')
    {
    }
}
