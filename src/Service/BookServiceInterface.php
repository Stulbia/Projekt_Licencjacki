<?php

namespace App\Service;

use App\Dto\BookListInputFiltersDto;
use App\Dto\BookSearchInputFiltersDto;
use App\Entity\Book;
use App\Entity\Tag;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;

/**
* Interface BookServiceInterface.
*
* Filtracja obsługuje m.in. tagi, statusy i sortowanie (np. po ratingu).
*/
interface BookServiceInterface
{
/**
* Lista książek (ogólna), paginowana.
* Obsługuje: tag, status, sortBy.
*/
    public function getPaginatedList(int $page, BookListInputFiltersDto $filters): PaginationInterface;

/**
* Wyszukiwanie książek (title/description + sortBy).
*/
    public function getSearchList(int $page, BookSearchInputFiltersDto $filters): PaginationInterface;

/**
* Lista książek użytkownika.
*/
    public function getPaginatedUserList(int $page, UserInterface $author, BookListInputFiltersDto $filters): PaginationInterface;

/**
* Zapis książki z uploadem pliku i przypisaniem autora.
*/
    public function save(Book $book, UploadedFile $uploadedFile, UserInterface $user): void;

/**
* Edycja książki bez uploadu.
*/
    public function edit(Book $book): void;

/**
* Usunięcie książki i pliku.
*/
    public function delete(Book $book): void;

/**
* Znajdź książki po tagach.
*
* @param Tag[] $tagName
* @return Book[]
*/
    public function findByTags(array $tagName): array;

/**
* Pobierz książkę po slug z tagami i recenzjami.
*/
    public function findOneWithTags(string $slug): ?Book;
}
