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
use Doctrine\ORM\OptimisticLockException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Service\AuthorServiceInterface;

class BookService implements BookServiceInterface
{
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    public function __construct(
        private readonly string $targetDirectory,
        private readonly BookRepository $bookRepository,
        private readonly FileUploadServiceInterface $fileUploadService,
        private readonly Filesystem $filesystem,
        private readonly PaginatorInterface $paginator,
        private readonly TagServiceInterface $tagService,
        private readonly AuthorServiceInterface $authorService,
        private readonly ReviewTagServiceInterface $reviewTagService
    ) {
    }

    /**
     * @param int $page
     * @param UserInterface $author
     * @param BookListInputFiltersDto $filters
     * @return PaginationInterface
     */
    public function getPaginatedUserList(int $page, UserInterface $author, BookListInputFiltersDto $filters): PaginationInterface
    {
        $parsedFilters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->bookRepository->queryByAuthor($author, $parsedFilters),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    public function getPaginatedList(int $page, BookListInputFiltersDto $filters): PaginationInterface
    {
        $parsedFilters = $this->prepareFilters($filters);
        return $this->paginator->paginate(
            $this->bookRepository->queryAll($parsedFilters),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    public function getSearchList(int $page, BookSearchInputFiltersDto $filters, int $items): PaginationInterface
    {
        $parsedFilters = $this->prepareSearchFilters($filters);
//       dump($parsedFilters); die;
    //    dump($this->bookRepository->querySearch($parsedFilters)); die;
        return $this->paginator->paginate(
            $this->bookRepository->querySearch($parsedFilters),
            $page,
            $items,
            [
                'wrap-queries' => true,
                'useOutputWalkers' => true,
            ]
        );
    }

    public function save(Book $book, UploadedFile $uploadedFile, UserInterface $user): void
    {
        $bookFilename = $this->fileUploadService->upload($uploadedFile);
        $book->setCoverFilename($bookFilename);

        try {
            $this->bookRepository->save($book);
        } catch (OptimisticLockException | ORMException) {
        }
    }

    public function edit(Book $book): void
    {
        try {
            $this->bookRepository->save($book);
        } catch (OptimisticLockException | ORMException) {
        }
    }

    public function delete(Book $book): void
    {
        $filename = $book->getFilename();

        if (null !== $filename) {
            $this->filesystem->remove($this->targetDirectory . '/' . $filename);
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
            bookStatus: BookStatus::tryFrom($filters->bookStatus),
            sortBy: ($filters->sortBy ?? 'id'),
        );
    }

    private function prepareSearchFilters(BookSearchInputFiltersDto $filters): BookSearchFiltersDto
    {
        return new BookSearchFiltersDto(
            tag: $filters->tagId ? $this->tagService->findOneById($filters->tagId) : null,
            bookStatus: BookStatus::tryFrom($filters->bookStatus),
            titlePattern: $filters->titlePattern,
            descriptionPattern: $filters->descriptionPattern,
            sortBy: $filters->sortBy ?? null,
            minRating: $filters->minRating ?? null,
            author: $filters->author ??  null,
            reviewTagIds: $filters->reviewTagId ? $this->reviewTagService->findOneById($filters->reviewTagId) : null
        );
    }
    public function findOneWithTags(string $slug): ?Book
    {
        return $this->bookRepository->findOneBySlugWithTags($slug);
    }


    /**
     * @param int $page
     * @param UserInterface $user
     * @param BookSearchInputFiltersDto $filters
     * @return PaginationInterface
     */
    public function getUserBooksList(int $page, UserInterface $user, BookSearchInputFiltersDto $filters): PaginationInterface
    {
        $parsedFilters = $this->prepareSearchFilters($filters);

        return $this->paginator->paginate(
            $this->bookRepository->queryForUserBooks($user, $parsedFilters),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'wrap-queries' => true,
                'useOutputWalkers' => true, // ← to jest kluczowe!
            ]
        );
    }
    public function findMostPopularBooks(int $page): PaginationInterface
    {
         return $this->paginator->paginate(
             $this->bookRepository->FindMostPopularBooks(),
             $page,
             5,
             [
             'wrap-queries' => true,
             'useOutputWalkers' => true,
             ]
         );
    }
}
