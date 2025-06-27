<?php

namespace App\Controller;

use App\Dto\BookSearchInputFiltersDto;
use App\Entity\Book;
use App\Entity\UserBookRelation;
use App\Form\Type\SearchBookType;
use App\Form\Type\UserBookRelationType;
use App\Repository\UserBookRelationRepository;
use App\Resolver\BookSearchInputFiltersDtoResolver;
use App\Service\ReviewServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/library')]
class LibraryController extends AbstractController
{
    public function __construct(
        private readonly ReviewServiceInterface $reviewService
    ) {
    }
    #[Route('/add/{id}', name: 'library_add', methods: ['GET', 'POST'])]
    public function addToLibrary(
        Book $book,
        Request $request,
        EntityManagerInterface $em,
        UserBookRelationRepository $relationRepo
    ): Response {
        $user = $this->getUser();
        $relation = $relationRepo->findOneBy(['book' => $book, 'owner' => $user]);
        $avg = $this->reviewService->avgRating($book->getId());
        foreach ($book->getReviews() as $review) {
            if ($user && $review->getAuthor() === $user) {
                $userReview = $review;
            } else {
                $otherReviews[] = $review;
            }
        }
        if (!$relation) {
            $relation = new UserBookRelation();
            $relation->setBook($book);
            $relation->setOwner($user);
        }

        $form = $this->createForm(UserBookRelationType::class, $relation);
        $form->handleRequest($request);

        $userReview = null;
        $otherReviews = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($relation);
            $em->flush();

            $this->addFlash('success', 'Dodano do Twojej biblioteczki!');

            return $this->redirectToRoute('book_show', ['slug' => $book->getSlug(), 'avg' => $avg]);
        }

        return $this->render('library/add.html.twig', [
        'book' => $book,
        'form' => $form->createView(),
            'avg' => $avg,
        'formVisible' => true, 'hasUserReview' => $userReview !== null, 'otherReviews' => $otherReviews,
            'inLibrary' => true,
        ]);
    }

    /**
     * @param BookSearchInputFiltersDto $filters
     * @param int $page
     * @param UserBookRelationRepository $relationRepo
     * @param PaginatorInterface $paginator
     * @return Response
     */
    #[Route('/my-books', name: 'library_index')]
    public function myLibrary(
        Request $request,
        #[MapQueryString(resolver: BookSearchInputFiltersDtoResolver::class)] BookSearchInputFiltersDto $filters,
        UserBookRelationRepository $relationRepo,
        PaginatorInterface $paginator,
        #[MapQueryParameter] int $page = 1
    ): Response {

        $user = $this->getUser();

        $form = $this->createForm(SearchBookType::class, [
            'action' => $this->generateUrl('library_index'),
        ]);
        $query = $relationRepo->getBooksByUserWithFilters($user, $filters);
        $pagination = $paginator->paginate($query, $page, 8);

        return $this->render('library/index.html.twig', [
            'form' => $form->createView(),
            'pagination' => $pagination,
            'filters' => $filters,
        ]);
    }
}
