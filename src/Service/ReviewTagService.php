<?php

/**
 * ReviewTag service.
 */

namespace App\Service;

use App\Entity\ReviewTag;
use App\Repository\ReviewTagRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Class ReviewTagService.
 */
class ReviewTagService implements ReviewTagServiceInterface
{
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    public function __construct(
        private readonly ReviewTagRepository $reviewTagRepository,
        private readonly PaginatorInterface $paginator
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->reviewTagRepository->queryAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function save(ReviewTag $tag): void
    {
        try {
            $this->reviewTagRepository->save($tag);
        } catch (ORMException|OptimisticLockException) {
            // handle error
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ReviewTag $tag): void
    {
        try {
            $this->reviewTagRepository->delete($tag);
        } catch (ORMException|OptimisticLockException) {
            // handle error
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findOneById(int $id): ?ReviewTag
    {
        return $this->reviewTagRepository->findOneById($id);
    }

    public function findManyById(array $ids): array
    {
        return $this->reviewTagRepository->createQueryBuilder('rt')
            ->where('rt.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

}
