<?php

namespace App\Controller;

use App\Service\RecommendationServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/for-you')]
//#[IsGranted('IS_AUTHENTICATED_FULLY')]
class ForYouController extends AbstractController
{
    public function __construct(
        private readonly RecommendationServiceInterface $recommendationService
    ) {
    }

    #[Route(name: 'for_you_index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();
        $yourBooks = $this->recommendationService->getRecommendationsFor($user);
        $popularBooks = $this->recommendationService->getPopularBooks();
        $topBooks = $this->recommendationService->getTopBooks();
        return $this->render('for_you/index.html.twig', [
            'yourBooks' => $yourBooks,
            'popularBooks' => $popularBooks,
            'topBooks' => $topBooks,
        ]);
    }
//    popularBooks
//yourBooks
//topBooks
}
