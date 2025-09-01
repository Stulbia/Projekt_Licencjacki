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
            return $this->getTopBooks();
        }


        $reviews = $this->reviewRepository->topReviewsByAuthor($user);
        $reviews = $reviews ->getQuery()->getResult();
        $reviewTagCounts = [];
        $bookSet = [];
        foreach ($reviews as $review) {
            foreach ($review->getTagAssignments() as $assignment) {
                $tag = $assignment->getTag();
                $tid = $tag->getId();
                $reviewTagCounts[$tid] = ($reviewTagCounts[$tid] ?? 0) + 1;
            }
            $bookSet[$review->getBook()->getId()] = $review->getBook();
        }
        $tagCounts = [];
        foreach ($bookSet as $book) {
            foreach ($book->getTags() as $tag) {
                $tid = $tag->getId();
                $tagCounts[$tid] = ($tagCounts[$tid] ?? 0) + 1;
            }
        }
        arsort($tagCounts);
        arsort($reviewTagCounts);
//        if (empty($topBookTags) && empty($topReviewTags)) {
//            return $this->getTopBooks();
//        }
        $topBookTags    = array_slice(array_keys($tagCounts), 0, 3);
        $topReviewTags    = array_slice(array_keys($reviewTagCounts), 0, 3);
        //topka bookId po tagach ksiazek;
        $top1 =  $this->reviewRepository->findMostPopularBooksByTags($topReviewTags, 10);
        $top2 = $this->bookRepository->findBooksByTagIdsExcludingUserReviewed($topBookTags, $user, 10);

//        var_dump($top2);
        $top = array_merge($top1, $top2);

        $recommendedBooks = array_slice($this->bookRepository->searchTopWithAvgRating($top), 0, 5);

        return  $recommendedBooks;
    }


    public function getPopularBooks(): array
    {
        return $this->bookRepository->findMostPopularBooks(10);
    }
    public function getTopBooks(): array
    {
        return $this->bookRepository->findHighestRatedBooks(10);
    }
}
