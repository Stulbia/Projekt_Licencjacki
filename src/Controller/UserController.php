<?php

declare(strict_types=1);

/**
 * User controller.
 */

namespace App\Controller;

use App\Dto\BookListInputFiltersDto;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Form\Type\ChangePasswordType;
use App\Form\Type\UserType;
use App\Form\Type\UserTypeForAdmin;
use App\Form\Type\UserUpdateType;
use App\Service\BookServiceInterface;
use App\Service\UserManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UserController.
 */
#[Route('/user')]

class UserController extends AbstractController
{
    public function __construct(
        private readonly UserManagerInterface $userManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route(name: 'user_index', methods: ['GET'])]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        $user = $this->getUser();

        return $this->render('user/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/register', name: 'user_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $user->setRoles([UserRole::ROLE_USER->value]);
                $this->userManager->register($user);
                $this->addFlash('success', $this->translator->trans('message.registered_successfully'));

                return $this->redirectToRoute('book_index');
            } catch (UniqueConstraintViolationException) {
                $this->addFlash('error', 'message.Email in use.');
            }
        }

        return $this->render('user/register.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/list', name: 'user_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function list(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->userManager->getPaginatedList($page);

        return $this->render('user/list.html.twig', ['pagination' => $pagination]);
    }

    #[Route('/{id}', name: 'user_show', requirements: ['id' => '[1-9]\d*'], methods: ['GET'])]
    #[IsGranted('VIEW', subject: 'user')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', ['user' => $user]);
    }

    #[Route('/{id}/edit', name: 'user_edit', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'PUT'])]
    #[IsGranted('EDIT', subject: 'user')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserUpdateType::class, $user, [
            'method' => 'PUT',
            'action' => $this->generateUrl('user_edit', ['id' => $user->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->save($user);
            $this->addFlash('success', $this->translator->trans('message.updated_successfully'));

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit/admin', name: 'user_edit_admin', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function editAdmin(Request $request, User $user): Response
    {
        if ($this->getUser()?->getUserIdentifier() === $user->getUserIdentifier()) {
            if (!$this->userManager->canBeDowngraded()) {
                $this->addFlash('warning', $this->translator->trans('message.this_is_last_admin'));
                return $this->redirectToRoute('user_index');
            }
        }

        $form = $this->createForm(UserTypeForAdmin::class, $user, [
            'method' => 'PUT',
            'action' => $this->generateUrl('user_edit_admin', ['id' => $user->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->ifBanAdmin($user);
            $this->userManager->save($user);
            $this->addFlash('success', $this->translator->trans('message.updated_successfully'));

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/{id}/password', name: 'user_password', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'PUT'])]
    #[IsGranted('EDIT', subject: 'user')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function changePassword(Request $request, User $user): Response
    {
        $form = $this->createForm(ChangePasswordType::class, $user, [
            'method' => 'PUT',
            'action' => $this->generateUrl('user_password', ['id' => $user->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'message.passwords_not_match.');
            } else {
                try {
                    $this->userManager->upgradePassword($user, $newPassword);
                    $this->addFlash('success', 'message.Password_updated_successfully.');

                    return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'message.error: '.$e->getMessage());
                }
            }
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
