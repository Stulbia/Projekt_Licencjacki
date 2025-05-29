<?php

/**
 * Gallery repository.
 */

namespace App\Repository;

use App\Entity\Gallery;
// use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Gallery|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gallery|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gallery[]    findAll()
 * @method Gallery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Gallery>
 */
class GalleryRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gallery::class);
    }

    /**
     * Query all records.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select('partial gallery.{id, createdAt, updatedAt, title}')
            ->orderBy('gallery.updatedAt', 'DESC');
    }

    /**
     * Save entity.
     *
     * @param Gallery $gallery Gallery entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Gallery $gallery): void
    {
        assert($this->_em instanceof EntityManager);

        //        $currentDateTime = new \DateTimeImmutable();
        //
        //        $gallery->setCreatedAt($currentDateTime); //moje,z zajec w serwisie
        //        $gallery->setUpdatedAt($currentDateTime);

        $this->_em->persist($gallery);
        $this->_em->flush();
    }

    /**
     * Delete entity.
     *
     * @param Gallery $gallery Gallery entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Gallery $gallery): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->remove($gallery);
        $this->_em->flush();
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
        return $queryBuilder ?? $this->createQueryBuilder('gallery');
    }
}
