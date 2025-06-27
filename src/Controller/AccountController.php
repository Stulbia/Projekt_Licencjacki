<?php
namespace App\Controller;

use App\Entity\Account;
use App\Form\Type\AccountType;
use App\Security\Voter\AccountVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/account')]
class AccountController extends AbstractController
{


    private function isLinkValidForService(Account $account): bool
    {
        $service = $account->getPortalSpolecznosciowy();
        $host    = strtolower(parse_url($account->getLink(), PHP_URL_HOST) ?? '');

        $allowedHosts = [
            'tiktok'    => ['tiktok.com'],
            'youtube'   => ['youtube.com', 'youtu.be'],
            'instagram' => ['instagram.com'],
            'facebook'  => ['facebook.com'],
        ];

        foreach ($allowedHosts[$service] ?? [] as $domain) {
            if (\str_ends_with($host, $domain)) {
                return true;
            }
        }

        return false;
    }
    #[Route('/new', name: 'account_new', methods: ['GET','POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $account = new Account();
        $account->setUser($this->getUser());

        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // walidacja hosta w linku
            if (! $this->isLinkValidForService($account)) {
                $this->addFlash('error', 'message.invalid_social_link');
            } else {
                $em->persist($account);
                $em->flush();
                $this->addFlash('success', 'message.account_added');
                return $this->redirectToRoute('user_show', ['id' => $this->getUser()->getId()]);
            }
        }

        return $this->render('account/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/{id}/edit', name: 'account_edit', methods: ['GET','POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function edit(Account $account, Request $request, EntityManagerInterface $em): Response
    {
      //  $this->denyAccessUnlessGranted('EDIT', $account);
        $this->denyAccessUnlessGranted(AccountVoter::EDIT, $account);

        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // walidacja hosta w linku
            if (! $this->isLinkValidForService($account)) {
                $this->addFlash('error', 'message.invalid_social_link');
            } else {
                $em->flush();
                $this->addFlash('success', 'message.account_updated');
                return $this->redirectToRoute('user_index');
            }
        }

        return $this->render('account/edit.html.twig', [
            'form'    => $form->createView(),
            'account' => $account,
        ]);
    }


    #[Route('/{id}/delete', name: 'account_delete', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(Account $account, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(AccountVoter::EDIT, $account);

        if ($this->isCsrfTokenValid('delete'.$account->getId(), $request->request->get('_token'))) {
            $em->remove($account);
            $em->flush();
            $this->addFlash('success', 'message.account_deleted');
        }

        return $this->redirectToRoute('user_index');
    }
}