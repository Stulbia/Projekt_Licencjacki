<?php

namespace App\Repository;

use App\Entity\Avatar;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Avatar>
 */
class AvatarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avatar::class);
    }

    /**
     * Save entity.
     *
     * @param Avatar $avatar Avatar entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Avatar $avatar): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->persist($avatar);
        $this->_em->flush();
    }

    /**
     * Delete entity.
     *
     * @param Avatar $avatar Avatar entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Avatar $avatar): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->remove($avatar);
        $this->_em->flush();
    }

    /**
     * Query all avatars.
     *
     * @return QueryBuilder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select('partial avatar.{id, filename}');
    }

    /**
     * Query avatar by user.
     *
     * @param User $user
     *
     * @return QueryBuilder
     */
    public function queryByUser(User $user): QueryBuilder
    {
        return $this->queryAll()
            ->andWhere('avatar.user = :user')
            ->setParameter('user', $user);
    }

    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder
     *
     * @return QueryBuilder
     */
    private function getOrCreateQueryBuilder(?QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('avatar');
    }
}
