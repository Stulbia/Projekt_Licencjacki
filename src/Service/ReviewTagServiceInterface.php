<?php

/**
 * ReviewTag service interface.
 */

namespace App\Service;

use App\Entity\ReviewTag;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface ReviewTagServiceInterface.
 */
interface ReviewTagServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Save entity.
     *
     * @param ReviewTag $tag Tag entity
     */
    public function save(ReviewTag $tag): void;

    /**
     * Delete entity.
     *
     * @param ReviewTag $tag Tag entity
     */
    public function delete(ReviewTag $tag): void;

    /**
     * Find by ID.
     *
     * @param int $id
     *
     * @return ReviewTag|null
     */
    public function findOneById(int $id): ?ReviewTag;
}
