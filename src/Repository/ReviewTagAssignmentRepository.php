<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\ReviewTag;
use App\Entity\ReviewTagAssignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReviewTagAssignment>
 */
class ReviewTagAssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReviewTagAssignment::class);
    }

    public function save(ReviewTagAssignment $assignment): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->persist($assignment);
        $this->_em->flush();
    }

    public function delete(ReviewTagAssignment $assignment): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->remove($assignment);
        $this->_em->flush();
    }

    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select('partial assignment.{id, score}')
            ->leftJoin('assignment.review', 'review')
            ->leftJoin('assignment.tag', 'tag')
            ->addSelect('partial review.{id}')
            ->addSelect('partial tag.{id, name}')
            ->orderBy('assignment.id', 'DESC');
    }

    public function queryByReview(Review $review): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->andWhere('assignment.review = :review')
            ->setParameter('review', $review);
    }

    public function queryByTag(ReviewTag $tag): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->andWhere('assignment.tag = :tag')
            ->setParameter('tag', $tag);
    }

    private function getOrCreateQueryBuilder(?QueryBuilder $qb = null): QueryBuilder
    {
        return $qb ?? $this->createQueryBuilder('assignment');
    }
}
