<?php

namespace App\Repository;

use App\Dto\ReviewSearchFiltersDto;
use App\Entity\Review;
use App\Entity\User;
use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function save(Review $review): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->persist($review);
        $this->_em->flush();
    }

    public function delete(Review $review): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->remove($review);
        $this->_em->flush();
    }

    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select('partial review.{id, rating, comment}')
            ->orderBy('review.id', 'DESC');
    }

//    public function queryByUser(User $user): QueryBuilder
//    {
//        return $this->getOrCreateQueryBuilder()
//            ->andWhere('review.author = :author')
//            ->setParameter('author', $user);
//    }

    public function queryByAuthor(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.book', 'b')
            ->leftJoin('b.author', 'a')
            ->addSelect('b')
            ->andWhere('r.author = :author')
            ->setParameter('author', $user)
            ->orderBy('r.rating', 'DESC');
    }

    public function topReviewsByAuthor(User $user): QueryBuilder
    {
        $qb = $this->queryByAuthor($user);
        $qb->getQuery()
            ->getResult();
        return $qb;
    }

    public function findMostPopularBooksByTags(array $topReviewTags, int $limit = 10): array
    {
        $tagIds = $topReviewTags;

        if (empty($tagIds)) {
            return [];
        }

        return $this->createQueryBuilder('r')
            ->select('b.id AS bookId')
            ->innerJoin('r.book', 'b')
            ->innerJoin('r.tagAssignments', 'ra')
            ->innerJoin('ra.tag', 't')
            ->where('t.id IN (:tagIds)')
            ->setParameter('tagIds', $tagIds)
            ->groupBy('b.id')
            ->setMaxResults($limit)
            ->getQuery()
            ->getScalarResult();



    }


    public function queryByBook(Book $book): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->andWhere('review.book = :book')
            ->setParameter('book', $book);
    }

    private function getOrCreateQueryBuilder(?QueryBuilder $qb = null): QueryBuilder
    {
        return $qb ?? $this->createQueryBuilder('review');
    }

    public function findTopTagIdsUsedByUser(User $user, int $limit = 3): array
    {
        return $this->createQueryBuilder('r')
            ->select('t.id')
            ->join('r.tagAssignments', 'a')
            ->join('a.tag', 't')
            ->where('r.author = :author')
            ->setParameter('author', $user)
            ->groupBy('t.id')
            ->orderBy('COUNT(t.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getSingleColumnResult(); // Doctrine >= 2.8
    }
//
//    public function queryByFilters(ReviewSearchFiltersDto $filters): QueryBuilder
//    {
//        $qb = $this->createQueryBuilder('r')
//            ->leftJoin('r.book', 'b')
//            ->addSelect('b');
//
//        if ($filters->getTagIds()) {
//            $qb->join('r.tagAssignments', 'assignment')
//                ->join('assignment.tag', 'tag')
//                ->andWhere('tag.id IN (:tagIds)')
//                ->setParameter('tagIds', $filters->getTagIds());
//        }
//
//        return $qb->orderBy('r.createdAt', 'DESC');
//    }
//

    public function queryByFilters(ReviewSearchFiltersDto $filters): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.book', 'b')
            ->addSelect('b');


        if (!empty($filters->getTagIdValues())) {
            $qb->join('r.tagAssignments', 'a')
                ->join('a.tag', 't')
                ->andWhere('t.id IN (:tagIds)')
                ->setParameter('tagIds', $filters->getTagIdValues());
        }


        if ($filters->getSearch()) {
            $qb->andWhere('r.comment LIKE :search')
                ->setParameter('search', '%' . $filters->getSearch() . '%');
        }
        if ($filters->getMinRating() !== null) {
            $qb->andWhere('r.rating >= :minRating')
                ->setParameter('minRating', $filters->getMinRating());
        }

        return $qb->orderBy('r.rating', 'DESC');
    }

    public function avgRating(int $book_id): float
    {
        return ($this->createQueryBuilder('r')
            ->select('AVG(r.rating)')
            ->where('r.book = :book_id')
            ->setParameter('book_id', $book_id)
            ->getQuery()
            ->getSingleScalarResult() ?? 0.0);
    }
}
