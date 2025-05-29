<?php

namespace App\Controller;

use App\Dto\BookListInputFiltersDto;
use App\Dto\BookSearchInputFiltersDto;
use App\Entity\Book;
use App\Form\Type\BookEditType;
use App\Form\Type\BookType;
use App\Form\Type\SearchBookType;
use App\Resolver\BookListInputFiltersDtoResolver;
use App\Resolver\BookSearchInputFiltersDtoResolver;
use App\Service\BookServiceInterface;
use App\Service\TagServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/book')]
class BookController extends AbstractController
{
    public function __construct(
        private readonly BookServiceInterface $bookService,
        private readonly TagServiceInterface $tagService,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(name: 'book_index', methods: 'GET')]
    public function index(
        #[MapQueryString(resolver: BookListInputFiltersDtoResolver::class)] BookListInputFiltersDto $filters,
        #[MapQueryParameter] int $page = 1
    ): Response {
        $pagination = $this->bookService->getPaginatedList($page, $filters);

        return $this->render('book/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/my-books', name: 'book_my_books', methods: 'GET')]
    #[IsGranted('ROLE_USER')]
    public function myBooks(
        #[MapQueryString(resolver: BookListInputFiltersDtoResolver::class)] BookListInputFiltersDto $filters,
        #[MapQueryParameter] int $page = 1
    ): Response {
        $user = $this->getUser();
        $pagination = $this->bookService->getPaginatedUserList($page, $user, $filters);

        return $this->render('book/my_books.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/search', name: 'book_search', methods: 'GET')]
    public function search(Request $request, #[MapQueryString(resolver: BookSearchInputFiltersDtoResolver::class)] BookSearchInputFiltersDto $filters, #[MapQueryParameter] int $page = 1): Response
    {
        $form = $this->createForm(SearchBookType::class, [
            'action' => $this->generateUrl('book_search'),
        ]);

        $pagination = $this->bookService->getSearchList($page, $filters);

        return $this->render('book/search.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/create', name: 'book_create', methods: 'GET|POST')]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        $book = new Book();
        $form = $this->createForm(BookType::class, $book, [
            'method' => 'POST',
            'action' => $this->generateUrl('book_create'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $this->bookService->save($book, $file, $user);
            $this->addFlash('success', $this->translator->trans('message.created_successfully'));

            return $this->redirectToRoute('book_index');
        }

        return $this->render('book/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'book_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    #[IsGranted('EDIT', subject: 'book')]
    public function edit(Request $request, Book $book): Response
    {
        $form = $this->createForm(BookEditType::class, $book, [
            'method' => 'PUT',
            'action' => $this->generateUrl('book_edit', ['id' => $book->getId()]),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->bookService->edit($book);
            $this->addFlash('success', $this->translator->trans('message.updated_successfully'));

            return $this->redirectToRoute('book_index');
        }

        return $this->render('book/edit.html.twig', [
            'form' => $form->createView(),
            'book' => $book,
        ]);
    }

    #[Route('/{id}/delete', name: 'book_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
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

    #[Route('/{id}', name: 'book_show', requirements: ['id' => '[1-9]\d*'], methods: 'GET')]
    public function show(Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }
}
