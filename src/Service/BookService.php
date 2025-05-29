<?php

/**
 * Book service.
 */

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
    /**
     * Constructor.
     *
     * @param string                     $targetDirectory   Target directory
     * @param BookRepository            $bookRepository   Book repository
     * @param FileUploadServiceInterface $fileUploadService File upload service
     * @param Filesystem                 $filesystem        Filesystem component
     * @param PaginatorInterface         $paginator         Paginator
     * @param TagServiceInterface        $tagService        Tag service
     * @param GalleryServiceInterface    $galleryService    Gallery service
     */
    public function __construct(private readonly string $targetDirectory, private readonly BookRepository $bookRepository, private readonly FileUploadServiceInterface $fileUploadService, private readonly Filesystem $filesystem, private readonly PaginatorInterface $paginator, private readonly TagServiceInterface $tagService, private readonly GalleryServiceInterface $galleryService)
    {
    }

    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Get paginated list for user books.
     *
     * @param int                      $page    Page number
     * @param User                     $author  Book author
     * @param BookListInputFiltersDto $filters Filter
     *
     * @return PaginationInterface<string, mixed> Paginated list
     *
     * @throws NonUniqueResultException
     */
    public function getPaginatedUserList(int $page, UserInterface $author, BookListInputFiltersDto $filters): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->bookRepository->queryByAuthor($author, $filters),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Get paginated list for all books.
     *
     * @param int                      $page    Page number
     * @param BookListInputFiltersDto $filters Filter
     *
     * @return PaginationInterface<string, mixed> Paginated list
     *
     * @throws NonUniqueResultException
     */
    public function getPaginatedList(int $page, BookListInputFiltersDto $filters): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->bookRepository->queryAll($filters),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

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
    public function getSearchList(int $page, BookSearchInputFiltersDto $filters): PaginationInterface
    {
        $filters = $this->prepareSearchFilters($filters);

        return $this->paginator->paginate(
            $this->bookRepository->querySearch($filters),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save book.
     *
     * @param Book         $book        Book entity
     * @param UploadedFile  $uploadedFile Uploaded file
     * @param UserInterface $user         User entity
     */
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

    /**
     * Update book.
     *
     * @param Book $book Book entity
     */
    public function edit(Book $book): void
    {
        try {
            $this->bookRepository->save($book);
        } catch (OptimisticLockException|ORMException) {
        }
    }

    /**
     * Delete book.
     *
     * @param Book $book Book entity
     *
     * @throws ORMException             if an ORM error occurs
     * @throws OptimisticLockException  if a version conflict occurs
     * @throws InvalidArgumentException if the provided tag is invalid
     */
    public function delete(Book $book): void
    {
        $filename = $book->getFilename();
        if (null !== $filename) {
            $this->filesystem->remove($this->targetDirectory.'/'.$filename);
        }
        $this->bookRepository->delete($book);
    }

    /**
     * Find Books by Tag Name.
     *
     * @param Tag[] $tagName Tag Name
     *
     * @return Book[]
     */
    public function findByTags(array $tagName): array
    {
        return $this->bookRepository->findByTags($tagName);
    }

    /**
     * Prepare filters for the books list.
     *
     * @param BookListInputFiltersDto $filters Raw filters from request
     *
     * @return BookListFiltersDto Result filters
     *
     * @throws NonUniqueResultException
     */
    private function prepareFilters(BookListInputFiltersDto $filters): BookListFiltersDto
    {
        return new BookListFiltersDto(
            null !== $filters->galleryId ? $this->galleryService->findOneById($filters->galleryId) : null,
            null !== $filters->tagId ? $this->tagService->findOneById($filters->tagId) : null,
            BookStatus::tryFrom($filters->statusId)
        );
    }

    /**
     * Prepare filters for the search books list.
     *
     * @param BookSearchInputFiltersDto $filters Raw filters from request
     *
     * @return BookSearchFiltersDto Result filters
     *
     * @throws NonUniqueResultException
     */
    private function prepareSearchFilters(BookSearchInputFiltersDto $filters): BookSearchFiltersDto
    {
        return new BookSearchFiltersDto(
            null !== $filters->galleryId ? $this->galleryService->findOneById($filters->galleryId) : null,
            null !== $filters->tagId ? $this->tagService->findOneById($filters->tagId) : null,
            BookStatus::tryFrom($filters->statusId),
            $filters->titleId,
            $filters->descriptionId,
        );
    }
}
