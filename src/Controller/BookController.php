<?php

/**
 * Book controller.
 */

namespace App\Controller;

use App\Dto\BookListInputFiltersDto;
use App\Dto\BookSearchInputFiltersDto;
use App\Entity\Comment;
use App\Entity\Book;
use App\Entity\Rating;
use App\Form\Type\CommentType;
use App\Form\Type\BookEditType;
use App\Form\Type\BookType;
use App\Form\Type\RatingType;
use App\Form\Type\SearchBookType;
use App\Resolver\BookListInputFiltersDtoResolver;
use App\Resolver\BookSearchInputFiltersDtoResolver;
use App\Service\CommentServiceInterface;
use App\Service\BookServiceInterface;
use App\Service\RatingServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class BookController.
 */
#[Route('/book')]
class BookController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param BookServiceInterface   $bookService   Book service
     * @param CommentServiceInterface $commentService Comment service
     * @param RatingServiceInterface  $ratingService  Rating service
     * @param TranslatorInterface     $translator     Translator
     */
    public function __construct(private readonly BookServiceInterface $bookService, private readonly CommentServiceInterface $commentService, private readonly RatingServiceInterface $ratingService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Index action.
     *
     * @param BookListInputFiltersDto $filters Input filters
     * @param int                      $page    Page number
     *
     * @return Response HTTP response
     */
    #[Route(name: 'book_index', methods: 'GET')]
    public function index(#[MapQueryString(resolver: BookListInputFiltersDtoResolver::class)] BookListInputFiltersDto $filters, #[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->bookService->getPaginatedList($page, $filters);

        return $this->render('book/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Top action.
     *
     * @param int $page Page number
     *
     * @return Response HTTP response
     */
    #[Route('/top', name: 'book_top', methods: 'GET')]
    public function top(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->ratingService->findBookOrder($page);

        return $this->render('book/top.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Search action.
     *
     * @param Request                    $request HTTP request
     * @param BookSearchInputFiltersDto $filters Input filters
     * @param int                        $page    Page number
     *
     * @return Response HTTP response
     */
    #[Route('/search', name: 'book_search', methods: 'GET')]
    public function search(Request $request, #[MapQueryString(resolver: BookSearchInputFiltersDtoResolver::class)] BookSearchInputFiltersDto $filters, #[MapQueryParameter] int $page = 1): Response
    {
        $form = $this->createForm(SearchBookType::class, ['action' => $this->generateUrl('book_search')]);
        $pagination = $this->bookService->getSearchList($page, $filters);
        if ($form->isSubmitted() && $form->isValid()) {
            $title = $form->getData();
            $description = $form->getData();
            $filters->descriptionId = $description;
            $filters->titleId = $title;

            return $this->render('book/search.html.twig', ['pagination' => $pagination, 'form' => $form->createView()]);
        }

        return $this->render('book/search.html.twig', ['pagination' => $pagination, 'form' => $form->createView()]);
    }

    /**
     * Show action.
     *
     * @param Book $book Book entity
     * @param int   $page  Page number
     *
     * @return Response HTTP response
     */
    #[Route('/{id}', name: 'book_show', requirements: ['id' => '[1-9]\d*'], methods: 'GET')]
    public function show(Book $book, #[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->commentService->findByBook($book, $page);
        $rating = $this->ratingService->findAverageRatingByBook($book);

        return $this->render('book/show.html.twig', ['book' => $book, 'pagination' => $pagination, 'rating' => $rating]);
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/create', name: 'book_create', methods: 'GET|POST')]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        $book = new Book();
        $book->setAuthor($user);
        $book->setFilename(' ');
        $form = $this->createForm(BookType::class, $book, ['method' => 'POST', 'action' => $this->generateUrl('book_create')]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $gallery = $book->getGallery();
            if ($this->isGranted('EDIT', $gallery)) {
                $this->bookService->save($book, $file, $user);

                $this->addFlash('success', $this->translator->trans('message.created_successfully'));
            } else {
                $this->addFlash('error', $this->translator->trans('message.incorrect_gallery'));
            }

            return $this->redirectToRoute('book_index');
        }

        return $this->render('book/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Book   $book   Book entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'book_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    #[IsGranted('EDIT', subject: 'book')]
    public function edit(Request $request, Book $book): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(BookEditType::class, $book, [
            'method' => 'PUT',
            'action' => $this->generateUrl('book_edit', ['id' => $book->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $gallery = $book->getGallery();
            if ($this->isGranted('EDIT', $gallery)) {
                $this->bookService->edit($book);

                $this->addFlash('success', $this->translator->trans('message.created_successfully'));
            } else {
                $this->addFlash('error', $this->translator->trans('message.incorrect_gallery'));
            }

            return $this->redirectToRoute('book_index');
        }

        return $this->render('book/edit.html.twig', ['form' => $form->createView(), 'book' => $book]);
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Book   $book   Book entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'book_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
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

        return $this->render('book/delete.html.twig', ['form' => $form->createView(), 'book' => $book]);
    }

    /**
     * Leave a Comment.
     *
     * @param Request $request HTTP request
     * @param Book   $book   Book entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/comment', name: 'comment_create', methods: 'GET|POST')]
    #[IsGranted('ROLE_USER')]
    public function comment(Request $request, Book $book): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $this->commentService->save($comment, $user, $book);

            $this->addFlash('success', $this->translator->trans('message.created_successfully'));

            return $this->redirectToRoute('book_show', ['id' => $book->getId()]);
        }

        return $this->render('book/comment.html.twig', ['form' => $form->createView(), 'book' => $book]);
    }

    /**
     * Leave a Rating.
     *
     * @param Request $request HTTP request
     * @param Book   $book   Book entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/rating', name: 'rating', methods: 'GET|POST')]
    #[IsGranted('ROLE_USER')]
    public function rate(Request $request, Book $book): Response
    {
        $user = $this->getUser();
        $rating = $this->ratingService->findByUserAndBook($user, $book);
        if (null !== $rating) {
            $this->ratingService->delete($rating);
        }
        $rating = new Rating();
        $form = $this->createForm(RatingType::class, $rating);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $this->ratingService->save($rating, $user, $book);

            $this->addFlash('success', $this->translator->trans('message.created_successfully'));

            return $this->redirectToRoute('book_show', ['id' => $book->getId()]);
        }

        return $this->render('book/rate.html.twig', ['form' => $form->createView(), 'book' => $book]);
    }

    /**
     * List of ratings for a book.
     *
     * @param Book $book Book entity
     * @param int   $page  Page
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/rating/list', name: 'rating_list', methods: 'GET|POST')]
    #[IsGranted('ROLE_ADMIN')]
    public function ratingList(Book $book, #[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->ratingService->findByBook($book, $page);

        return $this->render('book/rating_list.html.twig', ['pagination'  => $pagination, 'book' => $book]);
    }

    /**
     * Deletes a given rating.
     *
     * @param Request $request HTTP request
     * @param Rating  $rating  Rating entity
     *
     * @return Response HTTP response
     */
    #[Route('/rating/{id}', name: 'rating_delete', methods: 'GET|DELETE')]
    #[IsGranted('ROLE_ADMIN')]
    public function ratingDelete(Request $request, Rating $rating): Response
    {
        $form = $this->createForm(FormType::class, $rating, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('rating_delete', ['id' => $rating->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $book = $rating->getBook();
            $this->ratingService->delete($rating);

            $this->addFlash('success', $this->translator->trans('message.deleted_successfully'));

            return $this->redirectToRoute('rating_list', ['id' => $book->getId()]);
        }

        return $this->render('book/rating_delete.html.twig', ['form' => $form->createView(), 'rating' => $rating]);
    }
}
