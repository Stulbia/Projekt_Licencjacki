<?php

/**
 * Avatar controller.
 */

namespace App\Controller;

use App\Entity\Avatar;
use App\Entity\User;
use App\Form\Type\AvatarType;
use App\Service\AvatarServiceInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AvatarController.
 */
#[Route('/avatar')]
class AvatarController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param AvatarServiceInterface $avatarService Avatar service
     * @param TranslatorInterface    $translator    Translator
     */
    public function __construct(private readonly AvatarServiceInterface $avatarService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Show action.
     *
     * @param User $user user
     *
     * @return Response HTTP response
     */
    #[Route('/{id}', name: 'avatar_index', methods: 'GET')]
    #[IsGranted('VIEW', subject: 'user')]
    public function index(User $user): Response
    {
        $id = $user->getId();
        if (!$user->getAvatar()) {
            return $this->redirectToRoute('avatar_create', ['id' => $id]);
        }

        $avatar = $user->getAvatar();

        return $this->render('avatar/show.html.twig', [
            'avatar' => $avatar,
            'user' => $user,
        ]);
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     * @param User    $user    user
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/create',
        name: 'avatar_create',
        methods: 'GET|POST'
    )]
    #[IsGranted('EDIT', subject: 'user')]
    public function create(Request $request, User $user): Response
    {
        if ($user->getAvatar()) {
            return $this->redirectToRoute('avatar_edit', ['id' => $user->getId()]);
        }

        $avatar = new Avatar();
        $avatar->setUser($user);
        $avatar->setFilename(' ');

        $form = $this->createForm(
            AvatarType::class,
            $avatar,
            [
                'method' => 'POST',
                'action' => $this->generateUrl('avatar_create', ['id' => $user->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();
            $this->avatarService->create($file, $avatar, $user);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('avatar_index', ['id' => $user->getId()]);
        }

        return $this->render('avatar/create.html.twig', [
            'form' => $form->createView(),
            'avatar' => $avatar,
        ]);
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param User    $user    user
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'avatar_edit',
        methods: 'GET|PUT'
    )]
    #[IsGranted('EDIT', subject: 'user')]
    public function edit(Request $request, User $user): Response
    {
        if (!$user->getAvatar()) {
            return $this->redirectToRoute('avatar_create', ['id' => $user->getId()]);
        }

        $avatar = $user->getAvatar();

        $form = $this->createForm(
            AvatarType::class,
            $avatar,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('avatar_edit', ['id' => $user->getId()]),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();
            $this->avatarService->update($file, $avatar, $user);

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('avatar_index', ['id' => $user->getId()]);
        }

        return $this->render('avatar/edit.html.twig', [
            'form' => $form->createView(),
            'avatar' => $avatar,
        ]);
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param User    $user    user
     *
     * @return Response HTTP response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    #[Route(
        '/{id}/delete',
        name: 'avatar_delete',
        methods: 'GET|DELETE'
    )]
    #[IsGranted('EDIT', subject: 'user')]
    public function delete(Request $request, User $user): Response
    {
        if (!$user->getAvatar()) {
            $this->addFlash(
                'error',
                $this->translator->trans('message.avatar_does_not_exist')
            );

            return $this->redirectToRoute('avatar_index', ['id' => $user->getId()]);
        }

        $avatar = $user->getAvatar();
        $form = $this->createForm(
            FormType::class,
            $avatar,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('avatar_delete', ['id' => $user->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->avatarService->delete($avatar, $user);
            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('avatar_index', ['id' => $user->getId()]);
        }

        return $this->render('avatar/delete.html.twig', [
            'form' => $form->createView(),
            'avatar' => $avatar,
        ]);
    }
}
