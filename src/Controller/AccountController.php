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
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class AccountController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /** Map of service => allowed host suffixes */
    private const ALLOWED_HOSTS = [
        'tiktok'    => ['tiktok.com'],
        'youtube'   => ['youtube.com', 'youtu.be'],
        'instagram' => ['instagram.com'],
        'facebook'  => ['facebook.com'],
    ];

    private function canonicalizeUrl(string $url): string
    {
        // Add scheme if user pasted a bare domain
        if (!preg_match('~^https?://~i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }
        return $url;
    }

    private function extractHost(?string $url): string
    {
        if (!$url) return '';
        $url  = $this->canonicalizeUrl($url);
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
        // strip common subdomains like www. or m.
        $host = preg_replace('~^(www|m)\.~', '', $host);
        return $host ?? '';
    }

    private function isLinkValidForService(Account $account): bool
    {
        $service = $account->getPortalSpolecznosciowy(); // e.g. 'instagram'
        $host    = $this->extractHost($account->getLink());

        foreach (self::ALLOWED_HOSTS[$service] ?? [] as $allowedSuffix) {
            if (str_ends_with($host, $allowedSuffix)) {
                return true;
            }
        }
        return false;
    }

    #[Route('/new', name: 'account_new', methods: ['GET','POST'])]
    public function new(Request $request): Response
    {
        $account = new Account();
        $account->setUser($this->getUser());

        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isLinkValidForService($account)) {
                $this->addFlash('error', 'message.invalid_social_link');
            } else {
                $this->em->persist($account);
                $this->em->flush();
                $this->addFlash('success', 'message.account_added');

                return $this->redirectToRoute('user_index');
            }
        }

        return $this->render('account/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'account_edit', methods: ['GET','POST'])]
    public function edit(Account $account, Request $request): Response
    {
        $this->denyAccessUnlessGranted(AccountVoter::EDIT, $account);

        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isLinkValidForService($account)) {
                $this->addFlash('error', 'message.invalid_social_link');
            } else {
                $this->em->flush();
                $this->addFlash('success', 'message.account_updated');

                // take user back to their profile (adjust route if you use slug)
                return $this->redirectToRoute('user_index');

            }
        }

        return $this->render('account/edit.html.twig', [
            'form'    => $form->createView(),
            'account' => $account,
        ]);
    }

    #[Route('/{id}/delete', name: 'account_delete', methods: ['POST'])]
    public function delete(Account $account, Request $request): Response
    {
        $this->denyAccessUnlessGranted(AccountVoter::EDIT, $account);

        if ($this->isCsrfTokenValid('delete'.$account->getId(), (string)$request->request->get('_token'))) {
            $this->em->remove($account);
            $this->em->flush();
            $this->addFlash('success', 'message.account_deleted');
        } else {
            $this->addFlash('error', 'message.csrf_invalid');
        }

        // Back to the user's profile (or wherever you list the accounts)
        return $this->redirectToRoute('user_index');
    }
}
