<?php

namespace App\Repository;

use App\Entity\ReviewTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReviewTag>
 */
class ReviewTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReviewTag::class);
    }

    public function save(ReviewTag $tag): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->persist($tag);
        $this->_em->flush();
    }

    public function delete(ReviewTag $tag): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->remove($tag);
        $this->_em->flush();
    }

    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select('partial reviewTag.{id, name}')
            ->orderBy('reviewTag.name', 'ASC');
    }

    private function getOrCreateQueryBuilder(?QueryBuilder $qb = null): QueryBuilder
    {
        return $qb ?? $this->createQueryBuilder('reviewTag');
    }
}
