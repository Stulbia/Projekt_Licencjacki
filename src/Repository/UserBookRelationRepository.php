<?php

namespace App\Repository;

use App\Dto\BookSearchInputFiltersDto;
use App\Entity\Book;
use App\Entity\User;
use App\Entity\UserBookRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserBookRelation>
 */
class UserBookRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBookRelation::class);
    }

    /**
     * Get query builder for user's books with optional filters
     *
     * @param User $user
     * @param BookSearchInputFiltersDto $filters
     * @return \App\Entity\Book[]|array[]
     */
    public function getBooksByUserWithFilters(User $user, BookSearchInputFiltersDto $filters): array
    {
        $qb = $this->createQueryBuilder('ubr')
            ->join('ubr.book', 'b')
            ->addSelect('b')
            ->leftJoin('b.reviews', 'r')
            // use a hidden select so hydration still yields entities
            ->addSelect('AVG(r.rating) AS HIDDEN avgRating')
            // IMPORTANT: group by both root & book
            ->groupBy('ubr.id, b.id')
            ->where('ubr.owner = :user')
            ->setParameter('user', $user);


        if (!$filters->bookStatus && !$filters->titlePattern && $filters->minRating === null && !$filters->sortBy) {
            $qb->orderBy('ubr.updatedAt', 'DESC');
        }

        if ($filters->bookStatus) {
            $qb->andWhere('ubr.status = :status')
                ->setParameter('status', $filters->bookStatus);
        }

        if ($filters->titlePattern) {
            $qb->andWhere('b.title LIKE :search')
                ->setParameter('search', '%'.$filters->titlePattern.'%');
        }

        if ($filters->minRating !== null) {
            // COALESCE to allow books without reviews if you want to include them at 0
            $qb->having('COALESCE(AVG(r.rating), 0) >= :minRating')
                ->setParameter('minRating', $filters->minRating);
        }

        if ($filters->sortBy) {
            switch ($filters->sortBy) {
                case 'title':
                    $qb->orderBy('b.title', 'ASC');
                    break;
                case 'rating':
                    // sort by the hidden alias
                    $qb->orderBy('avgRating', 'DESC');
                    break;
                case 'updated':
                default:
                    $qb->orderBy('ubr.updatedAt', 'DESC');
                    break;
            }
        } else {
            $qb->orderBy('ubr.updatedAt', 'DESC');
        }

//        return $qb;


        $rows = $qb->getQuery()->getResult();

         return $this->hydrateAvgRatingIntoBooks($rows);
    }

    /**
     * @param array<int, array{0:\App\Entity\Book, avgRating?: mixed}> $rows
     * @return \App\Entity\Book[]
     */
    public function hydrateAvgRatingIntoBooks(array $relations): array
    {
        foreach ($relations as $i => $relation) {
            $book = $relation->getBook();

            $book->setAvgRating($this->calculateAvgRating($book->getId()));
            $relation->setBook($book);
            $relations[$i] = $relation;
        }
        return $relations;
    }

    public function calculateAvgRating(int $bookId): ?float
    {
        return $this->createQueryBuilder('ubr')
            ->select('AVG(r.rating) as avgRating')
            ->join('ubr.book', 'b')
            ->leftJoin('b.reviews', 'r')
            ->where('b.id = :id')
            ->setParameter('id', $bookId)
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function getBooksByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('ubr')
            ->join('ubr.book', 'b')
            ->addSelect('b')
            ->leftJoin('b.reviews', 'r')
            // use a hidden select so hydration still yields entities
            ->addSelect('AVG(r.rating) AS HIDDEN avgRating')
            // IMPORTANT: group by both root & book
            ->groupBy('ubr.id, b.id')
            ->where('ubr.owner = :user')
            ->setParameter('user', $user);

        $qb->orderBy('avgRating', 'DESC');

        $rows = $qb->getQuery()->getResult();

        return $this->hydrateAvgRatingIntoBooks($rows);
    }










}
