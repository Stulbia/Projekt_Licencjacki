<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\BookListInputFiltersDto;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Form\Type\ChangePasswordType;
use App\Form\Type\UserType;
use App\Form\Type\UserTypeForAdmin;
use App\Form\Type\UserUpdateType;
use App\Repository\UserBookRelationRepository;
use App\Service\BookServiceInterface;
use App\Service\ReviewServiceInterface;
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
        private readonly BookServiceInterface $bookService,
        private readonly ReviewServiceInterface $reviewService,
        private readonly UserBookRelationRepository $bookRepo,
    ) {
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route(name: 'user_index', methods: ['GET'])]
    public function index(): Response
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

                return $this->redirectToRoute('user_index');
            } catch (UniqueConstraintViolationException) {
                $this->addFlash('error', 'message.Email in use.');
            }
        }

        return $this->render('user/register.html.twig', ['form' => $form->createView()]);
    }

//    #[IsGranted('IS_AUTHENTICATED_FULLY')]
//    #[Route('/show', name: 'user_show', methods: ['GET'])]
//    public function show(): Response
//    {
//        return $this->render('user/show.html.twig', ['user' => $this->getUser()]);
//    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/edit', name: 'user_edit', methods: ['GET', 'PUT'])]
    public function edit(Request $request): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(UserUpdateType::class, $user, [
            'method' => 'PUT',
            'action' => $this->generateUrl('user_edit'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->save($user);
            $this->addFlash('success', $this->translator->trans('message.updated_successfully'));

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/password', name: 'user_password', methods: ['GET', 'PUT'])]
    public function changePassword(Request $request): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class, $user, [
            'method' => 'PUT',
            'action' => $this->generateUrl('user_password'),
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

                    return $this->redirectToRoute('user_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'message.error: ' . $e->getMessage());
                }
            }
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'back_to_list_path' => 'user_index'
        ]);
    }
}
