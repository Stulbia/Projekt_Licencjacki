<?php

namespace App\Service;

use App\Dto\BookListFiltersDto;
use App\Dto\BookListInputFiltersDto;
use App\Dto\BookSearchFiltersDto;
use App\Dto\BookSearchInputFiltersDto;
use App\Entity\Enum\BookStatus;
use App\Entity\Book;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\BookRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class BookService.
 */
class BookService implements BookServiceInterface
{
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    public function __construct(
        private readonly string $targetDirectory,
        private readonly BookRepository $bookRepository,
        private readonly FileUploadServiceInterface $fileUploadService,
        private readonly Filesystem $filesystem,
        private readonly PaginatorInterface $paginator,
        private readonly TagServiceInterface $tagService
    ) {
    }

    public function getPaginatedUserList(int $page, UserInterface $author, BookListInputFiltersDto $filters): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->bookRepository->queryByAuthor($author, $filters),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    public function getPaginatedList(int $page, BookListInputFiltersDto $filters): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->bookRepository->queryAll($filters),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    public function getSearchList(int $page, BookSearchInputFiltersDto $filters): PaginationInterface
    {
        $filters = $this->prepareSearchFilters($filters);

        return $this->paginator->paginate(
            $this->bookRepository->querySearch($filters),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    public function save(Book $book, UploadedFile $uploadedFile, UserInterface $user): void
    {
        $bookFilename = $this->fileUploadService->upload($uploadedFile);
        $book->setAuthor($user);
        $book->setFilename($bookFilename);

        try {
            $this->bookRepository->save($book);
        } catch (OptimisticLockException|ORMException) {
        }
    }

    public function edit(Book $book): void
    {
        try {
            $this->bookRepository->save($book);
        } catch (OptimisticLockException|ORMException) {
        }
    }

    public function delete(Book $book): void
    {
        $filename = $book->getFilename();

        if (null !== $filename) {
            $this->filesystem->remove($this->targetDirectory.'/'.$filename);
        }

        $this->bookRepository->delete($book);
    }

    public function findByTags(array $tagName): array
    {
        return $this->bookRepository->findByTags($tagName);
    }

    private function prepareFilters(BookListInputFiltersDto $filters): BookListFiltersDto
    {
        return new BookListFiltersDto(
            tag: $filters->tagId ? $this->tagService->findOneById($filters->tagId) : null,
            bookStatus: BookStatus::tryFrom($filters->statusId)
        );
    }

    private function prepareSearchFilters(BookSearchInputFiltersDto $filters): BookSearchFiltersDto
    {
        return new BookSearchFiltersDto(
            tag: $filters->tagId ? $this->tagService->findOneById($filters->tagId) : null,
            bookStatus: BookStatus::tryFrom($filters->statusId),
            titlePattern: $filters->titleId,
            descriptionPattern: $filters->descriptionId,
        );
    }
}
