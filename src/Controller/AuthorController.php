<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\Type\AuthorType;
use App\Repository\BookRepository;
use App\Service\AuthorServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/author', name: 'author_')]
class AuthorController extends AbstractController
{
    #[Route('/{id}', name: 'show', requirements: ['id' => '\\d+'])]
    public function show(
        Author $author,
        BookRepository $bookRepo,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
//        $qb = $bookRepo->createQueryBuilder('b')
//            ->setParameter('author', $author)
//            ->leftJoin('b.reviews', 'r')
//            ->having('COUNT(r.id) > 0')
//            ->groupBy('b.id')
//            ->addSelect('AVG(r.rating) AS avgRating')
////            ->orderBy('avgRating', 'DESC')
////            ->addOrderBy('b.id', 'DESC')
//            ->orderBy('b.title', 'ASC')
//            ->getQuery()
//            ->getResult();


        $rows = $bookRepo->createQueryBuilder('b')
            ->leftJoin('b.reviews', 'r')
            ->addSelect('AVG(r.rating) AS avgRating')   // visible scalar
            ->groupBy('b.id')
            ->having('COUNT(r.id) > 0')
            ->orderBy('b.title', 'ASC')
            ->addOrderBy('b.id', 'DESC')               // optional tie-breaker
            ->andWhere('b.author = :author')
            ->setParameter('author', $author)
            ->getQuery()
            ->getResult();

        $hydrated = $bookRepo->hydrateAvgRatingIntoBooks($rows);

//        $bookRepo->hydrateAvgRatingIntoBooks($qb);
        $pagination = $paginator->paginate(
            $hydrated,
            $request->query->getInt('page', 1),
            12
        );

//        var_dump(($pagination->getItems()));

        return $this->render('author/show.html.twig', [
            'author'     => $author,
            'pagination' => $pagination->getItems(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(
        Request $request,
        Author $author,
        AuthorServiceInterface $authorService
    ): Response {
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $form->get('photo')->getData();

            $authorService->update($author, $photo);
            $this->addFlash('success', 'Autor został zaktualizowany.');

            return $this->redirectToRoute('author_show', ['id' => $author->getId()]);
        }

        return $this->render('author/edit.html.twig', [
            'form' => $form->createView(),
            'author' => $author,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Author $author,
        AuthorServiceInterface $authorService
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $author->getId(), $request->request->get('_token'))) {
            $authorService->delete($author);
            $this->addFlash('success', 'Autor został usunięty.');
        }

        return $this->redirectToRoute('book_index');
    }
}
