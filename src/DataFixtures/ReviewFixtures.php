<?php

namespace App\DataFixtures;

use App\Entity\Review;
use App\Entity\Book;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ReviewFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    public function loadData(): void
    {
        $this->createMany(50, 'reviews', function (int $i) {
            $review = new Review();
            $review->setRating($this->faker->numberBetween(1, 10));
            $review->setComment($this->faker->paragraph());

            /** @var Book $book */
            $book = $this->getRandomReference('books');
            $review->setBook($book);

            /** @var User $author */
            $author = $this->getRandomReference('users');
            $review->setAuthor($author);

            return $review;
        });

        $this->manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            BookFixtures::class,
            UserFixtures::class,
        ];
    }
}
