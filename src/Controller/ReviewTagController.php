<?php

/**
 * ReviewTag controller.
 */

namespace App\Controller;

use App\Entity\ReviewTag;
use App\Form\Type\ReviewTagType;
use App\Service\ReviewTagServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ReviewTagController.
 */
#[Route('/review-tag')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class ReviewTagController extends AbstractController
{
    public function __construct(
        private readonly ReviewTagServiceInterface $reviewTagService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(name: 'review_tag_index', methods: ['GET'])]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->reviewTagService->getPaginatedList($page);

        return $this->render('review_tag/index.html.twig', ['pagination' => $pagination]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/create', name: 'review_tag_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $reviewTag = new ReviewTag();
        $form = $this->createForm(ReviewTagType::class, $reviewTag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->reviewTagService->save($reviewTag);
            $this->addFlash('success', $this->translator->trans('message.created_successfully'));
            return $this->redirectToRoute('review_tag_index');
        }

        return $this->render('review_tag/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}', name: 'review_tag_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(ReviewTag $reviewTag): Response
    {
        return $this->render('review_tag/show.html.twig', ['reviewTag' => $reviewTag]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'review_tag_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'PUT'])]
    public function edit(Request $request, ReviewTag $reviewTag): Response
    {
        $form = $this->createForm(ReviewTagType::class, $reviewTag, [
            'method' => 'PUT',
            'action' => $this->generateUrl('review_tag_edit', ['id' => $reviewTag->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->reviewTagService->save($reviewTag);
            $this->addFlash('success', $this->translator->trans('message.updated_successfully'));
            return $this->redirectToRoute('review_tag_index');
        }

        return $this->render('review_tag/edit.html.twig', [
            'form' => $form->createView(),
            'reviewTag' => $reviewTag,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/delete', name: 'review_tag_delete', requirements: ['id' => '\\d+'], methods: ['GET', 'DELETE'])]
    public function delete(Request $request, ReviewTag $reviewTag): Response
    {
        $form = $this->createForm(FormType::class, $reviewTag, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('review_tag_delete', ['id' => $reviewTag->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->reviewTagService->delete($reviewTag);
            $this->addFlash('success', $this->translator->trans('message.deleted_successfully'));
            return $this->redirectToRoute('review_tag_index');
        }

        return $this->render('review_tag/delete.html.twig', [
            'form' => $form->createView(),
            'reviewTag' => $reviewTag,
        ]);
    }
}
