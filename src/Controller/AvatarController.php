<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\Type\AvatarType;
use App\Service\AvatarService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/avatar')]
class AvatarController extends AbstractController
{
    public function __construct(
        private readonly AvatarService $avatarService,
        private readonly TranslatorInterface $translator,
    ) {}

    #[Route('/edit', name: 'avatar_edit', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function edit(Request $request): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(AvatarType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('avatar')->getData();

            if ($uploadedFile) {
                try {
                    $this->avatarService->updateAvatar($user, $uploadedFile);
                    $this->addFlash('success', $this->translator->trans('message.avatar_updated'));

                    return $this->redirectToRoute('user_index');
                } catch (FileException $e) {
                    $this->addFlash('error', $this->translator->trans('message.avatar_upload_error'));
                }
            }
        }

        return $this->render('avatar/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
