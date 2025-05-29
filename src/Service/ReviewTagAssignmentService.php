<?php

namespace App\Service;

use App\Entity\Review;
use App\Entity\ReviewTag;
use App\Entity\ReviewTagAssignment;
use App\Repository\ReviewTagAssignmentRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class ReviewTagAssignmentService implements ReviewTagAssignmentServiceInterface
{
    public function __construct(
        private readonly ReviewTagAssignmentRepository $assignmentRepository
    ) {
    }

    public function assignTagToReview(Review $review, ReviewTag $tag): void
    {
        $assignment = new ReviewTagAssignment();
        $assignment->setReview($review);
        $assignment->setTag($tag);

        try {
            $this->assignmentRepository->save($assignment);
        } catch (ORMException|OptimisticLockException) {
            // handle error
        }
    }

    public function removeAssignment(ReviewTagAssignment $assignment): void
    {
        try {
            $this->assignmentRepository->delete($assignment);
        } catch (ORMException|OptimisticLockException) {
            // handle error
        }
    }

    public function getAssignmentsForReview(Review $review): array
    {
        return $this->assignmentRepository->findBy(['review' => $review]);
    }
}
