<?php

namespace App\Service;

use App\Entity\Author;
use App\Entity\Book;

interface AuthorServiceInterface
{
    public function findOneById(int $id): ?Author;

    public function findAll(): array;

    public function save(Author $author): void;

    public function delete(Author $author): void;

/** @return Author[] */
    public function findAuthorsWithBooks(): array;

/** @return Book[] */
    public function findBooksByAuthor(Author $author): array;
}
