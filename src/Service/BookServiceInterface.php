<?php

/**
 * Book service interface.
 */

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
    /**
     * Get paginated list for all books.
     *
     * @param int                      $page    Page number
     * @param BookListInputFiltersDto $filters Filter
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, BookListInputFiltersDto $filters): PaginationInterface;

    /**
     * Get paginated list for searched books.
     *
     * @param int                        $page    Page number
     * @param BookSearchInputFiltersDto $filters Filter
     *
     * @return PaginationInterface<string, mixed> Paginated list
     *
     * @throws NonUniqueResultException
     */
    public function getSearchList(int $page, BookSearchInputFiltersDto $filters): PaginationInterface;

    /**
     * Get paginated list.
     *
     * @param int                      $page    Page number
     * @param UserInterface            $author  author
     * @param BookListInputFiltersDto $filters Filter
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedUserList(int $page, UserInterface $author, BookListInputFiltersDto $filters): PaginationInterface;

    /**
     * Save book.
     *
     * @param Book         $book        Book entity
     * @param UploadedFile  $uploadedFile Uploaded file
     * @param UserInterface $user         User entity
     */
    public function save(Book $book, UploadedFile $uploadedFile, UserInterface $user): void;

    /**
     * Update book.
     *
     * @param Book $book Book entity
     */
    public function edit(Book $book): void;

    /**
     * Delete entity.
     *
     * @param Book $book Book entity
     */
    public function delete(Book $book): void;

    /**
     * Find Books by Tag Name.
     *
     * @param Tag[] $tagName Tag Name
     *
     * @return Book[]
     */
    public function findByTags(array $tagName): array;
}
