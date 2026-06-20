<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\Type\BookEditType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/book/administer')]
class BookEditController extends AbstractController
{
    public function __construct(
        private readonly string $coversDir
    ) {
    }

    /**
     * @param BookRepository $bookRepository
     * @return Response
     */
    #[Route('/' , name: 'book_admin_index', methods: ['GET'])]
    public function index(BookRepository $bookRepository): Response
    {
        return $this->render('book_admin/index.html.twig', [
            'books' => $bookRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $book = new Book();
        $form = $this->createForm(BookEditType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $coverFile */
            $coverFile = $form->get('cover')->getData();
            if ($coverFile) {
                $newFilename = uniqid() . '.' . $coverFile->guessExtension();
                $coverFile->move($this->coversDir, $newFilename);
                $book->setCoverFilename($newFilename);
            }

            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('book_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('book_admin/new.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_show', methods: ['GET'])]
    public function show(Book $book): Response
    {
        return $this->render('book_admin/show.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/{id}/edit', name: 'book_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BookEditType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $coverFile */
            $coverFile = $form->get('cover')->getData();
            if ($coverFile) {
                $newFilename = uniqid() . '.' . $coverFile->guessExtension();
                $coverFile->move($this->coversDir, $newFilename);
                $book->setCoverFilename($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('book_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('book_admin/edit.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'book_admin_delete', methods: ['POST'])]
    public function delete(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $book->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($book);
            $entityManager->flush();
        }

        return $this->redirectToRoute('book_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
