<?php

namespace App\Controller;

use App\Dto\BookListInputFiltersDto;
use App\Dto\BookSearchFiltersDto;
use App\Form\Type\UserAvatarType;
use App\Repository\UserBookRelationRepository;
use App\Service\BookServiceInterface;
use App\Service\FileUploadService;
use App\Service\ReviewServiceInterface;
use App\Service\UserManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


class ProfileController extends AbstractController
{

    public function __construct(
        private readonly UserManagerInterface $userManager,
        private readonly TranslatorInterface $translator,
        private readonly BookServiceInterface $bookService,
        private readonly ReviewServiceInterface $reviewService,
        private UserBookRelationRepository $bookRepo,
        private PaginatorInterface $paginator,
    ) {
    }
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

    #[Route('/profile/{slug}', name: 'user_public_profile', methods: ['GET'])]
    public function showPublic(string $slug, Request $request): Response
    {
        $user = $this->userManager->findOneBySlug($slug);

        if (!$user) {
            throw $this->createNotFoundException('Nie znaleziono użytkownika.');
        }


//        BIBLIOTECZKA UZYTKOWNIKA
        $page = $request->query->getInt('page', 1);
        $paginationR= $this->reviewService->getPaginatedUserList($request->query->getInt('page', 1), $user);
        if ($user) {

            $query = $this->bookRepo->getBooksByUser($user);
            $paginationB = $this->paginator->paginate(
                $query,
                $page,
                8,
                [
                    'defaultSortFieldName' => 'b.title',
                    'defaultSortDirection' => 'asc',
                    'sortFieldAllowList' => [
                        'b.title',
                        'a.name',
                        'relation.createdAt',
                        'relation.status',
                        'r.rating',
                        'avg_rating', // HIDDEN scalar from repo
                    ],
                ],
            );



//            $paginationB = $this->bookService->getPaginatedUserList($page, $user,new BookListInputFiltersDto(null,null,null )); //przepisac metode na bez filtrow
        } else {
            return $this->redirectToRoute('login');
        }

        return $this->render('user/public_profile.html.twig', [
            'user' => $user,
            'paginationR' => $paginationR,
            'paginationB' => $paginationB,
        ]);
    }

}
