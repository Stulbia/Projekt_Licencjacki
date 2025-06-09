<?php

namespace App\Repository;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
