<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\ReviewTag;
use App\Entity\ReviewTagAssignment;
use App\Form\Type\ReviewSearchFiltersType;
use App\Form\Type\ReviewSearchType;
use App\Form\Type\ReviewType;
use App\Service\ReviewServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Dto\ReviewSearchFiltersDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/review')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class ReviewController extends AbstractController
{
    public function __construct(
        private readonly ReviewServiceInterface $reviewService,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(name: 'review_index', methods: 'GET')]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->reviewService->getPaginatedList($page);

        return $this->render('review/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/my-reviews', name: 'review_my_reviews', methods: 'GET')]
    public function myReviews(#[MapQueryParameter] int $page = 1): Response
    {
        $user = $this->getUser();
        $pagination = $this->reviewService->getPaginatedUserList($page, $user);

        return $this->render('review/my_reviews.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/create', name: 'review_create', methods: 'GET|POST')]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        $review = new Review();

        $form = $this->createForm(ReviewType::class, $review, [
            'method' => 'POST',
            'action' => $this->generateUrl('review_create'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ReviewTag[] $selectedTags */
            $selectedTags = $form->get('reviewTags')->getData();

            foreach ($selectedTags as $tag) {
                $assignment = new ReviewTagAssignment();
                $assignment->setReview($review);
                $assignment->setTag($tag);
                $review->addTagAssignment($assignment);
            }

            $this->reviewService->save($review, $user);
            $this->addFlash('success', $this->translator->trans('message.created_successfully'));

            return $this->redirectToRoute('review_index');
        }

        return $this->render('review/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'review_edit', requirements: ['id' => '\\d+'], methods: 'GET|PUT')]
    #[IsGranted('EDIT', subject: 'review')]
    public function edit(Request $request, Review $review): Response
    {
        $form = $this->createForm(ReviewType::class, $review, [
            'method' => 'PUT',
            'action' => $this->generateUrl('review_edit', ['id' => $review->getId()]),
        ]);

        // Pre-fill existing tag data
        $existingTags = $review->getTagAssignments()->map(fn(ReviewTagAssignment $a) => $a->getTag())->toArray();
        $form->get('reviewTags')->setData($existingTags);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Clear old assignments
            foreach ($review->getTagAssignments() as $assignment) {
                $review->removeTagAssignment($assignment);
            }

            // Add new ones
            /** @var ReviewTag[] $selectedTags */
            $selectedTags = $form->get('reviewTags')->getData();

            foreach ($selectedTags as $tag) {
                $assignment = new ReviewTagAssignment();
                $assignment->setReview($review);
                $assignment->setTag($tag);
                $review->addTagAssignment($assignment);
            }

            $this->reviewService->edit($review);
            $this->addFlash('success', $this->translator->trans('message.updated_successfully'));

            return $this->redirectToRoute('review_index');
        }

        return $this->render('review/edit.html.twig', [
            'form' => $form->createView(),
            'review' => $review,
        ]);
    }

    #[Route('/{id}/delete', name: 'review_delete', requirements: ['id' => '\\d+'], methods: 'GET|DELETE')]
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

            return $this->redirectToRoute('review_index');
        }

        return $this->render('review/delete.html.twig', [
            'form' => $form->createView(),
            'review' => $review,
        ]);
    }
/////////////////////////////////////

    #[Route('/search', name: 'review_search')]
    public function search(
        Request $request,
        PaginatorInterface $paginator,
        FormFactoryInterface $formFactory
    ): Response {
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
