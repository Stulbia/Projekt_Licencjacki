<?php

namespace App\Service;

use App\Entity\Author;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class AuthorService implements AuthorServiceInterface
{
    public function __construct(
        private readonly AuthorRepository $authorRepository
    ) {
    }

    public function findOneById(int $id): ?Author
    {
        return $this->authorRepository->find($id);
    }

    public function findAll(): array
    {
        return $this->authorRepository->findAll();
    }

    public function save(Author $author): void
    {
        try {
            $this->authorRepository->save($author);
        } catch (ORMException | OptimisticLockException) {
            // log error if needed
        }
    }

    public function delete(Author $author): void
    {
        try {
            $this->authorRepository->delete($author);
        } catch (ORMException | OptimisticLockException) {
            // log error if needed
        }
    }

    /**
     * Zwraca autorów mających książki.
     *
     * @return Author[]
     */
    public function findAuthorsWithBooks(): array
    {
        return $this->authorRepository->findAuthorsWithBooks();
    }

    /**
     * Zwraca książki danego autora.
     *
     * @param Author $author
     * @return Book[]
     */
    public function findBooksByAuthor(Author $author): array
    {
        return $this->authorRepository->findBooksByAuthor($author);
    }
}
