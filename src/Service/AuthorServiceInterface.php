<?php

namespace App\Service;

use App\Entity\Author;
use App\Entity\Book;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface AuthorServiceInterface
{
    public function findOneById(int $id): ?Author;
    public function findByName(string $name): array;
    public function findAll(): array;

    public function save(Author $author): void;

    public function delete(Author $author): void;
    public function update(Author $author, ?UploadedFile $photo = null): void;

/** @return Author[] */
    public function findAuthorsWithBooks(): array;

/** @return Book[] */
    public function findBooksByAuthor(Author $author): array;
}
