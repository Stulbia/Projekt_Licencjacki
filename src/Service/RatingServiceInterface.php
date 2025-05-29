<?php

/**
 * Rating service interface.
 */

namespace App\Service;

use App\Entity\Rating;
use App\Entity\Book;
use App\Entity\User;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Interface RatingServiceInterface.
 */
interface RatingServiceInterface
{
    /**
     * Get paginated list by Book.
     *
     * @param Book $book Book
     * @param int   $page  Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function findByBook(Book $book, int $page): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Rating $rating Rating entity
     * @param User   $user   User entity
     * @param Book  $book  Book
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Rating $rating, UserInterface $user, Book $book): void;

    /** Delete entity.
     *
     * @param Rating $rating Rating entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Rating $rating): void;

    /**
     * Select Ratings by User and Book.
     *
     * @param User  $user  User
     * @param Book $book Book
     *
     * @return Rating|null rating
     */
    public function findByUserAndBook(User $user, Book $book): ?Rating;

    /**
     * Get average rating on Book.
     *
     * @param Book $book Book
     *
     * @return float|null Average Rating
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findAverageRatingByBook(Book $book): ?float;

    /**
     * Find order of certain books.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function findBookOrder(int $page): PaginationInterface;
}
