<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\User;
use App\Repository\BookRepository;
use App\Repository\ReviewRepository;

class RecommendationService implements RecommendationServiceInterface
{
    public function __construct(
        private readonly ReviewRepository $reviewRepository,
        private readonly BookRepository $bookRepository,
    ) {
    }

    public function getRecommendationsFor(?User $user): array
    {
        if (!$user) {
        // Gość – pokaż najpopularniejsze książki
            return $this->bookRepository->findMostPopularBooks(5);
        }

    // Krok 1: znajdź najczęściej używane tagi w recenzjach użytkownika
        $tagIds = $this->reviewRepository->findTopTagIdsUsedByUser($user, 3);

        if (empty($tagIds)) {
            return $this->bookRepository->findMostPopularBooks(5);
        }

    // Krok 2: znajdź książki z tymi tagami, pomiń już ocenione przez użytkownika
        return $this->bookRepository->findBooksByTagIdsExcludingUserReviewed($tagIds, $user, 5);
    }
}
