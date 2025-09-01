<?php

/**
 * ReviewTagAssignment controller.
 */

namespace App\Controller;

use App\Entity\ReviewTagAssignment;
use App\Form\Type\ReviewTagAssignmentType;
use App\Service\ReviewTagAssignmentServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ReviewTagAssignmentController.
 */
#[Route('/review-tag-assignment')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class ReviewTagAssigmentController extends AbstractController
{
    public function __construct(
        private readonly ReviewTagAssignmentServiceInterface $reviewTagAssignmentService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(name: 'review_tag_assignment_index', methods: ['GET'])]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->reviewTagAssignmentService->getPaginatedList($page);

        return $this->render('review_tag_assignment/index.html.twig', ['pagination' => $pagination]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/create', name: 'review_tag_assignment_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $assignment = new ReviewTagAssignment();
        $form = $this->createForm(ReviewTagAssignmentType::class, $assignment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->reviewTagAssignmentService->save($assignment);
            $this->addFlash('success', $this->translator->trans('message.created_successfully'));
            return $this->redirectToRoute('review_tag_assignment_index');
        }

        return $this->render('review_tag_assignment/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}', name: 'review_tag_assignment_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(ReviewTagAssignment $assignment): Response
    {
        return $this->render('review_tag_assignment/show.html.twig', ['assignment' => $assignment]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'review_tag_assignment_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'PUT'])]
    public function edit(Request $request, ReviewTagAssignment $assignment): Response
    {
        $form = $this->createForm(ReviewTagAssignmentType::class, $assignment, [
            'method' => 'PUT',
            'action' => $this->generateUrl('review_tag_assignment_edit', ['id' => $assignment->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->reviewTagAssignmentService->save($assignment);
            $this->addFlash('success', $this->translator->trans('message.updated_successfully'));
            return $this->redirectToRoute('review_tag_assignment_index');
        }

        return $this->render('review_tag_assignment/edit.html.twig', [
            'form' => $form->createView(),
            'assignment' => $assignment,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/delete', name: 'review_tag_assignment_delete', requirements: ['id' => '\\d+'], methods: ['GET', 'DELETE'])]
    public function delete(Request $request, ReviewTagAssignment $assignment): Response
    {
        $form = $this->createForm(FormType::class, $assignment, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('review_tag_assignment_delete', ['id' => $assignment->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->reviewTagAssignmentService->delete($assignment);
            $this->addFlash('success', $this->translator->trans('message.deleted_successfully'));
            return $this->redirectToRoute('review_tag_assignment_index');
        }

        return $this->render('review_tag_assignment/delete.html.twig', [
            'form' => $form->createView(),
            'assignment' => $assignment,
        ]);
    }
}
