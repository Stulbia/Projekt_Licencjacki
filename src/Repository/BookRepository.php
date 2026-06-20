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
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

        if ($filters->sortBy === 'rating') {
            $qb->leftJoin('book.reviews', 'r')
                ->addSelect('AVG(r.rating) AS HIDDEN avgRating')
                ->groupBy('book.id')
                ->orderBy('avgRating', 'DESC');
        } else {
            $qb->orderBy('book.updatedAt', 'DESC');
        }

        return $this->applyFiltersToList($qb, $filters);
    }

    private function hasActiveFilters(BookSearchFiltersDto $f): bool
    {
        return
            ($f->author && trim($f->author) !== '') ||
            ($f->minRating !== null) ||
            (!empty($f->reviewTagIds)) ||
            (!empty($f->title)) ||
            (!empty($f->tagIds)) ||
            (!empty($f->status)) ||
            ($f->sortBy === 'rating');
    }

    public function querySearch(BookSearchFiltersDto $filters): QueryBuilder
    {

       if (!$this->hasActiveFilters($filters)) {
            $qb =  $this->getOrCreateQueryBuilder()
                ->select('book, a')
                ->addSelect('AVG(r.rating) AS avgRating')
                ->leftJoin('book.author', 'a')
                ->leftJoin('book.reviews', 'r')
                ->groupBy('book.id, a.id')
                ->orderBy('book.id', 'DESC');
		return $qb;
        }


        $qb = $this->getOrCreateQueryBuilder()
            ->select('book')
            ->leftJoin('book.tags', 'tags')
            ->leftJoin('book.author', 'a')
            ->leftJoin('book.reviews', 'r')
            ->addSelect('AVG(r.rating) AS avgRating')
            ->groupBy('book.id');

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

	if (!empty($filters->tag)) {
    		$tagIds = array_map(fn(Tag $t) => $t->getId(), $filters->tag);
 	   	$qb->andWhere('tags.id IN (:bookTagIds)')
        	->setParameter('bookTagIds', $tagIds);
}
        if ($filters->minRating !== null) {
            $qb->andHaving('COALESCE(AVG(r.rating), -1) >= :minRating')
                ->setParameter('minRating', $filters->minRating);
        }

        $qb = $this->applyFiltersToSearchList($qb, $filters);

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

    public function searchWithAvgRating(BookSearchFiltersDto $filters): array
    {
        $rows = $this->querySearch($filters)->getQuery()->getResult();
        return $this->hydrateAvgRatingIntoBooks($rows);
    }

    public function searchTopWithAvgRating(array $ids): array
    {
        $rows = $this->getOrCreateQueryBuilder()
            ->select('book, a')
            ->addSelect('AVG(r.rating) AS avgRating')
            ->leftJoin('book.author', 'a')
            ->leftJoin('book.reviews', 'r')
            ->groupBy('book.id, a.id')
            ->andWhere('book.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('avgRating', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->hydrateAvgRatingIntoBooks($rows);
    }

    /**
     * @param array<int, array{0: Book, avgRating?: mixed}> $rows
     * @return Book[]
     */
    public function hydrateAvgRatingIntoBooks(array $rows): array
    {
        foreach ($rows as $i => $row) {
            /** @var Book $book */
            $book = $row[0];
            $book->setAvgRating(isset($row['avgRating']) ? (float) $row['avgRating'] : null);
            $rows[$i] = $book;
        }
        return $rows;
    }

    public function queryByAuthor(User $user, BookListFiltersDto $filters): QueryBuilder
    {
        $qb = $this->queryAll($filters);
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
        return $this->createQueryBuilder('book')
            ->distinct()
            ->innerJoin('book.tags', 't')
            ->andWhere('t IN (:tags)')
            ->setParameter('tags', $tags)
            ->getQuery()
            ->getResult();
    }

    public function findById(int $id): array
    {
        return $this->createQueryBuilder('book')
            ->distinct()
            ->innerJoin('book.tags', 't')
            ->andWhere('book.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

    public function findOneBySlugWithTags(string $slug): ?Book
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.tags', 't')
            ->addSelect('t')
            ->where('b.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findMostPopularBooks(int $limit = 10): array
    {
        $query = $this->createQueryBuilder('b')
            ->select('b.id')
            ->leftJoin('b.reviews', 'r')
            ->addSelect('COUNT(r.id) AS HIDDEN reviews_count')
            ->addSelect('AVG(r.rating) AS avgRating')
            ->groupBy('b.id')
            ->having('COUNT(r.id) > 0')
            ->orderBy('reviews_count', 'DESC')
            ->addOrderBy('b.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getScalarResult();

        $ids = array_column($query, 'id');
        $avgRatings = array_column($query, 'avgRating', 'id');

        if (empty($ids)) {
            return [];
        }

        $books = $this->createQueryBuilder('b')
            ->leftJoin('b.author', 'a')
            ->addSelect('a')
            ->where('b.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        foreach ($books as $book) {
            $book->setAvgRating((float) ($avgRatings[$book->getId()] ?? 0));
        }

        return $books;
    }
//    public function findMostPopularBooks(int $limit = 10): array
//    {
//        $query = $this->createQueryBuilder('b')
//            ->leftJoin('b.reviews', 'r')
//            ->leftJoin('b.author', 'a')
//            ->addSelect('AVG(r.rating) AS avgRating')
//            ->addSelect('COUNT(r.id) AS HIDDEN reviews_count')
//            ->groupBy('b.id, a.id')
//            ->having('COUNT(r.id) > 0')
//            ->orderBy('reviews_count', 'DESC')
//            ->addOrderBy('b.id', 'DESC')
//            ->setMaxResults($limit)
//            ->getQuery();
//
//        $paginator = new Paginator($query, true);
//        $rows = iterator_to_array($paginator);
//
//        return $this->hydrateAvgRatingIntoBooks($rows);
//    }

    public function findHighestRatedBooks(int $limit = 6): array
    {
        $rows = $this->createQueryBuilder('b')
            ->leftJoin('b.reviews', 'r')
            ->addSelect('AVG(r.rating) AS avgRating')
            ->groupBy('b.id')
            ->having('COUNT(r.id) > 0')
            ->orderBy('avgRating', 'DESC')
            ->addOrderBy('b.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $this->hydrateAvgRatingIntoBooks($rows);
    }

    public function findByFilters(?int $minRating = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('App\Entity\Review', 'r', 'WITH', 'r.book = b')
            ->addSelect('AVG(r.rating) as HIDDEN avgRating')
            ->groupBy('b.id');

        if ($minRating !== null) {
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
            ->innerJoin('book.userBookRelations', 'relation')
            ->andWhere('relation.owner = :user')
            ->setParameter('user', $user);

        if ($filters->minRating !== null || $filters->sortBy === 'rating') {
            $qb->leftJoin('book.reviews', 'r')
                ->addSelect('AVG(r.rating) AS HIDDEN avgRating')
                ->groupBy('book.id');
        }

        if ($filters->minRating !== null) {
            $qb->having('COALESCE(AVG(r.rating), -1) >= :minRating')
                ->setParameter('minRating', $filters->minRating);
        }

        if ($filters->sortBy === 'rating') {
            $qb->orderBy('avgRating', 'DESC');
        } elseif ($filters->sortBy === 'title') {
            $qb->orderBy('book.title', 'ASC');
        } else {
            $qb->orderBy('book.updatedAt', 'DESC');
        }

        return $this->applyFiltersToSearchList($qb, $filters);
    }

    public function findBooksByTagIdsExcludingUserReviewed(array $tagIds, User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('b')
            ->select('b.id AS bookId')
            ->addSelect('AVG(r.rating) AS avgRating')
            ->join('b.tags', 't')
            ->leftJoin('b.reviews', 'r')
            ->where('t.id IN (:tagIds)')
            ->andWhere('b NOT IN (
                SELECT b2 FROM App\Entity\Book b2
                JOIN b2.reviews r3
                WHERE r3.author = :user
            )')
            ->groupBy('b.id')
            ->orderBy('avgRating', 'DESC')
            ->setParameter('tagIds', $tagIds)
            ->setParameter('user', $user)
            ->setMaxResults($limit)
            ->getQuery()
            ->getScalarResult();
    }

    public function findBooksWithTagSimilarity(array $userVector, User $user, int $limit): array
    {
        $tagIds = array_keys($userVector);

        if (empty($tagIds)) {
            return [];
        }

        $weightCases = '';
        foreach ($userVector as $tagId => $weight) {
            $tagId = (int) $tagId;
            $weight = (float) $weight;
            $weightCases .= " WHEN t.id = {$tagId} THEN {$weight}";
        }

        $tagIdList = implode(',', array_map('intval', $tagIds));

        $dql = "
            SELECT b,
                   SUM(CASE {$weightCases} ELSE 0 END) AS HIDDEN similarity
            FROM App\Entity\Book b
          JOIN b.tags t
            WHERE t.id IN ({$tagIdList})
              AND b.id NOT IN (
                  SELECT IDENTITY(r.book)
                  FROM App\Entity\Review r
                  WHERE r.author = :user
              )
            GROUP BY b.id
           ORDER BY similarity DESC
        ";

        return $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('user', $user)
            ->setMaxResults($limit)
            ->getResult();
    }




//public function findBooksWithTagSimilarity(array $userVector, User $user, int $limit): array
//{
//    $tagIds = array_keys($userVector);
//
 //   if (empty($tagIds)) {
 //       return [];
 //   }
//
///    $weightCases = '';
//    foreach ($userVector as $tagId => $weight) {
//        $tagId = (int) $tagId;
//        $weight = (float) $weight;
 //       $weightCases .= " WHEN t.id = {$tagId} THEN {$weight}";
//    }
//
//    $tagIdList = implode(',', array_map('intval', $tagIds));
//
//
//    $dql = "
 //       SELECT 
//            b,
 //           AVG(r.rating) AS avgRating,
 //           SUM(CASE {$weightCases} ELSE 0 END) AS HIDDEN similarity
 //       FROM App\Entity\Book b
 //       JOIN b.tags t
  //      LEFT JOIN b.reviews r
  //      WHERE t.id IN ({$tagIdList})
   //     
 //       AND b.id NOT IN (
  //          SELECT IDENTITY(r2.book)
  //          FROM App\Entity\Review r2
   //         WHERE r2.author = :user
  //      )
//
  //      AND b.id NOT IN (
 //           SELECT IDENTITY(rel.book)
  //          FROM App\Entity\UserBookRelation rel
    //        WHERE rel.owner = :user
  //      )
//
 //       GROUP BY b.id
  //      ORDER BY similarity DESC
//    ";

  //  return $this->getEntityManager()
 //       ->createQuery($dql)
 //       ->setParameter('user', $user)
  //      ->setMaxResults($limit)
//        ->getResult();
//}



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

        if ($filters->titlePattern) {
            $term = '%' . mb_strtolower(trim($filters->titlePattern)) . '%';
            $qb->andWhere('LOWER(book.title) LIKE :term')
                ->setParameter('term', $term);
        }

        if (null !== $filters->descriptionPattern) {
            $qb->andWhere('book.description LIKE :descriptionPattern')
                ->setParameter('descriptionPattern', '%' . $filters->descriptionPattern . '%');
        }

        return $qb;
    }
}
