<?php
/**
 * Book list input filters DTO.
 */

namespace App\Dto;

/**
 * Class BookSearchInputFiltersDto.
 */
class BookSearchInputFiltersDto
{
    /**
     * Constructor.
     *
     * @param int|null    $galleryId     Category identifier
     * @param int|null    $tagId         Tag identifier
     * @param string      $statusId      Status identifier
     * @param string|null $titleId       Title identifier
     * @param string|null $descriptionId Description identifier
     */
    public function __construct(public ?int $galleryId = null, public ?int $tagId = null, public string $statusId = 'PUBLIC', public ?string $titleId = null, public ?string $descriptionId = null)
    {
    }
}
