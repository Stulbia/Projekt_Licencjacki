<?php

namespace App\Service;

use App\Entity\Author;
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
}
