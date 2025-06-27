<?php

namespace App\Repository;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Author>
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

    /**
     * Zwraca autorów, którzy mają co najmniej jedną książkę.
     *
     * @return Author[]
     */
    public function findAuthorsWithBooks(): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.books', 'b')
            ->addSelect('b')
            ->groupBy('a.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * Zwraca książki konkretnego autora.
     *
     * @param Author $author
     * @return Book[]
     */
    public function findBooksByAuthor(Author $author): array
    {
        return $this->_em->createQueryBuilder()
            ->select('b')
            ->from(Book::class, 'b')
            ->where('b.author = :author')
            ->setParameter('author', $author)
            ->orderBy('b.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByName(string $name): array
    {
        $qb = $this->createQueryBuilder('a');
        $qb->where(
            $qb->expr()->orX(
                'LOWER(a.firstName)  LIKE :name',
                'LOWER(a.name)       LIKE :name',
                'LOWER(a.pseudonym)  LIKE :name'
            )
        )
            ->setParameter('name', '%' . mb_strtolower($name) . '%')
            ->orderBy('a.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function save(Author $author): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->persist($author);
        $this->_em->flush();
    }
}
