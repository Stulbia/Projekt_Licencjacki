<?php

namespace App\Service;

use App\Entity\Author;

interface AuthorServiceInterface
{
    public function findOneById(int $id): ?Author;

    /**
     * @return Author[]
     */
    public function findAll(): array;

    public function save(Author $author): void;

    public function delete(Author $author): void;
}
