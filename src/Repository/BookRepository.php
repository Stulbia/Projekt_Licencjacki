<?php

/**
 * Book repository.
 */

namespace App\Repository;

use App\Dto\BookListFiltersDto;
use App\Dto\BookSearchFiltersDto;
use App\Entity\Enum\BookStatus;
use App\Entity\Gallery;
use App\Entity\Book;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class BookRepository.
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * Query all records.
     *
     * @param BookListFiltersDto $filters Filters
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(BookListFiltersDto $filters): QueryBuilder
    {
        $queryBuilder = $this->getOrCreateQueryBuilder()
            ->select(
                'partial book.{id, createdAt, updatedAt, title, description, filename}',
                'partial gallery.{id, title}',
                'partial tags.{id, title}'
            )
            ->join('book.gallery', 'gallery')
            ->leftJoin('book.tags', 'tags')
            ->orderBy('book.updatedAt', 'DESC');

        return $this->applyFiltersToList($queryBuilder, $filters);
    }

    /**
     * Query searched records.
     *
     * @param BookSearchFiltersDto $filters Filters
     *
     * @return QueryBuilder Query builder
     */
    public function querySearch(BookSearchFiltersDto $filters): QueryBuilder
    {
        $queryBuilder = $this->getOrCreateQueryBuilder()
            ->select(
                'partial book.{id, createdAt, updatedAt, title, description, filename}',
                'partial gallery.{id, title}',
                'partial tags.{id, title}'
            )
            ->join('book.gallery', 'gallery')
            ->leftJoin('book.tags', 'tags')
            ->orderBy('book.updatedAt', 'DESC');

        return $this->applyFiltersToSearchList($queryBuilder, $filters);
    }

    /**
     * Count books by gallery.
     *
     * @param Gallery $gallery Gallery
     *
     * @return int Number of books in gallery
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countByGallery(Gallery $gallery): int
    {
        $qb = $this->getOrCreateQueryBuilder();

        return $qb->select($qb->expr()->countDistinct('book.id'))
            ->where('book.gallery = :gallery')
            ->setParameter(':gallery', $gallery)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // ...
    /**
     * Query book by author.
     *
     * @param User                $user    User entity
     * @param BookListFiltersDto $filters Filter
     *
     * @return QueryBuilder Query builder
     */
    public function queryByAuthor(User $user, BookListFiltersDto $filters): QueryBuilder
    {
        $queryBuilder = $this->queryAll($filters);

        $queryBuilder->andWhere('book.author = :author')
            ->setParameter('author', $user);

        return $queryBuilder;
    }
    //    /**
    //     * Select books by Tags.
    //     *
    //     * @param Gallery $gallery Gallery
    //     *
    //     * @return QueryBuilder Query builder
    //     *
    //     * @throws NoResultException
    //     */
    //    public function findByTag($tag):QueryBuilder
    //    {
    //        return $this->createQueryBuilder('book')
    //            ->select('partial book.{id, createdAt, updatedAt, title}')
    //            ->join('book.tags', 'tag')
    //            ->where('tag.id = :tag')
    //            ->setParameter('tag', $tag);
    //    }

    /**
     * Save entity.
     *
     * @param Book $book Book entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Book $book): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->persist($book);
        $this->_em->flush();
    }

    /**
     * Delete entity.
     *
     * @param Book $book Book entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Book $book): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->remove($book);
        $this->_em->flush();
    }

    /**
     * Find by Tags.
     *
     * @param Tag[] $tags
     *
     * @return Book[]
     */
    public function findByTags(array $tags): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->distinct()
            ->innerJoin('p.tags', 't')
            ->andWhere('t IN (:tags)')
            ->setParameter('tags', $tags);

        return $queryBuilder->getQuery()->getResult();
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
        return $queryBuilder ?? $this->createQueryBuilder('book');
    }

    /**
     * Apply filters to paginated list.
     *
     * @param QueryBuilder        $queryBuilder Query builder
     * @param BookListFiltersDto $filters      Filters
     *
     * @return QueryBuilder Query builder
     */
    private function applyFiltersToList(QueryBuilder $queryBuilder, BookListFiltersDto $filters): QueryBuilder
    {
        if ($filters->gallery instanceof Gallery) {
            $queryBuilder->andWhere('gallery = :gallery')
                ->setParameter('gallery', $filters->gallery);
        }

        if ($filters->tag instanceof Tag) {
            $queryBuilder->andWhere('tags IN (:tag)')
                ->setParameter('tag', $filters->tag);
        }

        if ($filters->bookStatus instanceof BookStatus) {
            $queryBuilder->andWhere('book.status = :status')
                ->setParameter('status', $filters->bookStatus->value, Types::STRING);
        }

        return $queryBuilder;
    }

    /**
     * Apply filters to paginated list.
     *
     * @param QueryBuilder          $queryBuilder Query builder
     * @param BookSearchFiltersDto $filters      Filters
     *
     * @return QueryBuilder Query builder
     */
    private function applyFiltersToSearchList(QueryBuilder $queryBuilder, BookSearchFiltersDto $filters): QueryBuilder
    {
        if ($filters->gallery instanceof Gallery) {
            $queryBuilder->andWhere('gallery = :gallery')
                ->setParameter('gallery', $filters->gallery);
        }

        if ($filters->tag instanceof Tag) {
            $queryBuilder->andWhere('tags IN (:tag)')
                ->setParameter('tag', $filters->tag);
        }

        if ($filters->bookStatus instanceof BookStatus) {
            $queryBuilder->andWhere('book.status = :status')
                ->setParameter('status', $filters->bookStatus->value, Types::STRING);
        }

        if (null !== $filters->titlePattern) {
            $queryBuilder->andWhere('book.title LIKE :titlePattern')
                ->setParameter('titlePattern', '%'.$filters->titlePattern.'%');
        }

        if (null !== $filters->descriptionPattern) {
            $queryBuilder->andWhere('book.description LIKE :descriptionPattern')
                ->setParameter('descriptionPattern', '%'.$filters->descriptionPattern.'%');
        }

        return $queryBuilder;
    }
}
