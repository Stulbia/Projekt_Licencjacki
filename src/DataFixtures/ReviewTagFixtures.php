<?php

namespace App\DataFixtures;

use App\Entity\ReviewTag;

class ReviewTagFixtures extends AbstractBaseFixtures
{
    public function loadData(): void
    {
        $this->createMany(10, 'review_tags', function (int $i) {
            $tag = new ReviewTag();
            $tag->setName($this->faker->unique()->word);

            return $tag;
        });

        $this->manager->flush();
    }
}
