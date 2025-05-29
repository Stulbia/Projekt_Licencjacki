<?php

/** @noinspection PhpClassNamingConventionInspection */

/**
 * Gallery service interface.
 */

namespace App\Service;

use App\Entity\Gallery;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Psr\Log\InvalidArgumentException;

/**
 * Interface GalleryServiceInterface.
 */
interface GalleryServiceInterface
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
     * @param Gallery $gallery Gallery entity
     */
    public function save(Gallery $gallery): void;

    /**
     * Delete entity.
     *
     * @param Gallery $gallery Gallery entity
     *
     * @throws ORMException             if an ORM error occurs
     * @throws OptimisticLockException  if a version conflict occurs
     * @throws InvalidArgumentException if the provided tag is invalid
     */
    public function delete(Gallery $gallery): void;

    /**
     * Can Gallery be deleted?
     *
     * @param Gallery $gallery Gallery entity
     *
     * @return bool Result
     */
    public function canBeDeleted(Gallery $gallery): bool;
}
