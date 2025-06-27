<?php

namespace App\Service;

use App\Dto\ReviewSearchFiltersDto;
use App\Entity\Review;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface ReviewServiceInterface
{
    /**
     * Get paginated list of reviews.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Get paginated list of reviews by user.
     *
     * @param int           $page Page number
     * @param UserInterface $user Author
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedUserList(int $page, UserInterface $user): PaginationInterface;

    /**
     * Save review.
     *
     * @param Review        $review Review entity
     * @param UserInterface $user   Author
     */
    public function save(Review $review, UserInterface $user): void;

    /**
     * Edit review.
     *
     * @param Review $review Review entity
     */
    public function edit(Review $review): void;

    /**
     * Delete review.
     *
     * @param Review $review Review entity
     */
    public function delete(Review $review): void;

    /**
     * Query reviews by filters (e.g. tags).
     *
     * @param ReviewSearchFiltersDto $filters
     *
     * @return QueryBuilder
     */
    public function queryByFilters(ReviewSearchFiltersDto $filters): QueryBuilder;

    /**
     * @param int $book_id
     * @return float
     */
    public function avgRating(int $book_id): float;
}
