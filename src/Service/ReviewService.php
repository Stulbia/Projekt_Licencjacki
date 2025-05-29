<?php

/**
 * Review service.
 */

namespace App\Service;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ReviewService.
 */
class ReviewService implements ReviewServiceInterface
{
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    public function __construct(
        private readonly ReviewRepository $reviewRepository,
        private readonly PaginatorInterface $paginator,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->reviewRepository->queryAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginatedUserList(int $page, UserInterface $user): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->reviewRepository->queryByAuthor($user),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function save(Review $review, UserInterface $user): void
    {
        $review->setAuthor($user);

        try {
            $this->reviewRepository->save($review);
        } catch (ORMException|OptimisticLockException) {
            // handle exception if needed
        }
    }

    /**
     * {@inheritdoc}
     */
    public function edit(Review $review): void
    {
        try {
            $this->reviewRepository->save($review);
        } catch (ORMException|OptimisticLockException) {
            // handle exception if needed
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Review $review): void
    {
        try {
            $this->reviewRepository->delete($review);
        } catch (ORMException|OptimisticLockException) {
            // handle exception if needed
        }
    }
}
