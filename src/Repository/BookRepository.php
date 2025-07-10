<?php

namespace App\Repository;

use App\Dto\BookListFiltersDto;
use App\Dto\BookSearchFiltersDto;
use App\Entity\Book;
use App\Entity\Enum\BookStatus;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
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
//            ->select('partial book.{id, createdAt, updatedAt, title, description, coverFilename, slug}')
//            ->leftJoin('book.tags', 'tags')
//            ->leftJoin()
//            ->addSelect('partial tags.{id, title}');
//        if ($filters->minRating !== null) {
//            $qb->leftJoin('book.reviews', 'r')
//                ->addSelect('AVG(r.rating) AS HIDDEN avgRating')
//                ->groupBy('book.id, tags.id');
//        }
//
//        // HAVING dla minimalnej oceny
//        if ($filters->minRating !== null) {
//            $qb->having('COALESCE(AVG(r.rating), -1) >= :minRating')
//                ->setParameter('minRating', $filters->minRating);
//        }
//        return $this->applyFiltersToSearchList($qb, $filters);
//    }


    public function querySearch(BookSearchFiltersDto $filters): QueryBuilder
    {
        $qb = $this->getOrCreateQueryBuilder()
            ->select('book')
            ->leftJoin('book.tags', 'tags')
            ->addSelect('partial tags.{id, title}')
            ->leftJoin('book.author', 'a')
            ->addSelect('partial a.{id, firstName, name, pseudonym}')
            ->leftJoin('book.reviews', 'r')
            ->groupBy('book.id')
            ->addGroupBy('a.id')
            ->addGroupBy('tags.id');

        // --- REVIEW TAGS PERCENTAGE FILTER ---
        if (!empty($filters->reviewTagIds)) {
            $qb->leftJoin('r.tagAssignments', 'rta')
                ->leftJoin('rta.tag', 'reviewTag')
                ->addSelect('SUM(CASE WHEN reviewTag.id IN (:tagIds) THEN 1 ELSE 0 END) AS HIDDEN tagged_reviews')
                ->addSelect('COUNT(DISTINCT r.id) AS HIDDEN total_reviews')
                ->having('(
                SUM(CASE WHEN reviewTag.id IN (:tagIds) THEN 1 ELSE 0 END) * 1.0 /
                NULLIF(COUNT(DISTINCT r.id), 0)
            ) >= :minRatio')
                ->setParameter('tagIds', $filters->reviewTagIds)
                ->setParameter('minRatio', 0.4);
        }

        // --- AUTHOR FILTER ---
        if ($filters->author) {
            $term = '%' . mb_strtolower(trim($filters->author)) . '%';
            $qb->andWhere(
                $qb->expr()->orX(
                    'LOWER(a.firstName) LIKE :term',
                    'LOWER(a.name) LIKE :term',
                    'LOWER(a.pseudonym) LIKE :term'
                )
            )->setParameter('term', $term);
        }

        // --- RATING FILTER ---
        if ($filters->minRating !== null) {
            $qb->addSelect('AVG(r.rating) AS HIDDEN avgRating')
                ->andHaving('COALESCE(AVG(r.rating), -1) >= :minRating')
                ->setParameter('minRating', $filters->minRating);
        }

        // --- ADDITIONAL FILTERS ---
        $qb = $this->applyFiltersToSearchList($qb, $filters);

        // --- SORTING ---
        switch ($filters->sortBy) {
            case 'rating':
                $qb->addOrderBy('avgRating', 'DESC');
                break;
            case 'title':
                $qb->addOrderBy('book.title', 'ASC');
                break;
            default:
                $qb->addOrderBy('book.updatedAt', 'DESC');
        }

        return $qb;
    }





    public function queryByAuthor(User $user, BookListFiltersDto $filters): QueryBuilder
    {
        $qb = $this->queryAll($filters);
//        $qb->andWhere('book.author = :author')
        $qb->andWhere($qb->expr()->in('book.author', ':author'))
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
//        if (null !== $filters->author) {
//            $qb->andWhere($qb->expr()->in('book.author', ':author'))
//                ->setParameter('author', $filters->author);
//        }



        if ($filters->bookStatus instanceof BookStatus) {
            $qb->andWhere('book.status = :status')
                ->setParameter('status', $filters->bookStatus->value, Types::STRING);
        }


        if ($filters->titlePattern) {
            $term = '%' . mb_strtolower(trim($filters->titlePattern)) . '%';
            $qb->andWhere(
                $qb->expr()->orX(
                    'LOWER(book.title) LIKE :term',
                )
            )->setParameter('term', $term);
        }

//        if (null !== $filters->titlePattern) {
//            $qb->andWhere('book.title LIKE :titlePattern')
//                ->setParameter('titlePattern', '%' . $filters->titlePattern . '%');
//        }

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

    public function findMostPopularBooks(int $limit = 6): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.reviews', 'r')
            ->addSelect('AVG(r.rating) AS HIDDEN avg_rating')
            ->groupBy('b.id')
            ->having('COUNT(r.id) > 0')
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
