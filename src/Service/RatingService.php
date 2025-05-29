<?php

/**
 * Rating service.
 */

namespace App\Service;

use App\Entity\Rating;
use App\Entity\Book;
use App\Entity\User;
use App\Repository\RatingRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class RatingService.
 */
class RatingService implements RatingServiceInterface
{
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param RatingRepository   $ratingRepository Rating repository
     * @param PaginatorInterface $paginator        Paginator
     */
    public function __construct(private readonly RatingRepository $ratingRepository, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * Get paginated list by Book.
     *
     * @param Book $book Book
     * @param int   $page  Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function findByBook(Book $book, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->ratingRepository->findByBook($book),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

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
    public function findAverageRatingByBook(Book $book): ?float
    {
        $ave = $this->ratingRepository->findAverageRatingByBook($book) ?? 0;

        return $ave;
    }

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
    public function save(Rating $rating, UserInterface $user, Book $book): void
    {
        $rating->setUser($user);
        $rating->setBook($book);

        $this->ratingRepository->save($rating);
    }

    /** Delete entity.
     *
     * @param Rating $rating Rating entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Rating $rating): void
    {
        $this->ratingRepository->delete($rating);
    }

    /**
     * Select Ratings by User and Book.
     *
     * @param User  $user  User
     * @param Book $book Book
     *
     * @return Rating|null rating
     */
    public function findByUserAndBook(User $user, Book $book): ?Rating
    {
        return $this->ratingRepository->findByUserAndBook($user, $book);
    }

    /**
     * Find order of certain books.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function findBookOrder(int $page = 1): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->ratingRepository->findBookOrder(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }
}
