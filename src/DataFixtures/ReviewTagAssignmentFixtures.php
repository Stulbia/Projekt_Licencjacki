<?php

namespace App\DataFixtures;

use App\Entity\Review;
use App\Entity\ReviewTag;
use App\Entity\ReviewTagAssignment;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ReviewTagAssignmentFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    public function loadData(): void
    {
        $this->createMany(100, 'review_tag_assignments', function (int $i) {
            $assignment = new ReviewTagAssignment();

            /** @var Review $review */
            $review = $this->getRandomReference('reviews');
            $assignment->setReview($review);

            /** @var ReviewTag $tag */
            $tag = $this->getRandomReference('review_tags');
            $assignment->setTag($tag);

            return $assignment;
        });

        $this->manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ReviewFixtures::class,
            ReviewTagFixtures::class,
        ];
    }
}
