<?php

namespace App\Service;

use App\Entity\Review;
use App\Entity\ReviewTag;
use App\Entity\ReviewTagAssignment;

interface ReviewTagAssignmentServiceInterface
{
    public function assignTagToReview(Review $review, ReviewTag $tag): void;

    public function removeAssignment(ReviewTagAssignment $assignment): void;

    /**
     * @return ReviewTagAssignment[]
     */
    public function getAssignmentsForReview(Review $review): array;
}
