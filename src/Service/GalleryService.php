<?php

/**
 * Gallery service.
 */

namespace App\Service;

use App\Entity\Gallery;
use App\Repository\GalleryRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\InvalidArgumentException;

/**
 * Class GalleryService.
 */
class GalleryService implements GalleryServiceInterface
{
    /**
     * Items per page.
     *
     * @constant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param GalleryRepository  $galleryRepository Gallery repository
     * @param PaginatorInterface $paginator         Paginator
     * @param BookRepository    $bookRepository   Book repository
     */
    public function __construct(private readonly GalleryRepository $galleryRepository, private readonly PaginatorInterface $paginator, private readonly BookRepository $bookRepository)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->galleryRepository->queryAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Gallery $gallery Gallery entity
     */
    public function save(Gallery $gallery): void
    {
        $this->galleryRepository->save($gallery);
    }

    /**
     * Delete entity.
     *
     * @param Gallery $gallery Gallery entity
     *
     * @throws ORMException             if an ORM error occurs
     * @throws OptimisticLockException  if a version conflict occurs
     * @throws InvalidArgumentException if the provided tag is invalid
     */
    public function delete(Gallery $gallery): void
    {
        $this->galleryRepository->delete($gallery);
    }

    /**
     * Can Gallery be deleted?
     *
     * @param Gallery $gallery Gallery entity
     *
     * @return bool Result
     */
    public function canBeDeleted(Gallery $gallery): bool
    {
        try {
            $result = $this->bookRepository->countByGallery($gallery);

            return !($result > 0);
        } catch (NoResultException|NonUniqueResultException) {
            return false;
        }
    }

    /**
     * Find by id.
     *
     * @param int $id Gallery id
     *
     * @return Gallery|null Gallery entity
     *
     * @throws NonUniqueResultException
     */
    public function findOneById(int $id): ?Gallery
    {
        return $this->galleryRepository->findOneById($id);
    }
}
