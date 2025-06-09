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

//    public function queryAll(BookListFiltersDto $filters): QueryBuilder
//    {
//        $qb = $this->getOrCreateQueryBuilder()
//            ->select('partial book.{id, createdAt, updatedAt, title, description, filename, slug}')
//            ->leftJoin('book.tags', 'tags')
//            ->addSelect('partial tags.{id, title}')
//            ->orderBy('book.updatedAt', 'DESC');
//
//        return $this->applyFiltersToList($qb, $filters);
//    }

    public function queryAll(BookListFiltersDto $filters): QueryBuilder
    {
        $qb = $this->getOrCreateQueryBuilder()
            ->select('partial book.{id, createdAt, updatedAt, title, description, coverFilename, slug}')
            ->leftJoin('book.tags', 'tags')
            ->addSelect('partial tags.{id, title}');

        // Jeśli sortujemy po ratingu – dołącz recenzje i oblicz średnią
        if ($filters->sortBy === 'rating') {
            $qb->leftJoin('book.reviews', 'r')
                ->addSelect('AVG(r.rating) AS HIDDEN avgRating')
                ->groupBy('book.id')
                ->orderBy('avgRating', 'DESC');
        } else {
            // Domyślnie sortujemy po dacie aktualizacji
            $qb->orderBy('book.updatedAt', 'DESC');
        }

        return $this->applyFiltersToList($qb, $filters);
    }


//    public function querySearch(BookSearchFiltersDto $filters): QueryBuilder
//    {
//        $qb = $this->getOrCreateQueryBuilder()
//            ->select('partial book.{id, createdAt, updatedAt, title, description, filename, slug}')
//            ->leftJoin('book.tags', 'tags')
//            ->addSelect('partial tags.{id, title}')
//            ->orderBy('book.updatedAt', 'DESC');
//
//        return $this->applyFiltersToSearchList($qb, $filters);
//    }

