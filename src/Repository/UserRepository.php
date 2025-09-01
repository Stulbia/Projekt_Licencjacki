<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Upgrade user password over time.
     *
     * @param PasswordAuthenticatedUserInterface $user
     * @param string                             $newHashedPassword
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Save user.
     *
     * @param UserInterface $user
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(UserInterface $user): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Query all users.
     *
     * @return QueryBuilder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select('partial user.{id, email, name, roles, banned}')
            ->orderBy('user.email', 'DESC');
    }

    /**
     * Count all admin users.
     *
     * @return int
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countByAdmin(): int
    {
        $qb = $this->createQueryBuilder('user');
        $qb->select($qb->expr()->countDistinct('user.id'))
            ->where('user.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get or create query builder.
     *
     * @param QueryBuilder|null $queryBuilder
     *
     * @return QueryBuilder
     */
    private function getOrCreateQueryBuilder(?QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('user');
    }
}
