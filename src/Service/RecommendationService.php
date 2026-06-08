<?php
//
//namespace App\Service;
//
//use App\Entity\Book;
//use App\Entity\User;
//use App\Repository\BookRepository;
//use App\Repository\ReviewRepository;
//
//class RecommendationService implements RecommendationServiceInterface
//{
//    public function __construct(
//        private readonly ReviewRepository $reviewRepository,
//        private readonly BookRepository $bookRepository,
//    ) {
//    }
//
//    public function getRecommendationsFor(?User $user): array
//    {
//        if (!$user) {
//            return $this->getTopBooks();
//        }
//
//
//        $reviews = $this->reviewRepository->topReviewsByAuthor($user);
//        $reviews = $reviews ->getQuery()->getResult();
//        $reviewTagCounts = [];
//        $bookSet = [];
//        foreach ($reviews as $review) {
//            foreach ($review->getTagAssignments() as $assignment) {
//                $tag = $assignment->getTag();
//                $tid = $tag->getId();
//                $reviewTagCounts[$tid] = ($reviewTagCounts[$tid] ?? 0) + 1;
//            }
//            $bookSet[$review->getBook()->getId()] = $review->getBook();
//        }
//        $tagCounts = [];
//        foreach ($bookSet as $book) {
//            foreach ($book->getTags() as $tag) {
//                $tid = $tag->getId();
//                $tagCounts[$tid] = ($tagCounts[$tid] ?? 0) + 1;
//            }
//        }
//        arsort($tagCounts);
//        arsort($reviewTagCounts);
////        if (empty($topBookTags) && empty($topReviewTags)) {
////            return $this->getTopBooks();
////        }
//        $topBookTags    = array_slice(array_keys($tagCounts), 0, 3);
//        $topReviewTags    = array_slice(array_keys($reviewTagCounts), 0, 3);
//        //topka bookId po tagach ksiazek;
//        $top1 =  $this->reviewRepository->findMostPopularBooksByTags($topReviewTags, 10);
//        $top2 = $this->bookRepository->findBooksByTagIdsExcludingUserReviewed($topBookTags, $user, 10);
//
////        var_dump($top2);
//        $top = array_merge($top1, $top2);
//
//        $recommendedBooks = array_slice($this->bookRepository->searchTopWithAvgRating($top), 0, 5);
//
//        return  $recommendedBooks;
//    }
//
//
//    public function getPopularBooks(): array
//    {
//        return $this->bookRepository->findMostPopularBooks(10);
//    }
//    public function getTopBooks(): array
//    {
//        return $this->bookRepository->findHighestRatedBooks(10);
//    }
//}


namespace App\Service;

use App\Entity\User;
use App\Repository\BookRepository;
use App\Repository\ReviewRepository;

class RecommendationService implements RecommendationServiceInterface
{
    public function __construct(
        private readonly ReviewRepository $reviewRepository,
        private readonly BookRepository   $bookRepository,
    )
    {
    }

    public function getRecommendationsFor(?User $user): array
    {
        if (!$user) {
            return $this->getTopBooks();
        }

        $userVector = $this->buildUserVector($user);

        if (empty($userVector)) {
            return $this->getTopBooks();
        }

        $candidates = $this->bookRepository->findBooksWithTagSimilarity(
            $userVector,
            $user,
            limit: 20
        );

        if (count($candidates) < 5) {
            $top = $this->getTopBooks();
            $existingIds = array_map(fn($b) => $b->getId(), $candidates);
            foreach ($top as $book) {
                if (!in_array($book->getId(), $existingIds)) {
                    $candidates[] = $book;
                }
                if (count($candidates) >= 5) break;
            }
        }

        return array_slice($candidates, 0, 5);
    }

    public function getPopularBooks(): array
    {
        return $this->bookRepository->findMostPopularBooks(10);
    }

    public function getTopBooks(): array
    {
        return $this->bookRepository->findHighestRatedBooks(10);
    }

    private function buildUserVector(User $user): array
    {
        $reviews = $this->reviewRepository->findWithTagsByAuthor($user);

        $vector = [];
        foreach ($reviews as $review) {
            $weight = $this->ratingToWeight($review->getRating());

            foreach ($review->getTagAssignments() as $assignment) {
                $tid = $assignment->getTag()->getId();
                $vector[$tid] = ($vector[$tid] ?? 0) + $weight;
            }

            foreach ($review->getBook()->getTags() as $tag) {
                $tid = $tag->getId();
                $vector[$tid] = ($vector[$tid] ?? 0) + ($weight * 0.5);
            }
        }

        return $this->normalize($vector);
    }

    private function ratingToWeight(int $rating): float
    {
        return ($rating - 1) / 4 * 1.5 - 0.5;
    }

    private function normalize(array $vector): array
    {
        $magnitude = sqrt(array_sum(array_map(fn($v) => $v ** 2, $vector)));
        if ($magnitude == 0) return $vector;

        return array_map(fn($v) => $v / $magnitude, $vector);
    }
}
