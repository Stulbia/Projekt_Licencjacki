<?php

namespace App\Repository;

use App\Dto\BookSearchInputFiltersDto;
use App\Entity\User;
use App\Entity\UserBookRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserBookRelation>
 */
class UserBookRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBookRelation::class);
    }

    /**
     * Get query builder for user's books with optional filters
     *
     * @param User $user
     * @param BookSearchInputFiltersDto $filters
     * @return QueryBuilder
     */
    public function getBooksByUserWithFilters(User $user, BookSearchInputFiltersDto $filters): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ubr')
            ->join('ubr.book', 'b')
            ->addSelect('b')
            ->where('ubr.owner = :user')
            ->setParameter('user', $user);

        if ($filters->bookStatus) {
            $qb->andWhere('ubr.status = :status')
                ->setParameter('status', $filters->bookStatus);
        }

        if ($filters->bookStatus) {
            $qb->andWhere('b.title LIKE :search')
                ->setParameter('search', '%' . $filters->titlePattern . '%');
        }

        if ($filters->minRating !== null || $filters->sortBy === 'rating') {
            $qb->leftJoin('book.reviews', 'r')
                ->addSelect('AVG(r.rating) AS HIDDEN avgRating')
                ->groupBy('book.id, tags.id');
        }

        // Sorting (add more if needed)
        if ($filters->sortBy) {
            switch ($filters->sortBy) {
                case 'title':
                    $qb->orderBy('b.title', 'ASC');
                    break;
                case 'rating':
                    $qb->orderBy('b.averageRating', 'DESC');
                    break;
                case 'updated':
                default:
                    $qb->orderBy('ubr.updatedAt', 'DESC');
                    break;
            }
        } else {
            $qb->orderBy('ubr.updatedAt', 'DESC');
        }

        return $qb;
    }
}
