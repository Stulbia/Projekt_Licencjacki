<?php
//
///**
// * Class SecurityController.
// */
//
//namespace App\Controller;
//
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
//
///**
// * Class SecurityController.
// *
// * Controller responsible for handling security-related actions such as login and logout.
// */
//class SecurityController extends AbstractController
//{
//    /**
//     * Login action.
//     *
//     * @param AuthenticationUtils $authenticationUtils Authentication utilities
//     *
//     * @return Response HTTP response
//     */
//    #[Route(path: '/login', name: 'app_login')]
//    public function login(AuthenticationUtils $authenticationUtils): Response
//    {
//        $error = $authenticationUtils->getLastAuthenticationError();
//        $lastUsername = $authenticationUtils->getLastUsername();
//        return $this->render('security/login.html.twig', [
//            'last_username' => $lastUsername,
//            'error' => $error,
//        ]);
//    }
//
//    /**
//     * Logout action.
//     *
//     * This action will be intercepted by the Symfony security system.
//     *
//     * @throws \LogicException this method can be blank - it will be intercepted by the logout key on your firewall
//     */
//    #[Route(path: '/logout', name: 'app_logout')]
//    public function logout(): void
//    {
//        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
//    }
//}


namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method is handled by Symfony firewall (logout key).');
    }

    // ---------- GOOGLE OAUTH2 START ----------

    #[Route('/connect/google', name: 'connect_google_start')]
    public function connectGoogle(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect(['openid', 'email', 'profile']);
    }

    #[Route('/connect/google/check', name: 'google_check')]
    public function connectGoogleCheck(): void
    {
        // Symfony security system handles this automatically
        // Można przekierować np. do profilu użytkownika
    }

    // ---------- TIKTOK MOCK START ----------

    #[Route('/connect/mock-tiktok', name: 'mock_tiktok_start')]
    public function mockTiktokStart(SessionInterface $session): RedirectResponse
    {
        // Możesz tu dodać fejkowe dane do sesji, jeśli chcesz symulować dane użytkownika
        $session->set('mock_tiktok', true);
        return $this->redirectToRoute('mock_tiktok_check');
    }

    #[Route('/connect/mock-tiktok/check', name: 'mock_tiktok_check')]
    public function mockTiktokCheck(): RedirectResponse
    {
        // Symulowane dane użytkownika
        $mockUser = [
            'username' => 'TikTokUser',
            'roles' => ['ROLE_USER'],
        ];

        $token = new UsernamePasswordToken($mockUser, null, 'main', $mockUser['roles']);
        $this->container->get('security.token_storage')->setToken($token);

        return $this->redirectToRoute('app_home');
    }
}
