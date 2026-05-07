<?php

namespace App\Controller;

use App\Dto\BookListInputFiltersDto;
use App\Dto\BookSearchInputFiltersDto;
use App\Entity\Book;
use App\Entity\User;
use App\Form\Type\BookCoverType;
use App\Form\Type\BookEditType;
use App\Form\Type\BookType;
use App\Form\Type\SearchBookType;
use App\Repository\UserBookRelationRepository;
use App\Resolver\BookListInputFiltersDtoResolver;
use App\Resolver\BookSearchInputFiltersDtoResolver;
use App\Service\BookServiceInterface;
use App\Service\FileUploadService;
use App\Service\ReviewServiceInterface;
use App\Service\TagServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\DocBlock\Tags\Author;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\FileUploadServiceInterface;

#[Route('/book')]
class BookController extends AbstractController
{
    public function __construct(
        private readonly BookServiceInterface $bookService,
        private readonly TagServiceInterface $tagService,
        private readonly TranslatorInterface $translator,
        private readonly ReviewServiceInterface $reviewService,
        private readonly UserBookRelationRepository $relationRepo
    ) {
    }

    #[Route(name: 'book_index', methods: ['GET'])]
    public function index(
        #[MapQueryString(resolver: BookListInputFiltersDtoResolver::class)] BookListInputFiltersDto $filters,
        #[MapQueryParameter] int $page = 1
    ): Response {
        $pagination = $this->bookService->getPaginatedList($page, $filters);
        return $this->render('book/index.html.twig', [
            'pagination' => $pagination,
            'sortBy' => $filters->sortBy,
        ]);
    }

    #[Route('/search', name: 'book_search', methods: ['GET'])]
    public function search(
        \Symfony\Component\HttpFoundation\Request $request ,
        #[MapQueryString(resolver: BookSearchInputFiltersDtoResolver::class)] BookSearchInputFiltersDto $filters,
        #[MapQueryParameter] int $page = 1
    ): Response {
        $form = $this->createForm(SearchBookType::class, [
            'action' => $this->generateUrl('book_search'),
        ]);


        $session  = $request->getSession();
        $perPage  = $request->query->getInt('pp', $session->get('pp', 4));
        $perPage  = max(1, min(60, $perPage));
        $session->set('pp', $perPage);


        $pagination = $this->bookService->getSearchList($page, $filters, $perPage);

        return $this->render('book/search.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView(),
            'sortBy' => $filters->sortBy,
            'perPage'    => $perPage,
        ]);
    }

    #[Route('/my_books', name: 'my_books', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function myBooks(
        #[MapQueryString(resolver: BookSearchInputFiltersDtoResolver::class)] BookSearchInputFiltersDto $filters,
        #[MapQueryParameter] int $page = 1
    ): Response {
        $form = $this->createForm(SearchBookType::class, [
            'action' => $this->generateUrl('book_search'),
        ]);

        $user = $this->getUser();

        $pagination = $this->bookService->getUserBooksList($page, $user, $filters);

        return $this->render('book/search.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView(),
            'sortBy' => $filters->sortBy,
        ]);
    }

    /**
     * @param Book $book
     * @param Request $request
     * @param FileUploadService $uploadService
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/{id}/cover', name: 'book_cover_edit')]
    public function updateCover(Book $book, Request $request, FileUploadService $uploadService, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(BookCoverType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('cover')->getData();
            if ($file) {
                $filename = $uploadService->upload($file);
                $book->setCoverFilename($filename);
                $em->flush();
                $this->addFlash('success', 'Okładka zaktualizowana!');
                return $this->redirectToRoute('book_show', ['slug' => $book->getSlug()]);
            }
        }

        return $this->render('book/cover_edit.html.twig', [
            'form' => $form->createView(),
            'book' => $book,
        ]);
    }

    #[Route('/{id}/delete', name: 'book_delete', requirements: ['id' => '\d+'], methods: ['GET', 'DELETE'])]
    #[IsGranted('DELETE', subject: 'book')]
    public function delete(Request $request, Book $book): Response
    {
        $form = $this->createForm(FormType::class, $book, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('book_delete', ['id' => $book->getId()]),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->bookService->delete($book);
            $this->addFlash('success', $this->translator->trans('message.deleted_successfully'));

            return $this->redirectToRoute('book_index');
        }

        return $this->render('book/delete.html.twig', [
            'form' => $form->createView(),
            'book' => $book,
        ]);
    }

    #[Route('/{slug}', name: 'book_show', requirements: ['slug' => '[a-zA-Z0-9\-]+'], methods: ['GET'])]
    public function show(string $slug): Response
    {
        $book = $this->bookService->findOneWithTags($slug);

        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        $avg = $this->reviewService->avgRating($book->getId());
        $user = $this->getUser();

        $userReview = null;
        $otherReviews = [];

        foreach ($book->getReviews() as $review) {
            if ($user && $review->getAuthor() === $user) {
                $userReview = $review;
            } else {
                $otherReviews[] = $review;
            }
        }
        $inlibrary = (bool) $this->relationRepo->findOneBy(['book' => $book, 'owner' => $user]);

        return $this->render('book/show.html.twig', [
            'book' => $book,
            'avg' => $avg,
            'hasUserReview' => $userReview !== null,
            'userReview' => $userReview,
            'otherReviews' => $otherReviews,
            'inLibrary' => $inlibrary,
        ]);
    }
}