//    public function querySearch(BookSearchFiltersDto $filters): QueryBuilder
//    {
//        $qb = $this->getOrCreateQueryBuilder()
//            ->select('partial book.{id, createdAt, updatedAt, title, description, filename, slug}')
//            ->leftJoin('book.tags', 'tags')
//            ->addSelect('partial tags.{id, title}');
//
//        // Czy trzeba dołączyć recenzje (dla ratingu lub minRating)
//        $needsRating = $filters->sortBy === 'rating' || $filters->minRating !== null;
//
//        if ($needsRating) {
//            $qb->leftJoin('book.reviews', 'r')
//                ->addSelect('COALESCE(AVG(r.rating), 0) AS HIDDEN avgRating')
//                ->groupBy('book.id, tags.id');
//        }
//
//        // HAVING tylko jeśli potrzebne
//        if ($filters->minRating !== null) {
//            $qb->having('avgRating >= :minRating')
//                ->setParameter('minRating', $filters->minRating);
//        }
//
//        // Sortowanie
//        if ($filters->sortBy === 'rating') {
//            $qb->orderBy('avgRating', 'DESC');
//        } elseif ($filters->sortBy === 'title') {
//            $qb->orderBy('book.title', 'ASC');
//        } else {
//            $qb->orderBy('book.updatedAt', 'DESC');
//        }
//
//        return $this->applyFiltersToSearchList($qb, $filters);
//    }


    public function querySearch(BookSearchFiltersDto $filters): QueryBuilder
    {
        $qb = $this->getOrCreateQueryBuilder()
            ->select('partial book.{id, createdAt, updatedAt, title, description, coverFilename, slug}')
            ->leftJoin('book.tags', 'tags')
            ->addSelect('partial tags.{id, title}');

        // Jeśli potrzebujemy oceny (sortBy lub minRating), robimy join z reviews i liczymy średnią
        if ($filters->minRating !== null) {
            $qb->leftJoin('book.reviews', 'r')
                ->addSelect('AVG(r.rating) AS HIDDEN avgRating')
                ->groupBy('book.id, tags.id');
        }

        // HAVING dla minimalnej oceny
        if ($filters->minRating !== null) {
            $qb->having('COALESCE(AVG(r.rating), -1) >= :minRating')
                ->setParameter('minRating', $filters->minRating);
        }
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
        if (null !== $filters->author) {
            $qb->andWhere('book.author = :author')
                ->setParameter('author', $filters->author);
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

    public function findByFilters(?int $minRating = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('App\Entity\Review', 'r', 'WITH', 'r.book = b')
            ->addSelect('AVG(r.rating) as HIDDEN avgRating')
            ->groupBy('b.id');

        if ($minRating !== null) {
            // COALESCE sprawia, że brak recenzji = -1
            $qb->having('COALESCE(AVG(r.rating), -1) >= :minRating')
                ->setParameter('minRating', $minRating);
        }

        return $qb;
    }

    public function queryForUserBooks(User $user, BookSearchFiltersDto $filters): QueryBuilder
    {
        $qb = $this->getOrCreateQueryBuilder()
            ->select('partial book.{id, createdAt, updatedAt, title, description, coverFilename, slug}')
            ->leftJoin('book.tags', 'tags')
            ->addSelect('partial tags.{id, title}')
            ->innerJoin('book.userBookRelations', 'relation') // zakładam, że relacja `UserBookRelation` to "a"
            ->andWhere('relation.owner = :user')
            ->setParameter('user', $user);

        // Potrzebujemy oceny (dla minRating)
        if ($filters->minRating !== null || $filters->sortBy === 'rating') {
            $qb->leftJoin('book.reviews', 'r')
                ->addSelect('AVG(r.rating) AS HIDDEN avgRating')
                ->groupBy('book.id, tags.id');
        }

        // HAVING dla oceny
        if ($filters->minRating !== null) {
            $qb->having('COALESCE(AVG(r.rating), -1) >= :minRating')
                ->setParameter('minRating', $filters->minRating);
        }

        // Sortowanie
        if ($filters->sortBy === 'rating') {
            $qb->orderBy('avgRating', 'DESC');
        } elseif ($filters->sortBy === 'title') {
            $qb->orderBy('book.title', 'ASC');
        } else {
            $qb->orderBy('book.updatedAt', 'DESC');
        }

        // Reszta filtrów
        return $this->applyFiltersToSearchList($qb, $filters);
    }






//
//    public function findOneBySlugWithTags(string $slug): ?Book
//    {
//        return $this->createQueryBuilder('b')
//            ->leftJoin('b.tags', 't')
//            ->addSelect('t')
//            ->where('b.slug = :slug')
//            ->setParameter('slug', $slug)
//            ->getQuery()
//            ->getOneOrNullResult();
//    }

//    public function findOneBySlugWithTags(string $slug): ?Book
//    {
//        return $this->createQueryBuilder('b')
//            ->leftJoin('b.tags', 't')
//            ->addSelect('t')
//            ->leftJoin('b.reviews', 'r')
//            ->addSelect('r')
//            ->leftJoin('r.tagAssignments', 'ra')
//            ->addSelect('ra')
//            ->leftJoin('ra.tag', 'rt')
//            ->addSelect('rt')
//            ->where('b.slug = :slug')
//            ->setParameter('slug', $slug)
//            ->getQuery()
//            ->getOneOrNullResult();
//    }
    public function findOneBySlugWithTags(string $slug): ?Book
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.tags', 't')
            ->addSelect('t')
            ->leftJoin('b.reviews', 'r')
            ->addSelect('r')
            ->leftJoin('r.tagAssignments', 'ra')
            ->addSelect('ra')
            ->leftJoin('ra.tag', 'rt')
            ->addSelect('rt')
            ->where('b.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function queryForMostPopularBooks(int $limit = 6): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.reviews', 'r')
            ->addSelect('AVG(r.rating) AS HIDDEN avg_rating')
            ->groupBy('b.id')
            ->orderBy('avg_rating', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }


    public function findBooksByTagIdsExcludingUserReviewed(array $tagIds, User $user, int $limit = 5): array
    {
        return $this->createQueryBuilder('b')
            ->distinct()
            ->join('b.reviews', 'r2')
            ->join('r2.tagAssignments', 'ta')
            ->join('ta.tag', 't')
            ->where('t.id IN (:tagIds)')
            ->andWhere('b NOT IN (
            SELECT b2 FROM App\Entity\Book b2
            JOIN b2.reviews r3
            WHERE r3.author = :user
        )')
            ->setParameter('tagIds', $tagIds)
            ->setParameter('user', $user)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }



}
