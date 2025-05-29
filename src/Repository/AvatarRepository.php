<?php

/**
 * Avatar Repository .
 */

namespace App\Repository;

use App\Entity\Avatar;
use App\Entity\Book;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Avatar>
 */
class AvatarRepository extends ServiceEntityRepository
{
    /**
     * Constructor for avatar.
     *
     * @param ManagerRegistry $registry ManagerRegistry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avatar::class);
    }

    /**
     * Save entity.
     *
     * @param Avatar $avatar Book entity
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
     * @param Avatar $avatar Book entity
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
     * Select avatars from database.
     *
     * @return QueryBuilder Query builder
     *
     * @throws NoResultException
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select(
                'avatar.{id, filename}',
            );
    }

    /**
     * Select avatar by author.
     *
     * @param User $user User
     *
     * @return QueryBuilder Query builder
     *
     * @throws NoResultException
     */
    public function queryByAuthor(User $user): QueryBuilder
    {
        $queryBuilder = $this->queryAll();

        $queryBuilder->andWhere('avatar.user = :user')
            ->setParameter('user', $user);

        return $queryBuilder;
    }

    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(?QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('avatar');
    }

    //    /**
    //     * @return Avatar[] Returns an array of Avatar objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Avatar
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
