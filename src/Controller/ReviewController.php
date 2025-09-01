<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\ReviewTag;
use App\Entity\ReviewTagAssignment;
use App\Entity\User;
use App\Form\Type\ReviewSearchFiltersType;
use App\Form\Type\ReviewType;
use App\Repository\BookRepository;
use App\Repository\UserBookRelationRepository;
use App\Service\ReviewServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Dto\ReviewSearchFiltersDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/review')]
class ReviewController extends AbstractController
{
    public function __construct(
        private readonly ReviewServiceInterface $reviewService,
        private readonly TranslatorInterface $translator,
        private readonly UserBookRelationRepository $relationRepo
    ) {
    }

    #[Route(name: 'review_index', methods: 'GET')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $page = $request->query->getInt('page', 1);

//        $pagination = $this->reviewService->getPaginatedList($request->query->getInt('page', 1));
        if ($user) {
            $pagination = $this->reviewService->getPaginatedUserList($page, $user);
        } else {
            return $this->redirectToRoute('login');
        }
        return $this->render('review/my_reviews.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/create', name: 'review_create')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function create(Request $request, BookRepository $bookRepo, EntityManagerInterface $em): Response
    {
        $bookId = $request->query->getInt('bookId');
        $book = $bookRepo->find($bookId);
        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        $avg = $this->reviewService->avgRating($book->getId());
        $currentUser = $this->getUser();

        $review = (new Review())
            ->setBook($book)
            ->setAuthor($currentUser);

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($review);
            $em->flush();

            return $this->redirectToRoute('book_show', ['slug' => $book->getSlug()]);
        }
        $user = $this->getUser();
        $inlibrary = (bool) $this->relationRepo->findOneBy(['book' => $book, 'owner' => $user]);
        return $this->render('review/create.html.twig', [
            'form' => $form->createView(),
            'book' => $book,
            'avg' => $avg,
            'formVisibleReview' => true,
            'hasUserReview' => false,
            'userReview' => null,
            'otherReviews' => $book->getReviews(),
            'inLibrary' => $inlibrary,
        ]);
    }

    #[Route('/{id}/edit', name: 'review_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'PUT'])]
    #[IsGranted('EDIT', subject: 'review')]
    public function edit(Request $request, Review $review): Response
    {
        $form = $this->createForm(ReviewType::class, $review, [
            'method' => 'PUT',
            'action' => $this->generateUrl('review_edit', ['id' => $review->getId()]),
        ]);

        $existingTags = $review->getTagAssignments()->map(fn(ReviewTagAssignment $a) => $a->getTag())->toArray();
        $form->get('reviewTags')->setData($existingTags);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($review->getTagAssignments() as $assignment) {
                $review->removeTagAssignment($assignment);
            }

            $selectedTags = $form->get('reviewTags')->getData();
            foreach ($selectedTags as $tag) {
                $assignment = new ReviewTagAssignment();
                $assignment->setReview($review);
                $assignment->setTag($tag);
                $review->addTagAssignment($assignment);
            }

            $this->reviewService->edit($review);
            $this->addFlash('success', $this->translator->trans('message.updated_successfully'));

            return $this->redirectToRoute('book_show', ['slug' => $review->getBook()->getSlug()]);
        }
        $user = $this->getUser();
        $book  = $review->getBook();
        $inlibrary = (bool) $this->relationRepo->findOneBy(['book' => $book, 'owner' => $user]);

        return $this->render('review/edit.html.twig', [
            'form' => $form->createView(),
            'book' => $book,
            'avg' => $this->reviewService->avgRating($review->getBook()->getId()),
            'formVisibleReview' => true,
            'hasUserReview' => true,
            'review' => $review,
            'otherReviews' => $review->getBook()->getReviews()->filter(fn(Review $r) => $r !== $review),
            'inLibrary' => $inlibrary,
        ]);
    }

    #[Route('/{id}/delete', name: 'review_delete', requirements: ['id' => '\\d+'], methods: ['GET', 'DELETE'])]
    #[IsGranted('DELETE', subject: 'review')]
    public function delete(Request $request, Review $review): Response
    {
        $form = $this->createForm(FormType::class, $review, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('review_delete', ['id' => $review->getId()]),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->reviewService->delete($review);
            $this->addFlash('success', $this->translator->trans('message.deleted_successfully'));

            return $this->redirectToRoute('book_show', ['slug' => $review->getBook()->getSlug()]);
        }

        $user = $this->getUser();
        $book  = $review->getBook();
        $inlibrary = (bool) $this->relationRepo->findOneBy(['book' => $book, 'owner' => $user]);
        return $this->render('review/delete.html.twig', [
            'form' => $form->createView(),
            'book' => $review->getBook(),
            'avg' => $this->reviewService->avgRating($review->getBook()->getId()),
            'formVisibleReview' => true,
            'hasUserReview' => true,
            'review' => $review,
            'otherReviews' => $review->getBook()->getReviews()->filter(fn(Review $r) => $r !== $review),
            'inLibrary' => $inlibrary,
            ]);
    }

    #[Route('/search', name: 'review_search')]
    public function search(Request $request, PaginatorInterface $paginator, FormFactoryInterface $formFactory): Response
    {
        $filtersDto = new ReviewSearchFiltersDto();
        $form = $formFactory->create(ReviewSearchFiltersType::class, $filtersDto, [
            'method' => 'GET',
        ]);
        $form->handleRequest($request);

        $query = $this->reviewService->queryByFilters($filtersDto);
        $pagination = $paginator->paginate($query, $request->query->getInt('page', 1), 10);

        return $this->render('review/search.html.twig', [
            'pagination' => $pagination,
            'filtersForm' => $form->createView(),
        ]);
    }
}
