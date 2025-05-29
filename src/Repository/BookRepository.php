<?php

namespace App\Repository;

use App\Dto\BookListFiltersDto;
use App\Dto\BookSearchFiltersDto;
use App\Entity\Book;
use App\Entity\Enum\BookStatus;
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
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function queryAll(BookListFiltersDto $filters): QueryBuilder
    {
        $qb = $this->getOrCreateQueryBuilder()
            ->select('partial book.{id, createdAt, updatedAt, title, description, filename}')
            ->leftJoin('book.tags', 'tags')
            ->addSelect('partial tags.{id, title}')
            ->orderBy('book.updatedAt', 'DESC');

        return $this->applyFiltersToList($qb, $filters);
    }

    public function querySearch(BookSearchFiltersDto $filters): QueryBuilder
    {
        $qb = $this->getOrCreateQueryBuilder()
            ->select('partial book.{id, createdAt, updatedAt, title, description, filename}')
            ->leftJoin('book.tags', 'tags')
            ->addSelect('partial tags.{id, title}')
            ->orderBy('book.updatedAt', 'DESC');

        return $this->applyFiltersToSearchList($qb, $filters);
    }

    public function queryByAuthor(User $user, BookListFiltersDto $filters): QueryBuilder
    {
        $qb = $this->queryAll($filters);
        $qb->andWhere('book.author = :author')
            ->setParameter('author', $user);

        return $qb;
    }

    public function save(Book $book): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->persist($book);
        $this->_em->flush();
    }

    public function delete(Book $book): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->remove($book);
        $this->_em->flush();
    }

    public function findByTags(array $tags): array
    {
        $qb = $this->createQueryBuilder('book')
            ->distinct()
            ->innerJoin('book.tags', 't')
            ->andWhere('t IN (:tags)')
            ->setParameter('tags', $tags);

        return $qb->getQuery()->getResult();
    }

    private function getOrCreateQueryBuilder(?QueryBuilder $qb = null): QueryBuilder
    {
        return $qb ?? $this->createQueryBuilder('book');
    }

    private function applyFiltersToList(QueryBuilder $qb, BookListFiltersDto $filters): QueryBuilder
    {
        if ($filters->tag instanceof Tag) {
            $qb->andWhere('tags IN (:tag)')
                ->setParameter('tag', $filters->tag);
        }

        if ($filters->bookStatus instanceof BookStatus) {
            $qb->andWhere('book.status = :status')
                ->setParameter('status', $filters->bookStatus->value, Types::STRING);
        }

        return $qb;
    }

    private function applyFiltersToSearchList(QueryBuilder $qb, BookSearchFiltersDto $filters): QueryBuilder
    {
        if ($filters->tag instanceof Tag) {
            $qb->andWhere('tags IN (:tag)')
                ->setParameter('tag', $filters->tag);
        }

        if ($filters->bookStatus instanceof BookStatus) {
            $qb->andWhere('book.status = :status')
                ->setParameter('status', $filters->bookStatus->value, Types::STRING);
        }

        if (null !== $filters->titlePattern) {
            $qb->andWhere('book.title LIKE :titlePattern')
                ->setParameter('titlePattern', '%' . $filters->titlePattern . '%');
        }

        if (null !== $filters->descriptionPattern) {
            $qb->andWhere('book.description LIKE :descriptionPattern')
                ->setParameter('descriptionPattern', '%' . $filters->descriptionPattern . '%');
        }

        return $qb;
    }
}
