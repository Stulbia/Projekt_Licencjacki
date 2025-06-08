<?php

namespace App\Controller;

use App\Form\Type\UserAvatarType;
use App\Service\FileUploadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


class ProfileController extends AbstractController
{
    #[Route('/profile/avatar', name: 'user_avatar_edit')]
    public function updateAvatar(
        Request $request,
        FileUploadService $uploadService,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(UserAvatarType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('avatar')->getData();
            if ($file) {
                $filename = $uploadService->upload($file);
                $user->setAvatarFilename($filename);
                $em->flush(); // używamy wstrzykniętego EntityManagera
                $this->addFlash('success', 'Avatar zaktualizowany!');
                return $this->redirectToRoute('user_index');
            }
        }

        return $this->render('user/avatar_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
