<?php

/**
 * Gallery controller.
 */

namespace App\Controller;

use App\Dto\BookListInputFiltersDto;
use App\Entity\Gallery;
use App\Form\Type\GalleryType;
use App\Service\GalleryServiceInterface;
use App\Service\BookServiceInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class GalleryController.
 */
#[Route('/gallery')]
class GalleryController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param BookServiceInterface   $bookService   Book service
     * @param GalleryServiceInterface $galleryService Gallery service
     * @param TranslatorInterface     $translator     Translator
     */
    public function __construct(private readonly BookServiceInterface $bookService, private readonly GalleryServiceInterface $galleryService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Gallery $gallery Gallery entity
     *
     * @return Response HTTP response
     */
    #[IsGranted('EDIT', subject: 'gallery')]
    #[Route('/{id}/edit', name: 'gallery_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function edit(Request $request, Gallery $gallery): Response
    {
        $form = $this->createForm(
            GalleryType::class,
            $gallery,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('gallery_edit', ['id' => $gallery->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->galleryService->save($gallery);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('gallery_index');
        }

        return $this->render(
            'gallery/edit.html.twig',
            [
                'form' => $form->createView(),
                'gallery' => $gallery,
            ]
        );
    }

    /**
     * Index action.
     *
     * @param int $page Page
     *
     * @return Response HTTP response
     */
    #[Route(name: 'gallery_index', methods: 'GET')]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->galleryService->getPaginatedList($page);

        return $this->render('gallery/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param Gallery $gallery Gallery
     * @param int     $page    Page
     *
     * @return Response HTTP response
     *
     * @throws NoResultException
     */
    #[IsGranted('VIEW', subject: 'gallery')]
    #[Route(
        '/{id}',
        name: 'gallery_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    public function show(Gallery $gallery, #[MapQueryParameter] int $page = 1): Response
    {
        $filters = new BookListInputFiltersDto($gallery->getId(), null, 'PUBLIC');
        $pagination = $this->bookService->getPaginatedList($page, $filters);

        return $this->render('gallery/show.html.twig', ['gallery' => $gallery, 'pagination' => $pagination]);
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/create',
        name: 'gallery_create',
        methods: 'GET|POST',
    )]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function create(Request $request): Response
    {
        $gallery = new Gallery();
        $form = $this->createForm(GalleryType::class, $gallery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->galleryService->save($gallery);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('gallery_index');
        }

        return $this->render(
            'gallery/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Gallery $gallery Gallery entity
     *
     * @return Response HTTP response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    #[Route('/{id}/delete', name: 'gallery_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    #[IsGranted('DELETE', subject: 'gallery')]
    public function delete(Request $request, Gallery $gallery): Response
    {
        if (!$this->galleryService->canBeDeleted($gallery)) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.gallery_contains_books')
            );

            return $this->redirectToRoute('gallery_index');
        }

        $form = $this->createForm(
            FormType::class,
            $gallery,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('gallery_delete', ['id' => $gallery->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->galleryService->delete($gallery);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('gallery_index');
        }

        return $this->render(
            'gallery/delete.html.twig',
            [
                'form' => $form->createView(),
                'gallery' => $gallery,
            ]
        );
    }
}
