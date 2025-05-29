<?php

namespace App\Service;

use App\Dto\BookListInputFiltersDto;
use App\Dto\BookSearchInputFiltersDto;
use App\Entity\Book;
use App\Entity\Tag;
use Doctrine\ORM\NonUniqueResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Interface BookServiceInterface.
 */
interface BookServiceInterface
{
    public function getPaginatedList(int $page, BookListInputFiltersDto $filters): PaginationInterface;

    public function getSearchList(int $page, BookSearchInputFiltersDto $filters): PaginationInterface;

    public function getPaginatedUserList(int $page, UserInterface $author, BookListInputFiltersDto $filters): PaginationInterface;

    public function save(Book $book, UploadedFile $uploadedFile, UserInterface $user): void;

    public function edit(Book $book): void;

    public function delete(Book $book): void;

    /**
     * @param Tag[] $tagName
     * @return Book[]
     */
    public function findByTags(array $tagName): array;
}
