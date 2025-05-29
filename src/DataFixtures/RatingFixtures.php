<?php

/**
 * Rating fixtures.
 */

namespace App\DataFixtures;

use App\Entity\rating;
use App\Entity\Book;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class RatingFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class RatingFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        $this->createMany(50, 'ratings', function (int $i) {
            $rating = new rating();
            $rating->setValue($this->faker->numberBetween(1, 5));
            /** @var Book $book
             */
            $book = $this->getRandomReference('books');
            $rating->setBook($book);

            /** @var User $user
             */
            $user = $this->getRandomReference('users');
            $rating->setUser($user);

            return $rating;
        });

        $this->manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: BookFixtures::class}
     */
    public function getDependencies(): array
    {
        return [BookFixtures::class];
    }
}
