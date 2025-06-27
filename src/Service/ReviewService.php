<?php

namespace App\Service;

use App\Dto\ReviewSearchFiltersDto;
use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ReviewService implements ReviewServiceInterface
{
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    public function __construct(
        private readonly ReviewRepository $reviewRepository,
        private readonly PaginatorInterface $paginator,
    ) {
    }

    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->reviewRepository->queryAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    public function getPaginatedUserList(int $page, UserInterface $user): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->reviewRepository->queryByAuthor($user),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    public function save(Review $review, UserInterface $user): void
    {
        $review->setAuthor($user);

        try {
            $this->reviewRepository->save($review);
        } catch (ORMException|OptimisticLockException) {
            // handle exception if needed
        }
    }

    public function edit(Review $review): void
    {
        try {
            $this->reviewRepository->save($review);
        } catch (ORMException|OptimisticLockException) {
            // handle exception if needed
        }
    }

    public function delete(Review $review): void
    {
        try {
            $this->reviewRepository->delete($review);
        } catch (ORMException|OptimisticLockException) {
            // handle exception if needed
        }
    }

    /**
     * Build query to search reviews by filters (e.g. tags).
     */
    public function queryByFilters(ReviewSearchFiltersDto $filters): QueryBuilder
    {
        return $this->reviewRepository->queryByFilters($filters);
    }

    /**
     * @param int $book_id
     * @return float
     */
    public function avgRating(int $book_id): float
    {
        return  $this->reviewRepository->avgRating($book_id);
    }
}
