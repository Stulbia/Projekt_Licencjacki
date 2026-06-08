<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Form\Type\ChangePasswordType;
use App\Form\Type\UserType;
use App\Form\Type\UserUpdateType;
use App\Repository\UserBookRelationRepository;
use App\Service\BookServiceInterface;
use App\Service\ReviewServiceInterface;
use App\Service\UserManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

//register user + edit user + change password
/**
 * Class UserController.
 */
#[Route('/user')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserManagerInterface $userManager,
        private readonly TranslatorInterface $translator,
        //        private readonly BookServiceInterface $bookService,
        //        private readonly ReviewServiceInterface $reviewService,
        //        private readonly UserBookRelationRepository $bookRepo,
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

                // SUKCES: Zwykłe przekierowanie (domyślnie status 302). Turbo to uwielbia.
                return $this->redirectToRoute('app_login');

            } catch (UniqueConstraintViolationException $e) {
                // 1. Dodajemy flash (to już masz)
//                $this->addFlash('error', $this->translator->trans('message.email_already_exists'));

                // 2. Mapujemy błąd bezpośrednio na pole formularza 'email'
                // Dzięki temu dane NIE znikną z formularza!
                $form->get('email')->addError(new FormError($this->translator->trans('message.email_already_exists')));

                // 3. Renderujemy ten sam (teraz już "skażony" błędem) formularz z kodem 422
                return $this->render('user/register.html.twig', [
                    'form' => $form->createView(),
                ], new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY));
            }
        }

        return $this->render('user/register.html.twig', [
            'form' => $form->createView()
        ], new Response(
            null,
            $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK
        ));
    }


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
            'back_to_list_path' => 'user_index',
        ]);
    }
}
