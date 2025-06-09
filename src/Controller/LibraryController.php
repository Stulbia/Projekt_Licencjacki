<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\UserBookRelation;
use App\Form\Type\UserBookRelationType;
use App\Repository\UserBookRelationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/library')]
class LibraryController extends AbstractController
{
    #[Route('/add/{id}', name: 'library_add', methods: ['GET', 'POST'])]
    public function addToLibrary(
        Book $book,
        Request $request,
        EntityManagerInterface $em,
        UserBookRelationRepository $relationRepo
    ): Response {
        $user = $this->getUser();
        $relation = $relationRepo->findOneBy(['book' => $book, 'owner' => $user]);

        if (!$relation) {
            $relation = new UserBookRelation();
            $relation->setBook($book);
            $relation->setOwner($user);
        }

        $form = $this->createForm(UserBookRelationType::class, $relation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($relation);
            $em->flush();

            $this->addFlash('success', 'Dodano do Twojej biblioteczki!');
            return $this->redirectToRoute('book_show', ['slug' => $book->getSlug()]);
        }

        return $this->render('library/add.html.twig', [
        'book' => $book,
        'form' => $form->createView(),
        ]);
    }

    #[Route('/my-books', name: 'library_index')]
    public function myLibrary(UserBookRelationRepository $repo): Response
    {
        $relations = $repo->findBy(['owner' => $this->getUser()]);

        return $this->render('library/index.html.twig', [
        'relations' => $relations,
        ]);
    }
}
