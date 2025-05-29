<?php

namespace App\Repository;

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

    public function queryByUser(User $user): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->andWhere('review.author = :author')
            ->setParameter('author', $user);
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
}
