<?php

/**
 * Gallery fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Gallery;
use App\Entity\User;

/**
 * Class GalleryFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class GalleryFixtures extends AbstractBaseFixtures
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        $this->createMany(5, 'galleries', function (int $i) {
            $gallery = new Gallery();
            $gallery->setTitle($this->faker->unique()->word);
            $gallery->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $gallery->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            /** @var array<array-key, User> $users */
            $users = $this->getRandomReferences(
                'users',
                $this->faker->numberBetween(0, 5)
            );
            foreach ($users as $user) {
                $gallery->addUser($user);
            }

            return $gallery;
        });

        $this->manager->flush();
    }
}
