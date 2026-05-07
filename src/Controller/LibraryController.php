<?php

namespace App\Controller;

use App\Dto\BookSearchInputFiltersDto;
use App\Entity\Book;
use App\Entity\UserBookRelation;
use App\Enum\ReadingStatus;
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
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

        foreach ($book->getReviews() as $review) {
            if ($user && $review->getAuthor() === $user) {
                $userReview = $review;
            } else {
                $otherReviews[] = $review;
            }
        }








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
        dump($query);
        die;
        $pagination = $paginator->paginate(
            $query,
            $page,
            8,
        [
            'defaultSortFieldName' => 'b.title',
            'defaultSortDirection' => 'asc',
            'sortFieldAllowList' => [
                'b.title',
                'a.name',
                'relation.createdAt',
                'relation.status',
                'avg_rating', // HIDDEN scalar from repo
            ],
        ],
    );

        return $this->render('library/index.html.twig', [
            'form' => $form->createView(),
            'pagination' => $pagination,
            'filters' => $filters,
        ]);
    }








    #[Route('delete/{id}', name: 'library_remove', methods: ['POST'])]
    public function removeFromLibrary(
        Book $book,
        Request $request,
        EntityManagerInterface $em,
        UserBookRelationRepository $relationRepo
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        // Find the (user, book) relation
        $relation = $relationRepo->findOneBy(['book' => $book, 'owner' => $user]);

        if (!$relation) {
            $this->addFlash('info', 'Tej książki nie ma w Twojej biblioteczce.');
            return $this->redirectToRoute('book_show', ['id' => $book->getId(), 'slug' => $book->getSlug()]);
        }

        $em->remove($relation);
        $em->flush();

        $this->addFlash('success', 'Usunięto książkę z Twojej biblioteczki.');
        return $this->redirectToRoute('book_show', ['id' => $book->getId(), 'slug' => $book->getSlug()]);
    }

    #[Route('remove/{id}', name: 'library_delete', methods: ['POST'])]
    public function deleteFromLibrary(
        Book $book,
        Request $request,
        EntityManagerInterface $em,
        UserBookRelationRepository $relationRepo
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        // Find the (user, book) relation
        $relation = $relationRepo->findOneBy(['book' => $book, 'owner' => $user]);

        if (!$relation) {
            $this->addFlash('info', 'Tej książki nie ma w Twojej biblioteczce.');
            return $this->redirectToRoute('library_index', ['id' => $book->getId(), 'slug' => $book->getSlug()]);
        }

        $em->remove($relation);
        $em->flush();

        $this->addFlash('success', 'Usunięto książkę z Twojej biblioteczki.');
        return $this->redirectToRoute('library_index');
    }








    #[Route('/status/{id}', name: 'library_change_status', methods: ['POST', 'GET'])]
    public function changeStatus(
        Book $book,
        Request $request,
        EntityManagerInterface $em,
        UserBookRelationRepository $relationRepo
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        // CSRF
        if (!$this->isCsrfTokenValid('lib_status_' . $book->getId(), (string)$request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        // Validate status sent from buttons
        $status = $relationRepo->findOneBy(['book' => $book, 'owner' => $user]) ->getStatus()->label();
        $statusRaw = (string)$status;
        $statusRaw = (string)$request->request->get('status', '');
        try {
            $status = ReadingStatus::from($statusRaw);
        } catch (\ValueError $e) {
            $this->addFlash('danger', 'Nieprawidłowy status.');
            return $this->redirectBack($request, $book);
        }

        // Find (or create) the relation
        $relation = $relationRepo->findOneBy(['book' => $book, 'owner' => $user]);
        if (!$relation) {
            $this->addFlash('info', 'Tej książki nie ma jeszcze w Twojej biblioteczce.');
            return $this->redirectBack($request, $book);
        }

        $relation->setStatus($status);
        {{var_dump($relation->getStatus());}}
        {{var_dump($status);}}
        $em->flush();

        $this->addFlash('success', 'Zaktualizowano status.');
        return $this->redirectBack($request, $book);
    }

    private function redirectBack(Request $request, Book $book): Response
    {
        // Prefer going back to the calling page
        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }
        // Fallback to book page
        return $this->redirectToRoute('book_show', [
            'id' => $book->getId(),
            'slug' => $book->getSlug(),
        ]);
    }















}